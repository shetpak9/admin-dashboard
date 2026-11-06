<x-layout>
    <div class="w-full max-w-2xl mt-8 bg-white rounded-xl border border-red-200 p-6">
        <h2 class="text-xl font-bold text-red-700">Delete user account</h2>
        <p class="mt-3 text-gray-700">This permanently removes the Firebase account and profile data. Orders and reviews are kept for history.</p>
        <p class="mt-3 text-sm text-gray-600">Type <strong>{{ $user['uid'] }}</strong> to confirm.</p>
        <form method="POST" action="{{ route('users.destroy', $user['uid']) }}" class="mt-6 space-y-4" onsubmit="return confirm('Delete this account permanently?');">
            @csrf @method('DELETE')
            <input type="hidden" name="uid" value="{{ $user['uid'] }}">
            <input name="confirmation_uid" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
            <div class="flex gap-3"><a href="{{ route('users.show', $user['uid']) }}" class="px-4 py-2 rounded-lg bg-gray-100">Cancel</a><button class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete permanently</button></div>
        </form>
    </div>
</x-layout>
