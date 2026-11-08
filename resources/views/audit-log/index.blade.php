<x-layout>
    <div class="w-full mt-8 space-y-6">
        <form method="GET" action="{{ route('audit-log.index') }}" class="bg-white rounded-xl border border-gray-200 p-6 flex flex-wrap gap-4 items-end">
            <div class="min-w-52 flex-1">
                <label for="search" class="block text-sm font-bold text-gray-700 mb-1">Search user</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or Firebase UID" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="userRole" class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                <select id="userRole" name="userRole" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
                    <option value="">All Roles</option>
                    @foreach (['admin' => 'Admin', 'customer' => 'Customer', 'seller' => 'Seller'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['userRole'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="actionCategory" class="block text-sm font-bold text-gray-700 mb-1">Action category</label>
                <select id="actionCategory" name="actionCategory" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
                    <option value="">All Actions</option>
                    @foreach (['Authentication', 'Profile', 'Cart', 'Orders', 'Payments', 'Products', 'Seller'] as $category)
                        <option value="{{ $category }}" @selected(($filters['actionCategory'] ?? '') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="action" class="block text-sm font-bold text-gray-700 mb-1">Exact action</label>
                <select id="action" name="action" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
                    <option value="">All Actions</option>
                    @foreach ($actionOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['action'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dateFrom" class="block text-sm font-bold text-gray-700 mb-1">From</label>
                <input id="dateFrom" name="dateFrom" type="date" value="{{ $filters['dateFrom'] ?? '' }}" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="dateTo" class="block text-sm font-bold text-gray-700 mb-1">To</label>
                <input id="dateTo" name="dateTo" type="date" value="{{ $filters['dateTo'] ?? '' }}" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
            </div>
            <button class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Filter</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Activity Logs</h2>
                <p class="text-sm text-gray-500 mt-1">Showing the newest Firebase activity records first.</p>
            </div>
            @if (! empty($error))
                <div class="m-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $error }}</div>
            @endif
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Date &amp; Time</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Action</th>
                            <th class="px-6 py-4">Entity</th>
                            <th class="px-6 py-4">Details</th>
                            <th class="px-6 py-4">User UID</th>
                            <th class="px-6 py-4">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            @php
                                $timestamp = isset($log['createdAt']) && is_numeric($log['createdAt'])
                                    ? \Illuminate\Support\Carbon::createFromTimestampMs((int) $log['createdAt'])
                                    : null;
                                $roleClasses = match ($log['userRole'] ?? null) {
                                    'admin' => 'bg-purple-100 text-purple-700',
                                    'customer' => 'bg-blue-100 text-blue-700',
                                    'seller' => 'bg-green-100 text-green-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                $actionClasses = match ($log['actionCategory'] ?? null) {
                                    'Authentication' => 'bg-indigo-100 text-indigo-700',
                                    'Orders' => 'bg-orange-100 text-orange-700',
                                    'Payments' => 'bg-emerald-100 text-emerald-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $timestamp?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                <td class="px-6 py-4"><div class="font-medium">{{ $log['userName'] ?? 'N/A' }}</div><div class="text-xs text-gray-500 break-all">{{ $log['id'] ?? 'N/A' }}</div></td>
                                <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs font-medium {{ $roleClasses }}">{{ $log['userRole'] ?? 'N/A' }}</span></td>
                                <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs font-medium {{ $actionClasses }}">{{ $log['actionLabel'] ?? 'N/A' }}</span><div class="text-xs text-gray-500 mt-1">{{ $log['action'] ?? 'N/A' }}</div></td>
                                <td class="px-6 py-4"><div>{{ $log['entityType'] ?? 'N/A' }}</div><div class="text-xs text-gray-500 break-all">{{ $log['entityId'] ?? 'N/A' }}</div></td>
                                <td class="px-6 py-4 max-w-xs">{{ is_array($log['details'] ?? null) && $log['details'] !== [] ? \Illuminate\Support\Str::limit(implode('; ', array_map(fn ($key, $value) => $key.': '.(is_scalar($value) ? $value : '[object]'), array_keys($log['details']), $log['details'])), 80) : 'N/A' }}</td>
                                <td class="px-6 py-4 max-w-xs break-all">{{ $log['userUid'] ?? 'N/A' }}</td>
                                <td class="relative px-6 py-4"><details><summary class="cursor-pointer text-blue-700 font-medium">View</summary><div class="absolute right-0 z-10 mt-2 w-96 max-w-[80vw] rounded-lg border border-gray-200 bg-white p-4 shadow-lg"><dl class="space-y-2 text-sm"><div><dt class="font-semibold">Firebase Log ID</dt><dd class="break-all">{{ $log['id'] ?? 'N/A' }}</dd></div><div><dt class="font-semibold">User UID</dt><dd class="break-all">{{ $log['userUid'] ?? 'N/A' }}</dd></div><div><dt class="font-semibold">User</dt><dd>{{ $log['userName'] ?? 'N/A' }}</dd></div><div><dt class="font-semibold">Role</dt><dd>{{ $log['userRole'] ?? 'N/A' }}</dd></div><div><dt class="font-semibold">Action</dt><dd>{{ $log['action'] ?? 'N/A' }}</dd></div><div><dt class="font-semibold">Entity</dt><dd>{{ $log['entityType'] ?? 'N/A' }} / {{ $log['entityId'] ?? 'N/A' }}</dd></div><div><dt class="font-semibold">Date &amp; Time</dt><dd>{{ $timestamp?->format('M d, Y H:i') ?? 'N/A' }}</dd></div><div><dt class="font-semibold">Details</dt><dd class="break-words">{{ ! empty($log['details']) ? json_encode($log['details'], JSON_UNESCAPED_SLASHES) : 'N/A' }}</dd></div></dl></div></details></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">No activity records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6">{{ $logs->links() }}</div>
        </div>
    </div>
</x-layout>
