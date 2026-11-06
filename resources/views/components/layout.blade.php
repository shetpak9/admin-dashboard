<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Admin DashBoard</title>
</head>
<body class="bg-gray-300">
    <x-layout.nav></x-layout.nav>

    <main class="max-w-7xl mx-auto px-6 flex flex-col items-center">
        @if (session('success'))
            <div class="w-full mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="w-full mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="w-full mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <h1 class="text-3xl font-bold text-gray-900 mt-8">Admin Dashboard</h1>
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
