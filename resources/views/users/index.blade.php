<x-layout>
    <div class="w-full mt-8 space-y-6">
        <form method="GET" action="{{ route('users.index') }}" class="bg-white rounded-xl border border-gray-200 p-6 flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-56">
                <label for="search" class="block text-sm font-bold text-gray-700 mb-1">Search</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, phone, or UID" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
            </div>
            <div class="min-w-48">
                <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Exact email</label>
                <input id="email" name="email" type="email" value="{{ $filters['email'] ?? '' }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="role" class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                <select id="role" name="role" class="px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
                    <option value="">All</option>
                    <option value="customer" @selected(($filters['role'] ?? '') === 'customer')>Consumers</option>
                    <option value="seller" @selected(($filters['role'] ?? '') === 'seller')>Sellers</option>
                </select>
            </div>
            <button class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Filter</button>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100"><h2 class="text-xl font-bold text-gray-800">User Management</h2></div>
            @if ($users)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Name</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Role</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Email</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4"><a class="text-blue-700 font-medium" href="{{ route('users.show', $user['uid']) }}">{{ $user['name'] }}</a><div class="text-xs text-gray-500">{{ $user['uid'] }}</div></td>
                                    <td class="px-6 py-4 text-sm">{{ $user['role'] === 'seller' ? 'Seller' : ($user['role'] === 'customer' ? 'Consumer' : 'Unknown') }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $user['auth']?->email ?? 'No Auth account' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if (! $user['profileExists']) <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs">No RTDB profile</span>
                                        @elseif (! $user['auth']) <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs">No Auth account</span>
                                        @elseif ($user['auth']->disabled) <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-xs">Disabled</span>
                                        @else <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs">Active</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">{{ $user['auth']?->metadata->createdAt?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</x-layout>
