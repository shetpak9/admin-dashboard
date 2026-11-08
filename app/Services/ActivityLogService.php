<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogService
{
    private const MAX_RECORDS = 500;

    private const ACTION_LABELS = [
        'auth.registered' => 'Account Registered',
        'auth.login' => 'Logged In',
        'auth.logout' => 'Logged Out',
        'auth.password_reset_requested' => 'Password Reset Requested',
        'profile.created' => 'Profile Created',
        'profile.updated' => 'Profile Updated',
        'profile.address_added' => 'Address Added',
        'profile.address_updated' => 'Address Updated',
        'profile.address_deleted' => 'Address Deleted',
        'cart.item_added' => 'Item Added to Cart',
        'cart.item_removed' => 'Item Removed from Cart',
        'cart.quantity_updated' => 'Cart Quantity Updated',
        'order.created' => 'Order Created',
        'order.cancelled' => 'Order Cancelled',
        'payment.checkout_initiated' => 'Payment Checkout Initiated',
        'payment.checkout_opened' => 'Payment Checkout Opened',
        'payment.returned_from_checkout' => 'Returned from Payment Checkout',
        'product.created' => 'Product Created',
        'product.updated' => 'Product Updated',
        'product.deleted' => 'Product Deleted',
        'seller.order_status_updated' => 'Order Status Updated',
        'seller.profile_updated' => 'Seller Profile Updated',
    ];

    public function __construct(private FirebaseService $firebaseService) {}

    /**
     * @param array{
     *     userUid?: string|null,
     *     search?: string|null,
     *     userRole?: string|null,
     *     action?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null
     * } $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        try {
            $records = $this->firebaseService
                ->getDatabase()
                ->getReference('activity_logs')
                ->orderByChild('createdAt')
                ->limitToLast(self::MAX_RECORDS)
                ->getValue();
        } catch (Throwable $exception) {
            Log::error('Firebase activity log read failed.', ['exception' => $exception]);
            throw $exception;
        }

        $activities = [];

        foreach (is_array($records) ? $records : [] as $id => $record) {
            if (! is_array($record)) {
                continue;
            }

            $record['id'] = (string) $id;
            $record['actionLabel'] = $this->actionLabel($record['action'] ?? null);
            $record['actionCategory'] = $this->actionCategory($record['action'] ?? null);

            if (! $this->matches($record, $filters)) {
                continue;
            }

            $activities[] = $record;
        }

        usort($activities, fn (array $left, array $right): int => (int) ($right['createdAt'] ?? 0) <=> (int) ($left['createdAt'] ?? 0));

        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = array_slice($activities, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $items,
            count($activities),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    public function actionLabel(mixed $action): string
    {
        $action = trim((string) $action);

        if ($action === '') {
            return 'Unknown';
        }

        return self::ACTION_LABELS[$action] ?? Str::headline($action);
    }

    /**
     * @return array<string, string>
     */
    public function actionOptions(): array
    {
        return self::ACTION_LABELS;
    }

    public function actionCategory(mixed $action): string
    {
        $category = trim((string) $action);

        if ($category === '') {
            return 'Unknown';
        }

        return match (explode('.', $category)[0]) {
            'auth' => 'Authentication',
            'profile' => 'Profile',
            'cart' => 'Cart',
            'order' => 'Orders',
            'payment' => 'Payments',
            'product' => 'Products',
            'seller' => 'Seller',
            default => 'Other',
        };
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    public function record(array $activity): string
    {
        $record = [
            'userUid' => (string) ($activity['userUid'] ?? ''),
            'userName' => (string) ($activity['userName'] ?? ''),
            'userRole' => (string) ($activity['userRole'] ?? ''),
            'action' => (string) ($activity['action'] ?? ''),
            'entityType' => (string) ($activity['entityType'] ?? ''),
            'entityId' => (string) ($activity['entityId'] ?? ''),
            'details' => is_array($activity['details'] ?? null) ? $activity['details'] : [],
            'ipAddress' => $activity['ipAddress'] ?? null,
            'createdAt' => now()->getTimestampMs(),
        ];

        try {
            $key = $this->firebaseService
                ->getDatabase()
                ->getReference('activity_logs')
                ->push($record)
                ->getKey();

            if ($key === null) {
                throw new \RuntimeException('Firebase did not return an activity log key.');
            }

            return $key;
        } catch (Throwable $exception) {
            Log::error('Firebase activity log write failed.', [
                'action' => $record['action'],
                'entityType' => $record['entityType'],
                'entityId' => $record['entityId'],
                'exception' => $exception,
            ]);
            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $filters
     */
    private function matches(array $record, array $filters): bool
    {
        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $searchable = mb_strtolower(implode(' ', [
            (string) ($record['userName'] ?? ''),
            (string) ($record['userUid'] ?? ''),
        ]));

        if ($search !== '' && ! str_contains($searchable, $search)) {
            return false;
        }

        foreach ([
            'userUid' => 'userUid',
            'userRole' => 'userRole',
            'action' => 'action',
            'actionCategory' => 'actionCategory',
        ] as $filter => $field) {
            $value = trim((string) ($filters[$filter] ?? ''));

            if ($value !== '' && mb_strtolower((string) ($record[$field] ?? '')) !== mb_strtolower($value)) {
                return false;
            }
        }

        $createdAt = (int) ($record['createdAt'] ?? 0);

        if (($dateFrom = trim((string) ($filters['dateFrom'] ?? ''))) !== ''
            && ($createdAt <= 0 || $createdAt < Carbon::parse($dateFrom)->startOfDay()->getTimestampMs())) {
            return false;
        }

        if (($dateTo = trim((string) ($filters['dateTo'] ?? ''))) !== ''
            && ($createdAt <= 0 || $createdAt > Carbon::parse($dateTo)->endOfDay()->getTimestampMs())) {
            return false;
        }

        return true;
    }
}
