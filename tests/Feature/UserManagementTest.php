<?php

use App\Http\Controllers\UserController;
use App\Models\AdminAuditLog;
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

    $response = $this->post(route('users.disable', 'uid-1'), ['reason' => 'Policy violation']);

    $response->assertSessionHas('success');
    expect(AdminAuditLog::query()->first()->toArray())->toMatchArray([
        'action' => 'disable',
        'target_uid' => 'uid-1',
    ]);
    expect(AdminAuditLog::query()->first()->details['reason'])->toBe('Policy violation');
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
