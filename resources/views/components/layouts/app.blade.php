<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css'])
    <link rel="icon" type="image/svg+xml" href="/images/logo-michec-icon.png">
    @livewireStyles
</head>

<body style="color-scheme: light;" class="bg-white min-h-[100vh]">
    <div class="w-full">
        {{ $slot }}
    </div>
    @livewireScripts
</body>

</html>
