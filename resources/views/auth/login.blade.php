<x-layout>
    <div class="w-full max-w-md mt-8">
        <form id="login-form" method="POST" action="{{ route('session.store') }}" class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            @csrf
            <h2 class="text-xl font-bold text-gray-800">Sign in</h2>
            <p class="text-sm text-gray-500 mt-1">Enter your administrator credentials.</p>

            <div class="space-y-4 mt-6">
                <div>
                    <label for="username" class="block text-sm font-bold text-gray-700 mb-1">Username</label>
                    <input id="username" name="username" type="text" required autocomplete="username" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg">
                </div>
            </div>

            @error('username')
                <p class="mt-4 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <button type="submit" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">Sign in</button>
        </form>
    </div>
</x-layout>
