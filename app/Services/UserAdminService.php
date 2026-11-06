<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class UserAdminService
{
    public function __construct(private FirebaseService $firebaseService) {}

    public function list(array $filters): LengthAwarePaginator
    {
        $profiles = $this->profiles();
        $authRecords = [];

        foreach ($this->firebaseService->getAuth()->listUsers() as $record) {
            $authRecords[$record->uid] = $record;
        }

        if (($email = trim((string) ($filters['email'] ?? ''))) !== '') {
            try {
                $record = $this->firebaseService->getAuth()->getUserByEmail($email);
                $authRecords[$record->uid] = $record;
                $profiles[$record->uid] ??= ['uid' => $record->uid];
            } catch (UserNotFound) {
                //
            }
        }

        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $role = $filters['role'] ?? '';
        $emailUid = $email !== '' ? array_key_first(array_filter(
            $authRecords,
            fn (mixed $record): bool => $record?->email === $email,
        )) : null;
        $users = [];

        foreach ($profiles as $uid => $profile) {
            $profile = is_array($profile) ? $profile : [];
            $profile['uid'] = (string) ($profile['uid'] ?? $uid);

            if ($emailUid !== null && $profile['uid'] !== $emailUid) {
                continue;
            }

            if ($role !== '' && ($profile['role'] ?? '') !== $role) {
                continue;
            }

            $haystack = mb_strtolower(implode(' ', [
                (string) ($profile['firstName'] ?? ''),
                (string) ($profile['lastName'] ?? ''),
                (string) ($profile['phone'] ?? ''),
                $profile['uid'],
            ]));

            if ($search !== '' && ! str_contains($haystack, $search)) {
                continue;
            }

            $users[$profile['uid']] = $this->listItem($profile, $authRecords[$profile['uid']] ?? null);
        }

        foreach ($authRecords as $uid => $record) {
            if (isset($users[$uid]) || $role !== '') {
                continue;
            }

            if ($emailUid !== null && $uid !== $emailUid) {
                continue;
            }

            $profile = ['uid' => $uid];
            $haystack = mb_strtolower($uid.' '.($record->email ?? ''));

            if ($search !== '' && ! str_contains($haystack, $search)) {
                continue;
            }

            $users[$uid] = $this->listItem($profile, $record);
        }

        usort($users, fn (array $left, array $right): int => strcasecmp(
            trim($left['name']),
            trim($right['name']),
        ));

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $items = array_slice($users, ($page - 1) * $perPage, $perPage);
        $pageAuthRecords = $items === [] ? [] : $this->firebaseService->getAuth()->getUsers(array_column($items, 'uid'));

        foreach ($items as &$item) {
            $item['auth'] = $pageAuthRecords[$item['uid']] ?? null;
        }
        unset($item);

        return new LengthAwarePaginator(
            $items,
            count($users),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    public function find(string $uid): array
    {
        $profile = $this->profiles()[$uid] ?? null;
        $authRecord = null;

        try {
            $authRecord = $this->firebaseService->getAuth()->getUser($uid);
        } catch (UserNotFound) {
            //
        }

        if ($profile === null && $authRecord === null) {
            abort(404, 'User not found.');
        }

        return [
            'uid' => $uid,
            'profile' => is_array($profile) ? $profile : null,
            'auth' => $authRecord,
        ];
    }

    public function details(string $uid): array
    {
        $user = $this->find($uid);
        $profile = $user['profile'] ?? [];
        $role = $profile['role'] ?? null;
        $orders = $this->ordersFor($role === 'seller' ? 'sellerId' : 'customerId', $uid);
        $statusCounts = [];

        foreach ($orders as $order) {
            $status = (string) ($order['status'] ?? 'unknown');
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }

        $products = [];
        $store = null;

        if ($role === 'seller') {
            $products = $this->query('products', 'sellerId', $uid);
            $store = $this->reference("stores/{$uid}")->getValue();
        }

        $recentOrders = [];
        foreach ($orders as $orderId => $order) {
            $order['id'] = $orderId;
            $recentOrders[] = $order;
        }
        usort($recentOrders, fn (array $left, array $right): int => (int) ($right['createdAt'] ?? 0) <=> (int) ($left['createdAt'] ?? 0));

        return [
            ...$user,
            'role' => $role,
            'addresses' => $this->reference("addresses/{$uid}")->getValue() ?? [],
            'orders' => array_slice($recentOrders, 0, 20),
            'orderCount' => count($orders),
            'statusCounts' => $statusCounts,
            'productCount' => count($products),
            'store' => is_array($store) ? $store : null,
        ];
    }

    public function disable(string $uid): void
    {
        $this->firebaseService->getAuth()->disableUser($uid);
        $this->firebaseService->getAuth()->revokeRefreshTokens($uid);
    }

    public function enable(string $uid): void
    {
        $this->firebaseService->getAuth()->enableUser($uid);
    }

    public function revokeSessions(string $uid): void
    {
        $this->firebaseService->getAuth()->revokeRefreshTokens($uid);
    }

    public function delete(string $uid): array
    {
        $details = $this->details($uid);
        $activeOrders = $this->activeOrderCount($uid);

        if ($activeOrders > 0) {
            throw new \RuntimeException("This user cannot be deleted because {$activeOrders} active order(s) remain.");
        }

        $updates = [
            "carts/{$uid}" => null,
            "favorites/{$uid}" => null,
            "addresses/{$uid}" => null,
            "notifications/{$uid}" => null,
            "transactions/{$uid}" => null,
            "users/{$uid}" => null,
        ];

        if (($details['role'] ?? null) === 'seller') {
            $products = $this->query('products', 'sellerId', $uid);
            foreach (array_keys($products) as $productId) {
                $updates["products/{$productId}"] = null;
                $updates["productCounts/{$productId}"] = null;
                $updates["reviews/{$productId}"] = null;
            }
            $updates["promotions/{$uid}"] = null;
            $updates["stores/{$uid}"] = null;
        }

        $this->reference('')->update($updates);
        Cache::forget('firebase.users');

        try {
            $this->firebaseService->getAuth()->deleteUser($uid);
        } catch (UserNotFound) {
            //
        }

        return [
            'name' => trim(($details['profile']['firstName'] ?? '').' '.($details['profile']['lastName'] ?? '')),
            'role' => $details['role'],
            'email' => $details['auth']?->email,
        ];
    }

    private function profiles(): array
    {
        return Cache::remember('firebase.users', 60, function (): array {
            $value = $this->reference('users')->getValue();

            return is_array($value) ? $value : [];
        });
    }

    private function listItem(array $profile, mixed $authRecord): array
    {
        return [
            'uid' => $profile['uid'],
            'name' => trim(($profile['firstName'] ?? '').' '.($profile['lastName'] ?? '')) ?: 'Unnamed user',
            'role' => $profile['role'] ?? null,
            'phone' => $profile['phone'] ?? null,
            'auth' => $authRecord,
            'profileExists' => $profile !== ['uid' => $profile['uid']],
        ];
    }

    private function ordersFor(string $field, string $uid): array
    {
        $value = $this->reference('orders')->orderByChild($field)->equalTo($uid)->getValue();

        return is_array($value) ? $value : [];
    }

    private function query(string $node, string $field, string $value): array
    {
        $result = $this->reference($node)->orderByChild($field)->equalTo($value)->getValue();

        return is_array($result) ? $result : [];
    }

    private function activeOrderCount(string $uid): int
    {
        $orders = $this->query('orders', 'customerId', $uid);
        foreach ($this->query('orders', 'sellerId', $uid) as $orderId => $order) {
            $orders[$orderId] = $order;
        }

        return count(array_filter($orders, fn (array $order): bool => ! in_array($order['status'] ?? null, ['delivered', 'cancelled'], true)));
    }

    private function reference(string $path): mixed
    {
        return $this->firebaseService->getDatabase()->getReference($path);
    }
}
