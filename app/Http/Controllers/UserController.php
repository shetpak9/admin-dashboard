<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\DisableUserRequest;
use App\Services\ActivityLogService;
use App\Services\UserAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Kreait\Firebase\Exception\FirebaseException;

class UserController extends Controller
{
    public function __construct(
        private UserAdminService $userAdminService,
        private ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): View
    {
        try {
            $filters = Validator::make($request->query(), [
                'search' => ['nullable', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:255'],
                'role' => ['nullable', 'in:customer,seller'],
            ])->validate();

            return view('users.index', [
                'users' => $this->userAdminService->list($filters),
                'filters' => $filters,
            ]);
        } catch (FirebaseException) {
            session()->flash('error', 'Unable to load users from Firebase.');

            return view('users.index', ['users' => null, 'filters' => $request->query()]);
        }
    }

    public function show(string $uid): View|RedirectResponse
    {
        try {
            return view('users.show', ['user' => $this->userAdminService->details($uid)]);
        } catch (FirebaseException) {
            return to_route('users.index')->with('error', 'Unable to load this user from Firebase.');
        }
    }

    public function disable(DisableUserRequest $request, string $uid): RedirectResponse
    {
        try {
            $this->userAdminService->disable($uid);
            $this->audit($request, 'users.disable', $uid, ['reason' => $request->validated('reason')]);

            return back()->with('success', 'The account was disabled and its sessions were revoked.');
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to disable this account.');
        } catch (\Throwable) {
            return back()->with('error', 'The account was changed, but its activity could not be recorded.');
        }
    }

    public function enable(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->userAdminService->enable($uid);
            $this->audit($request, 'users.enable', $uid);

            return back()->with('success', 'The account was enabled.');
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to enable this account.');
        } catch (\Throwable) {
            return back()->with('error', 'The account was changed, but its activity could not be recorded.');
        }
    }

    public function revokeSessions(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->userAdminService->revokeSessions($uid);
            $this->audit($request, 'users.sessions_revoked', $uid);

            return back()->with('success', 'All sessions were revoked.');
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to revoke this user\'s sessions.');
        } catch (\Throwable) {
            return back()->with('error', 'Sessions were revoked, but the activity could not be recorded.');
        }
    }

    public function confirmDelete(string $uid): View|RedirectResponse
    {
        try {
            return view('users.delete', ['user' => $this->userAdminService->details($uid)]);
        } catch (FirebaseException) {
            return to_route('users.index')->with('error', 'Unable to load this user from Firebase.');
        }
    }

    public function destroy(DeleteUserRequest $request, string $uid): RedirectResponse
    {
        try {
            $snapshot = $this->userAdminService->delete($uid);
            $this->audit($request, 'users.deleted', $uid, ['snapshot' => $snapshot]);

            return to_route('users.index')->with('success', 'The user account was deleted.');
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to delete this user. No Auth account was removed.');
        } catch (\Throwable) {
            return back()->with('error', 'The account was deleted, but the activity could not be recorded.');
        }
    }

    public function auditLog(Request $request): View|RedirectResponse
    {
        try {
            $filters = Validator::make($request->query(), [
                'search' => ['nullable', 'string', 'max:128'],
                'userUid' => ['nullable', 'string', 'max:128'],
                'userRole' => ['nullable', 'in:admin,customer,seller'],
                'actionCategory' => ['nullable', 'in:Authentication,Profile,Cart,Orders,Payments,Products,Seller'],
                'action' => ['nullable', Rule::in([
                    'auth.registered',
                    'auth.login',
                    'auth.logout',
                    'auth.password_reset_requested',
                    'profile.created',
                    'profile.updated',
                    'profile.address_added',
                    'profile.address_updated',
                    'profile.address_deleted',
                    'cart.item_added',
                    'cart.item_removed',
                    'cart.quantity_updated',
                    'order.created',
                    'order.cancelled',
                    'payment.checkout_initiated',
                    'payment.checkout_opened',
                    'payment.returned_from_checkout',
                    'product.created',
                    'product.updated',
                    'product.deleted',
                    'seller.order_status_updated',
                    'seller.profile_updated',
                ])],
                'dateFrom' => ['nullable', 'date'],
                'dateTo' => ['nullable', 'date', 'after_or_equal:dateFrom'],
            ])->validate();

            return view('audit-log.index', [
                'logs' => $this->activityLogService->paginate($filters),
                'filters' => $filters,
                'actionOptions' => $this->activityLogService->actionOptions(),
            ]);
        } catch (\Throwable) {
            return view('audit-log.index', [
                'logs' => new LengthAwarePaginator([], 0, 20),
                'filters' => $request->query(),
                'actionOptions' => $this->activityLogService->actionOptions(),
                'error' => 'Activity logs are temporarily unavailable. Please try again later.',
            ]);
        }
    }

    private function audit(Request $request, string $action, string $uid, array $details = []): void
    {
        $adminUsername = (string) $request->session()->get('admin_username');

        $this->activityLogService->record([
            'userUid' => $adminUsername,
            'userName' => $adminUsername,
            'userRole' => 'admin',
            'action' => $action,
            'entityType' => 'user',
            'entityId' => $uid,
            'details' => $details,
            'ipAddress' => $request->ip(),
        ]);
    }
}
