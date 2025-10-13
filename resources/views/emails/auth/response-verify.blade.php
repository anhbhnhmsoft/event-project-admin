@extends('layout.guest')

@section('content')
    @php
        $status = $status ?? 'info';
        $colors = [
            'success' => [
                'bg' => 'bg-green-50',
                'icon' => 'text-green-600',
                'border' => 'border-green-200',
                'title' => __('Xác thực thành công'),
                'button' => 'bg-green-600 hover:bg-green-700',
                'dark_bg' => 'dark:bg-green-900',
                'dark_text' => 'dark:text-green-300',
            ],
            'error' => [
                'bg' => 'bg-red-50',
                'icon' => 'text-red-600',
                'border' => 'border-red-200',
                'title' => __('Xác thực thất bại'),
                'button' => 'bg-red-600 hover:bg-red-700',
                'dark_bg' => 'dark:bg-red-900',
                'dark_text' => 'dark:text-red-300',
            ],
            'info' => [
                'bg' => 'bg-blue-50',
                'icon' => 'text-blue-600',
                'border' => 'border-blue-200',
                'title' => __('Thông tin xác thực'),
                'button' => 'bg-blue-600 hover:bg-blue-700',
                'dark_bg' => 'dark:bg-blue-900',
                'dark_text' => 'dark:text-blue-300',
            ],
        ];
        $color = $colors[$status] ?? $colors['info'];
    @endphp

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 px-4 py-8">
        <div class="w-full max-w-sm bg-white dark:bg-gray-800 shadow-xl rounded-3xl p-6 sm:p-8 text-center transition-all duration-300 hover:shadow-2xl">

            {{-- Logo --}}
            <div class="flex justify-center mb-8">
                <img src="{{ asset('images/logo-michec.png') }}" alt="MICHEC" class="h-10 sm:h-12 object-contain">
            </div>

            {{-- Icon with animation --}}
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="absolute inset-0 {{ $color['bg'] }} {{ $color['dark_bg'] }} rounded-full blur-xl opacity-50 animate-pulse"></div>
                    <div class="relative rounded-full {{ $color['bg'] }} {{ $color['dark_bg'] }} p-4 sm:p-5 border-2 {{ $color['border'] }} dark:border-opacity-50">
                        @if ($status === 'success')
                            <svg class="w-10 h-10 sm:w-12 sm:h-12 {{ $color['icon'] }} {{ $color['dark_text'] }} animate-bounce"
                                 xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 15.172l9.192-9.193 1.415 1.414L10 18 .393 8.393 1.807 6.979z"/>
                            </svg>
                        @elseif ($status === 'error')
                            <svg class="w-10 h-10 sm:w-12 sm:h-12 {{ $color['icon'] }} {{ $color['dark_text'] }} animate-pulse"
                                 xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                            </svg>
                        @else
                            <svg class="w-10 h-10 sm:w-12 sm:h-12 {{ $color['icon'] }} {{ $color['dark_text'] }} animate-spin"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.1" fill="none"/>
                                <path d="M12 2C6.48 2 2 6.48 2 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Title --}}
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-3 transition-colors">
                {{ $color['title'] }}
            </h2>

            {{-- Message --}}
            <p class="text-gray-600 dark:text-gray-300 text-base sm:text-lg leading-relaxed mb-8 transition-colors">
                {{ $message ?? __('Không có thông tin xác thực.') }}
            </p>
            {{-- Footer info --}}
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-8 transition-colors">
                {{ __('Nếu bạn gặp sự cố, vui lòng liên hệ với bộ phận hỗ trợ') }}
            </p>
        </div>
    </div>
@endsection
