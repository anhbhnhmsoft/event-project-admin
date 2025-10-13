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
            ],
            'error' => [
                'bg' => 'bg-red-50',
                'icon' => 'text-red-600',
                'border' => 'border-red-200',
                'title' => __('Xác thực thất bại'),
                'button' => 'bg-red-600 hover:bg-red-700',
            ],
            'info' => [
                'bg' => 'bg-blue-50',
                'icon' => 'text-blue-600',
                'border' => 'border-blue-200',
                'title' => __('Thông tin xác thực'),
                'button' => 'bg-blue-600 hover:bg-blue-700',
            ],
        ];
        $color = $colors[$status] ?? $colors['info'];
    @endphp

    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md text-center">
            {{-- Logo --}}
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo-michec.png') }}" alt="MICHEC" class="h-10">
            </div>

            {{-- Icon --}}
            <div class="flex justify-center mb-4">
                <div class="rounded-full {{ $color['bg'] }} p-3 border {{ $color['border'] }}">
                    @if ($status === 'success')
                        <svg class="w-8 h-8 {{ $color['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @elseif ($status === 'error')
                        <svg class="w-8 h-8 {{ $color['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @else
                        <svg class="w-8 h-8 {{ $color['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 18h.01M12 6h.01M12 8h.01" />
                        </svg>
                    @endif
                </div>
            </div>

            {{-- Title & Message --}}
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ $color['title'] }}</h2>
            <p class="text-gray-600 text-sm mb-6">{{ $message ?? __('Không có thông tin xác thực.') }}</p>
        </div>
    </div>
@endsection
