<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('app.meta.title') }}</title>
    <meta name="description"
        content="{{ __('app.meta.description') }}">
    <meta name="keywords"
        content="{{ __('app.meta.keywords') }}">
    <meta name="author" content="{{ __('app.meta.author') }}">

    <meta property="og:title" content="{{ __('app.meta.og_title') }}">
    <meta property="og:description"
        content="{{ __('app.meta.og_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://michec.vn/">
    <meta property="og:image" content="/logo-michec.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@michec">
    <meta name="twitter:title" content="{{ __('app.meta.twitter_title') }}">
    <meta name="twitter:description"
        content="{{ __('app.meta.twitter_description') }}">
    <meta name="twitter:image" content="/logo-michec.png">
    <link rel="stylesheet" href="/vendor/filament/filament/app.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="/images/logo-michec-icon.png" sizes="32x32">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/screen.js'])
    @livewireStyles
</head>

<body style="color-scheme: light;" class="bg-white min-h-[100vh]">
    <div class="w-full">
        @yield('content')
    </div>
    @livewireScripts
</body>