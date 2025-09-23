<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>

<body class="bg-white min-h-[100vh]">
    <div class="w-full">
        {{ $slot }}
    </div>
    @livewireScripts
</body>

</html>
