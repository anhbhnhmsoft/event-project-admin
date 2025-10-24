<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MICHEC: Hệ thống Tổ chức Sự kiện Toàn diện | Đăng ký, Check-in, Tương tác</title>
    <meta name="description"
        content="MICHEC là nền tảng quản lý sự kiện số hóa từ A-Z, giúp tối ưu hóa quy trình lập kế hoạch, đăng ký, check-in QR/Face Recognition và tương tác khách mời. Yêu cầu demo ngay!">
    <meta name="keywords"
        content="hệ thống tổ chức sự kiện, quản lý sự kiện, phần mềm check-in sự kiện, đăng ký sự kiện, event management system, MICHEC">
    <meta name="author" content="Michec Team">

    <meta property="og:title" content="MICHEC: Hệ thống Tổ chức Sự kiện Toàn diện | Tối ưu hóa A-Z">
    <meta property="og:description"
        content="Số hóa mọi quy trình sự kiện: đăng ký tự động, check-in nhanh chóng, tương tác trực tiếp và phân tích chuyên sâu. Tăng trải nghiệm khách mời, giảm chi phí vận hành.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://michec.vn/">
    <meta property="og:image" content="/logo-michec.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@michec">
    <meta name="twitter:title" content="MICHEC: Hệ thống Tổ chức Sự kiện Toàn diện | Đăng ký, Check-in, Tương tác">
    <meta name="twitter:description"
        content="Tối ưu hóa lập kế hoạch, đăng ký, check-in QR/Face Recognition, tương tác và báo cáo sự kiện với nền tảng MICHEC.">
    <meta name="twitter:image" content="/logo-michec.png">
    <link rel="stylesheet" href="/vendor/filament/filament/app.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="/images/logo-michec-icon.png" sizes="32x32">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>

<body style="color-scheme: light;" class="bg-white min-h-[100vh]">
    <div class="w-full">
        {{ $slot }}
    </div>
    @livewireScripts
    <script src="/vendor/filament/filament/app.js"></script>
</body>

</html>
