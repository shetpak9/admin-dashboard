<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\DisableUserRequest;
use App\Models\AdminAuditLog;
use App\Services\UserAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Kreait\Firebase\Exception\FirebaseException;

class UserController extends Controller
{
    public function __construct(private UserAdminService $userAdminService) {}

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
            $this->audit($request, 'disable', $uid, ['reason' => $request->validated('reason')]);

            return back()->with('success', 'The account was disabled and its sessions were revoked.');
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to disable this account.');
        }
    }

    public function enable(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->userAdminService->enable($uid);
            $this->audit($request, 'enable', $uid);

            return back()->with('success', 'The account was enabled.');
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to enable this account.');
        }
    }

    public function revokeSessions(Request $request, string $uid): RedirectResponse
    {
        try {
            $this->userAdminService->revokeSessions($uid);
            $this->audit($request, 'revoke_sessions', $uid);

            return back()->with('success', 'All sessions were revoked.');
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to revoke this user\'s sessions.');
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
            $this->audit($request, 'delete', $uid, ['snapshot' => $snapshot]);

            return to_route('users.index')->with('success', 'The user account was deleted.');
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (FirebaseException) {
            return back()->with('error', 'Unable to delete this user. No Auth account was removed.');
        }
    }

    public function auditLog(): View
    {
        return view('audit-log.index', ['logs' => AdminAuditLog::query()->latest('created_at')->paginate(20)]);
    }

    private function audit(Request $request, string $action, string $uid, array $details = []): void
    {
        AdminAuditLog::create([
            'admin_username' => (string) $request->session()->get('admin_username'),
            'action' => $action,
            'target_uid' => $uid,
            'details' => $details,
            'ip_address' => $request->ip(),
        ]);
    }
}
