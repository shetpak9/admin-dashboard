<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-center">
        <div class="flex gap-2">

            <!-- Add Product Link -->
            <a href="/add" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                {{ request()->is('add') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                Add Product
            </a>

            <!-- View Product Link (Assuming the route is just '/') -->
            <a href="/" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                {{ request()->is('/') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                View Product
            </a>
            <a href="{{ route('users.index') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                {{ request()->is('users*') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                Users
            </a>
            <a href="{{ route('audit-log.index') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                {{ request()->is('audit-log') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                Audit log
            </a>
        </div>
        <div class="ml-auto flex items-center gap-2">
            @if (session()->get('admin_authenticated') === true)
                <form method="POST" action="{{ route('session.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                        Sign out
                    </button>
                </form>
            @endif

        </div>
    </div>
</nav>
