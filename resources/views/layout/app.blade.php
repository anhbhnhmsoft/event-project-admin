<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Cơ bản --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bảo mật --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO cơ bản --}}
    <title inertia>{{ $meta['title'] ?? 'EventApp - Hệ thống quản lý sự kiện chuyên nghiệp' }}</title>
    <meta name="description"
        content="{{ $meta['description'] ?? 'EventApp giúp bạn tổ chức và quản lý sự kiện một cách dễ dàng, nhanh chóng và hiệu quả.' }}">
    <meta name="keywords" content="EventApp, quản lý sự kiện, đăng ký tổ chức, hội nghị, event manager, booking">

    {{-- Open Graph (Facebook, Zalo, LinkedIn) --}}
    <meta property="og:title" content="{{ $meta['title'] ?? 'EventApp - Hệ thống quản lý sự kiện chuyên nghiệp' }}">
    <meta property="og:description"
        content="{{ $meta['description'] ?? 'EventApp giúp bạn tổ chức và quản lý sự kiện một cách dễ dàng, nhanh chóng và hiệu quả.' }}">
    <meta property="og:image" content="{{ asset('images/share-banner.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="vi_VN">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta['title'] ?? 'EventApp - Hệ thống quản lý sự kiện chuyên nghiệp' }}">
    <meta name="twitter:description"
        content="{{ $meta['description'] ?? 'EventApp giúp bạn tổ chức và quản lý sự kiện một cách dễ dàng, nhanh chóng và hiệu quả.' }}">
    <meta name="twitter:image" content="{{ asset('images/share-banner.png') }}">

    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="icon" href="/images/logo-michec-icon.png" sizes="32x32" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        @inertia
        @yield('content')
    </div>
</body>

</html>
