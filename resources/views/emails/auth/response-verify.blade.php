@extends('layout.guest')

@section('content')
    @php
        $status = $status ?? 'info';
        $statusColors = [
            'success' => 'bg-green-100 border-green-400 text-green-700',
            'error' => 'bg-red-100 border-red-400 text-red-700',
            'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
            'info' => 'bg-blue-100 border-blue-400 text-blue-700',
        ];
        $statusClass = $statusColors[$status] ?? $statusColors['info'];
    @endphp

    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
            <div class="flex justify-center mb-4">
                @if ($status === 'success')
                    <div class="rounded-full bg-green-100 p-3">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                @elseif($status === 'error')
                    <div class="rounded-full bg-red-100 p-3">
                        <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                @else
                    <div class="rounded-full bg-blue-100 p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                @endif
            </div>

            <div class="flex justify-center mb-4">
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full {{ $statusClass }}">
                    @switch($status)
                        @case('success')
                            {{ __('Success') }}
                        @break

                        @case('error')
                            {{ __('Error') }}
                        @break

                        @default
                            {{ __('Information') }}
                    @endswitch
                </span>
            </div>

            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-2">
                    @switch($status)
                        @case('success')
                            {{ __('Email Verified Successfully') }}
                        @break

                        @case('error')
                            {{ __('Verification Failed') }}
                        @break

                        @default
                            {{ __('Verification') }}
                    @endswitch
                </h2>
                <p class="text-gray-600 text-sm">
                    {{ $message }}
                </p>
            </div>

            <div class="border-l-4 {{ $statusClass }} p-4 mb-6">
                <p class="font-medium">{{ $message }}</p>
            </div>
        </div>
    </div>
@endsection
