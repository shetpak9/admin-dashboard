<x-layout>
    <div class="w-full mt-8 space-y-6">
        @php($profile = $user['profile'] ?? [])
        <div class="flex justify-between items-center"><a href="{{ route('users.index') }}" class="text-blue-700">← Users</a><a href="{{ route('users.delete.confirm', $user['uid']) }}" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete user</a></div>
        <section class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-800">Profile</h2>
            <dl class="grid md:grid-cols-2 gap-4 mt-4 text-sm">
                <div><dt class="text-gray-500">Name</dt><dd>{{ trim(($profile['firstName'] ?? '').' '.($profile['lastName'] ?? '')) ?: 'Unnamed user' }}</dd></div>
                <div><dt class="text-gray-500">Role</dt><dd>{{ $user['role'] === 'seller' ? 'Seller' : ($user['role'] === 'customer' ? 'Consumer' : 'Unknown') }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd>{{ $profile['phone'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd>{{ $user['auth']?->email ?? 'No Auth account' }}</dd></div>
                <div><dt class="text-gray-500">UID</dt><dd class="break-all">{{ $user['uid'] }}</dd></div>
                <div><dt class="text-gray-500">Auth status</dt><dd>{{ $user['auth']?->disabled ? 'Disabled' : ($user['auth'] ? 'Active' : 'No Auth account') }}</dd></div>
                <div><dt class="text-gray-500">Created</dt><dd>{{ $user['auth']?->metadata->createdAt?->format('M d, Y H:i') ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Last sign-in</dt><dd>{{ $user['auth']?->metadata->lastLoginAt?->format('M d, Y H:i') ?? '—' }}</dd></div>
            </dl>
            @if ($user['auth'])
                <div class="flex flex-wrap gap-3 mt-6">
                    @if ($user['auth']->disabled)
                        <form method="POST" action="{{ route('users.enable', $user['uid']) }}" onsubmit="return confirm('Enable this account?');">@csrf<button class="px-4 py-2 rounded-lg bg-green-600 text-white">Enable account</button></form>
                    @else
                        <form method="POST" action="{{ route('users.disable', $user['uid']) }}" class="flex gap-2" onsubmit="return confirm('Disable this account and revoke its sessions?');"><input name="reason" required maxlength="200" placeholder="Reason for disabling" class="px-3 py-2 border rounded-lg"><button class="px-4 py-2 rounded-lg bg-orange-600 text-white">Disable</button></form>
                    @endif
                    <form method="POST" action="{{ route('users.revoke-sessions', $user['uid']) }}" onsubmit="return confirm('Revoke all sessions for this account?');">@csrf<button class="px-4 py-2 rounded-lg bg-gray-700 text-white">Revoke sessions</button></form>
                </div>
            @endif
        </section>

        @if ($user['role'] === 'seller')
            <section class="bg-white rounded-xl border border-gray-200 p-6"><h2 class="text-xl font-bold text-gray-800">Seller details</h2><p class="mt-3">Store: {{ $user['store']['name'] ?? '—' }} ({{ ($user['store']['isOpen'] ?? false) ? 'Open' : 'Closed' }})</p><p class="mt-2">Products: {{ $user['productCount'] }}</p></section>
        @else
            <section class="bg-white rounded-xl border border-gray-200 p-6"><h2 class="text-xl font-bold text-gray-800">Consumer details</h2><p class="mt-3">Orders placed: {{ $user['orderCount'] }}</p></section>
        @endif
        <section class="bg-white rounded-xl border border-gray-200 p-6"><h2 class="text-xl font-bold text-gray-800">Order status breakdown</h2><div class="flex flex-wrap gap-3 mt-4">@forelse ($user['statusCounts'] as $status => $count)<span class="px-3 py-2 rounded bg-gray-100 text-sm">{{ $status }}: {{ $count }}</span>@empty<span class="text-gray-500">No orders.</span>@endforelse</div></section>
        <section class="bg-white rounded-xl border border-gray-200 p-6"><h2 class="text-xl font-bold text-gray-800">Saved addresses</h2><ul class="mt-4 space-y-2">@forelse ($user['addresses'] as $address)<li class="p-3 bg-gray-50 rounded">{{ is_array($address) ? implode(', ', array_filter($address)) : $address }}</li>@empty<li class="text-gray-500">No saved addresses.</li>@endforelse</ul></section>
        <section class="bg-white rounded-xl border border-gray-200 p-6"><h2 class="text-xl font-bold text-gray-800">Recent orders</h2><div class="overflow-x-auto mt-4"><table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="py-3">ID</th><th>Status</th><th>Payment</th><th>Total</th><th>Date</th></tr></thead><tbody>@forelse ($user['orders'] as $order)<tr class="border-b"><td class="py-3">{{ $order['id'] }}</td><td>{{ $order['status'] ?? '—' }}</td><td>{{ $order['paymentMethod'] ?? '—' }} / {{ $order['paymentStatus'] ?? '—' }}</td><td>₱{{ number_format((float) ($order['finalTotal'] ?? $order['total'] ?? 0), 2) }}</td><td>{{ isset($order['createdAt']) ? \Illuminate\Support\Carbon::createFromTimestampMs((int) $order['createdAt'])->format('M d, Y H:i') : '—' }}</td></tr>@empty<tr><td colspan="5" class="py-6 text-center text-gray-500">No orders.</td></tr>@endforelse</tbody></table></div></section>
    </div>
</x-layout>
