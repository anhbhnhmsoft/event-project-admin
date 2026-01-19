<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('app.meta.title') }}</title>
    <meta name="description" content="{{ __('app.meta.description') }}">
    <meta name="keywords" content="{{ __('app.meta.keywords') }}">
    <meta name="author" content="{{ __('app.meta.author') }}">

    <meta property="og:title" content="{{ __('app.meta.og_title') }}">
    <meta property="og:description" content="{{ __('app.meta.og_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://michec.vn/">
    <meta property="og:image" content="/logo-michec.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@michec">
    <meta name="twitter:title" content="{{ __('app.meta.twitter_title') }}">
    <meta name="twitter:description" content="{{ __('app.meta.twitter_description') }}">
    <meta name="twitter:image" content="/logo-michec.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="/images/logo-michec-icon.png" sizes="32x32">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-['Inter'] bg-white min-h-screen pt-20 text-[#333333]">
    <!-- Thanh điều hướng cố định (Fixed Header) -->
    <header class="fixed top-0 left-0 w-full bg-[#1f73b7]/95 backdrop-blur-md shadow-lg px-5 py-4 z-[1000]">
        <div class="max-w-[1200px] mx-auto flex justify-between items-center">
            <!-- Logo -->
            <div class="text-[28px] font-extrabold text-white tracking-tight cursor-pointer">
                MI<span class="text-[#5b9bd5]">CHEC</span>
            </div>

            <!-- Nút Đăng nhập, Đăng ký và Đổi ngôn ngữ -->
            <div class="flex items-center gap-4">
                <div class="flex gap-2.5">
                    <a href="/admin/login"
                        class="px-[18px] py-2 rounded-md font-semibold text-[15px] border-2 border-white/50 text-white hover:bg-white/10 hover:border-white transition-all duration-300 no-underline">
                        {{ __('app.nav.login') }}
                    </a>
                    <a href="{{ route('signup') }}"
                        class="px-[18px] py-2 rounded-md font-semibold text-[15px] bg-[#5b9bd5] text-white shadow-[0_4px_10px_rgba(91,155,213,0.3)] hover:bg-[#4784c0] hover:-translate-y-px transition-all duration-300 no-underline">
                        {{ __('app.nav.register') }}
                    </a>
                </div>

                <!-- Language Switcher -->
                <div class="flex items-center gap-2 ml-2 border-l border-white/30 pl-4">
                    <a href="{{ route('lang.switch', 'vi') }}"
                        class="text-sm font-bold transition-colors duration-200 {{ app()->getLocale() === 'vi' ? 'text-white' : 'text-white/60 hover:text-white' }}">
                        VI
                    </a>
                    <span class="text-white/40 text-sm">|</span>
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="text-sm font-bold transition-colors duration-200 {{ app()->getLocale() === 'en' ? 'text-white' : 'text-white/60 hover:text-white' }}">
                        EN
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Nội dung chính (Main Content) -->
    <div class="flex items-center justify-center py-10 px-5">
        <div class="w-full max-w-[1200px]">
            <div class="text-center mb-[50px]">
                <!-- Tagline mới, sử dụng màu xanh -->
                <div
                    class="text-[#1f73b7] text-lg font-semibold tracking-wide px-4 py-1.5 inline-block bg-[#f4f7f9] rounded-[20px] border border-[#5b9bd5]">
                    {{ __('app.welcome.tagline') }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-[60px] items-center mt-[60px]">
                <div class="text-center md:text-left">
                    <h1 class="text-[#1f73b7] text-[40px] md:text-[52px] font-extrabold leading-[1.15] mb-[25px]">
                        {{ __('app.welcome.main_title') }}
                    </h1>
                    <p class="text-[#555555] text-[16px] md:text-[18px] leading-relaxed mb-[30px] text-left">
                        {{ __('app.welcome.main_description') }}
                    </p>

                    <div class="flex flex-col gap-5 mb-10 items-start">
                        <!-- <div class="flex gap-3 items-start">
                            <div
                                class="w-7 h-7 bg-[#5b9bd5] rounded-full flex items-center justify-center shrink-0 text-white font-extrabold text-base shadow-[0_4px_8px_rgba(91,155,213,0.2)] mt-0.5">
                                ✓
                            </div>
                            <div class="text-[#333333] text-base leading-normal text-left">
                                {{ __('app.welcome.feature_registration') }}
                            </div>
                        </div> -->
                        <div class="flex gap-3 items-start">
                            <div
                                class="w-7 h-7 bg-[#5b9bd5] rounded-full flex items-center justify-center shrink-0 text-white font-extrabold text-base shadow-[0_4px_8px_rgba(91,155,213,0.2)] mt-0.5">
                                ✓
                            </div>
                            <div class="text-[#333333] text-base leading-normal text-left">
                                {{ __('app.welcome.feature_checkin') }}
                            </div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <div
                                class="w-7 h-7 bg-[#5b9bd5] rounded-full flex items-center justify-center shrink-0 text-white font-extrabold text-base shadow-[0_4px_8px_rgba(91,155,213,0.2)] mt-0.5">
                                ✓
                            </div>
                            <div class="text-[#333333] text-base leading-normal text-left">
                                {{ __('app.welcome.feature_interaction') }}
                            </div>
                        </div>

                    </div>

                    <div class="flex gap-4 flex-wrap justify-center md:justify-start">
                        <a href="{{ route('membership.purchase') }}"
                            class="px-9 py-4 rounded-[10px] font-bold text-base bg-white text-[#1f73b7] border-2 border-[#1f73b7] shadow-[0_4px_10px_rgba(0,0,0,0.05)] hover:bg-[#1f73b7] hover:text-white hover:border-[#1f73b7] hover:-translate-y-0.5 hover:shadow-[0_6px_15px_rgba(31,115,183,0.2)] transition-all duration-300 no-underline">
                            {{ __('app.welcome.register_member') }}
                        </a>
                    </div>
                </div>

                <div class="relative h-auto md:h-[400px] flex items-center justify-center">
                    <div
                        class="bg-white rounded-2xl p-10 shadow-[0_15px_40px_rgba(31,115,183,0.15)] text-center border border-[#e0e0e0] w-full">
                        <h3 class="text-[#1f73b7] text-2xl font-bold mb-4">{{ __('app.welcome.showcase_title') }}
                        </h3>
                        <p class="text-[#555555] text-[15px] leading-relaxed mb-6">{{ __('app.welcome.showcase_description') }}</p>

                        <div
                            class="flex flex-col sm:flex-row justify-around py-6 border-t border-b border-[#f0f0f0] my-6 gap-5 sm:gap-0">
                            <div class="text-center flex-1">
                                <div class="text-[32px] font-extrabold text-[#5b9bd5]">500+</div>
                                <div class="text-[13px] text-[#555555] mt-1">{{ __('app.welcome.stat_events') }}</div>
                            </div>
                            <div class="text-center flex-1">
                                <div class="text-[32px] font-extrabold text-[#5b9bd5]">200K+</div>
                                <div class="text-[13px] text-[#555555] mt-1">{{ __('app.welcome.stat_guests') }}</div>
                            </div>
                        </div>

                        <div
                            class="inline-block bg-[#f4f7f9] text-[#1f73b7] px-4 py-1.5 rounded-[20px] text-[13px] font-bold mt-4 border border-[#5b9bd5]">
                            {{ __('app.welcome.badge_text') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
