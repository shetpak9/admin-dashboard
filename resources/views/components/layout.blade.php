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
    <x-layout.nav>

    </x-layout.nav>

    <main class="max-w-7xl mx-auto px-6 flex flex-col items-center">
        <h1 class="text-3xl font-bold text-gray-900 mt-8">Admin Dashboard</h1>
        {{ $slot }}
    </main>
</body>
</html>
