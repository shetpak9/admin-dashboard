<x-layout>
    <div class="w-full mt-8 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100"><h2 class="text-xl font-bold text-gray-800">Audit log</h2></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-gray-50"><tr><th class="px-6 py-4">Date</th><th>Admin</th><th>Action</th><th>Target UID</th><th>Details</th><th>IP</th></tr></thead><tbody class="divide-y divide-gray-100">@forelse ($logs as $log)<tr><td class="px-6 py-4">{{ $log->created_at?->format('M d, Y H:i') }}</td><td>{{ $log->admin_username }}</td><td>{{ $log->action }}</td><td>{{ $log->target_uid }}</td><td>{{ json_encode($log->details) }}</td><td>{{ $log->ip_address }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No audit entries.</td></tr>@endforelse</tbody></table></div>
        <div class="p-6">{{ $logs->links() }}</div>
    </div>
</x-layout>
