<?php

use App\Http\Controllers\UserController;
use App\Services\ActivityLogService;
use App\Services\FirebaseService;
use App\Services\UserAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withSession([
        'admin_authenticated' => true,
        'admin_username' => 'tester',
    ]);
    $this->mock(ActivityLogService::class);
});

it('redirects guests from every user route', function (string $method, string $uri): void {
    $this->flushSession();

    $response = $this->{strtolower($method)}($uri);

    $response->assertRedirect(route('login'));
})->with([
    ['GET', '/users'],
    ['GET', '/users/test-uid'],
    ['POST', '/users/test-uid/disable'],
    ['POST', '/users/test-uid/enable'],
    ['POST', '/users/test-uid/revoke-sessions'],
    ['GET', '/users/test-uid/delete'],
    ['DELETE', '/users/test-uid'],
    ['GET', '/audit-log'],
]);

it('disables an account, revokes sessions, and writes an audit log', function (): void {
    $this->mock(UserAdminService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('disable')->once()->with('uid-1');
    });
    $this->mock(ActivityLogService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('record')->once()->with(Mockery::on(
            fn (array $activity): bool => $activity['action'] === 'users.disable'
                && $activity['entityId'] === 'uid-1'
                && $activity['details']['reason'] === 'Policy violation',
        ));
    });

    $response = $this->post(route('users.disable', 'uid-1'), ['reason' => 'Policy violation']);

    $response->assertSessionHas('success');
});

it('delegates user search and role filters to the user service', function (): void {
    $this->mock(UserAdminService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('list')
            ->once()
            ->with(['search' => 'Mina', 'role' => 'seller'])
            ->andReturn(new LengthAwarePaginator([], 0, 20));
    });

    $view = app(UserController::class)->index(Request::create('/users', 'GET', [
        'search' => 'Mina',
        'role' => 'seller',
    ]));

    expect($view->name())->toBe('users.index');
});

it('delegates audit-log filters to Firebase activity logging', function (): void {
    $this->mock(UserAdminService::class);
    $this->mock(ActivityLogService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('actionOptions')->once()->andReturn([]);
        $mock->shouldReceive('paginate')
            ->once()
            ->with([
                'userUid' => 'uid-1',
                'userRole' => 'seller',
                'action' => 'product.updated',
                'dateFrom' => '2026-11-01',
                'dateTo' => '2026-11-06',
            ])
            ->andReturn(new LengthAwarePaginator([], 0, 20));
    });

    $view = app(UserController::class)->auditLog(Request::create('/audit-log', 'GET', [
        'userUid' => 'uid-1',
        'userRole' => 'seller',
        'action' => 'product.updated',
        'dateFrom' => '2026-11-01',
        'dateTo' => '2026-11-06',
    ]));

    expect($view->name())->toBe('audit-log.index');
});

it('formats activity actions for display', function (): void {
    $service = new ActivityLogService(Mockery::mock(FirebaseService::class));

    expect($service->actionLabel('order.created'))->toBe('Order Created')
        ->and($service->actionLabel('profile.updated'))->toBe('Profile Updated')
        ->and($service->actionLabel(null))->toBe('Unknown');
});

it('renders a safe error state when Firebase activity logs fail', function (): void {
    $this->mock(UserAdminService::class);
    $this->mock(ActivityLogService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('actionOptions')->once()->andReturn([]);
        $mock->shouldReceive('paginate')->once()->andThrow(new RuntimeException('Firebase unavailable'));
    });

    $view = app(UserController::class)->auditLog(Request::create('/audit-log', 'GET'));

    expect($view->name())->toBe('audit-log.index')
        ->and($view->getData()['error'])->toContain('temporarily unavailable');
});

it('blocks delete when the service reports active orders', function (): void {
    $this->mock(UserAdminService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('delete')
            ->once()
            ->andThrow(new RuntimeException('This user cannot be deleted because 1 active order(s) remain.'));
    });

    $this->delete(route('users.destroy', 'uid-1'), [
        'uid' => 'uid-1',
        'confirmation_uid' => 'uid-1',
    ])->assertSessionHas('error', 'This user cannot be deleted because 1 active order(s) remain.');
});
