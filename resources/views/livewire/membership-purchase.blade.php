<div class="min-h-screen bg-gray-50 py-8 px-4">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-4xl mx-auto">
        {{-- Inline Notification Banner --}}
        @if($notificationMessage)
            <div class="mb-6 rounded-lg p-4 flex items-start justify-between {{
                $notificationType === 'success' ? 'bg-green-50 border border-green-200' :
                ($notificationType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200')
            }}">
                <div class="flex items-start">
                    @if($notificationType === 'success')
                        <svg class="h-5 w-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                  clip-rule="evenodd"/>
                        </svg>
                    @elseif($notificationType === 'error')
                        <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                  clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                    @endif
                    <p class="ml-3 text-sm font-medium {{
                        $notificationType === 'success' ? 'text-green-800' :
                        ($notificationType === 'error' ? 'text-red-800' : 'text-yellow-800')
                    }}">
                        {{ $notificationMessage }}
                    </p>
                </div>
                <button wire:click="clearNotification" class="ml-4 text-gray-400 hover:text-gray-600 cursor-pointer">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                              clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Stage 1: Authentication Form --}}
        @if($currentStage === 1)
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="w-full flex justify-center">
                    <img src="{{ asset('images/logo-michec.png') }}" class="w-1/6 h-20 d-inline-block mb-6 mx-auto" alt="">
                </div>
                <livewire:language-switcher></livewire:language-switcher>

                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    {{ __('membership_purchase.auth.title') }}
                </h2>

                <form wire:submit="authenticate" class="space-y-6">
                    {{-- Username Field --}}
                    @csrf
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('membership_purchase.auth.username') }}
                        </label>
                        <input
                            type="text"
                            id="username"
                            wire:model="username"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="{{ __('membership_purchase.auth.username_placeholder') }}"
                            required>
                        @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Field --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('membership_purchase.auth.password') }}
                        </label>
                        <input
                            type="password"
                            id="password"
                            wire:model="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="{{ __('membership_purchase.auth.password_placeholder') }}"
                            required>
                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Organization Field --}}
                    <div>
                        <label for="organizerInput" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('membership_purchase.auth.organization') }}
                        </label>
                        <select
                            id="organizerInput"
                            wire:model="organizerInput"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                            required>
                            <option value="">{{ __('membership_purchase.auth.select_organization') }}</option>
                            @foreach($organizerList as $organizer)
                                <option value="{{ $organizer['id'] }}">{{ $organizer['name'] }}</option>
                            @endforeach
                        </select>
                        @error('organizerInput')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium cursor-pointer">
                        {{ __('membership_purchase.auth.login_button') }}
                    </button>
                </form>
            </div>
        @endif

        {{-- Stage 2: Membership List --}}
        @if($currentStage === 2)
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ __('membership_purchase.list.title') }}
                    </h2>
                    <button
                        wire:click="logout"
                        class="text-sm text-gray-600 hover:text-gray-800 cursor-pointer">
                        {{ __('membership_purchase.auth.logout') }}
                    </button>
                </div>

                @if(empty($membershipList))
                    <div class="text-center py-12">
                        <p class="text-gray-500">{{ __('membership_purchase.list.no_memberships') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($membershipList as $membership)
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                                {{-- Badge --}}
                                @if(isset($membership['badge']))
                                    <div class="inline-block px-3 py-1 rounded-full text-sm font-medium mb-3"
                                         style="background-color: {{ $membership['badge_color_background'] }}; color: {{ $membership['badge_color_text'] }}">
                                        {{ $membership['badge'] }}
                                    </div>
                                @endif

                                {{-- Name --}}
                                <h3 class="text-xl font-bold text-gray-800 mb-2">
                                    {{ $membership['name'] }}
                                </h3>

                                {{-- Description --}}
                                @if(isset($membership['description']))
                                    <p class="text-gray-600 mb-4">
                                        {{ $membership['description'] }}
                                    </p>
                                @endif

                                {{-- Price & Duration --}}
                                <div class="mb-4">
                                    <p class="text-3xl font-bold text-blue-600">
                                        {{ number_format($membership['price']) }} <span class="text-sm text-gray-500">VND</span>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ __('membership_purchase.list.duration', ['days' => $membership['duration']]) }}
                                    </p>
                                </div>

                                {{-- Features --}}
                                @if(isset($membership['config']))
                                    <div class="mb-4 space-y-2">
                                        @foreach($membership['config'] as $key => $value)
                                            @if($value)
                                                <div class="flex items-center text-sm text-gray-600">
                                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor"
                                                         viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                              clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ __('membership_purchase.list.features.' . $key) }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Select Button --}}
                                <button
                                    wire:click="selectMembership('{{ $membership['id'] }}')"
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium cursor-pointer">
                                    {{ __('membership_purchase.list.select_button') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Stage 3: QR Payment --}}
        @if($currentStage === 3)
            <div class="bg-white rounded-lg shadow-md p-8" wire:poll.5s="refreshPaymentStatus">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    {{ __('membership_purchase.payment.title') }}
                </h2>

                {{-- Membership Summary --}}
                @if($selectedMembership)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-gray-800 mb-2">
                            {{ $selectedMembership['name'] }}
                        </h3>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ number_format($selectedMembership['price']) }} <span
                                class="text-sm text-gray-500">VND</span>
                        </p>
                    </div>
                @endif

                {{-- Payment Status --}}
                @if($paymentStatus === \App\Utils\Constants\TransactionStatus::WAITING->value)
                    <div class="text-center mb-6">
                        {{-- QR Code --}}
                        @if(isset($paymentData['urlBankQrcode']))
                            <div class="mb-4">
                                <img
                                    src="{{ $paymentData['urlBankQrcode'] }}"
                                    alt="QR Code"
                                    class="mx-auto w-64 h-64 border-2 border-gray-200 rounded-lg">
                            </div>
                        @endif

                        <p class="text-lg font-medium text-gray-700 mb-2">
                            {{ __('membership_purchase.payment.scan_qr') }}
                        </p>

                        {{-- Payment Details --}}
                        <div class="bg-gray-50 rounded-lg p-4 text-left mb-4">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('membership_purchase.payment.amount') }}:</span>
                                    <span
                                        class="font-semibold">{{ number_format($paymentData['amount'] ?? 0) }} đ</span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600">{{ __('membership_purchase.payment.account_number') }}:</span>
                                    <span class="font-semibold">{{ $paymentData['accountNumber'] ?? '' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600">{{ __('membership_purchase.payment.account_name') }}:</span>
                                    <span class="font-semibold">{{ $paymentData['accountName'] ?? '' }}</span>
                                </div>
                                @if(isset($paymentData['bankName']))
                                    <div class="flex justify-between">
                                        <span
                                            class="text-gray-600">{{ __('membership_purchase.payment.bank_name') }}:</span>
                                        <span class="font-semibold">{{ $paymentData['bankName'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Countdown Timer --}}
                        <div class="mb-4" x-data="{
                            expiryTime: @entangle('expiryTime'),
                            timeLeft: 0,
                            interval: null,
                            init() {
                                this.updateTimer();
                                this.interval = setInterval(() => this.updateTimer(), 1000);
                            },
                            updateTimer() {
                                const now = Math.floor(Date.now() / 1000);
                                this.timeLeft = Math.max(0, this.expiryTime - now);
                                if (this.timeLeft === 0 && this.interval) {
                                    clearInterval(this.interval);
                                }
                            },
                            formatTime() {
                                const minutes = Math.floor(this.timeLeft / 60);
                                const seconds = this.timeLeft % 60;
                                return `${minutes}:${seconds.toString().padStart(2, '0')}`;
                            }
                        }">
                            <p class="text-sm text-gray-600">
                                {{ __('membership_purchase.payment.expires_in') }}:
                                <span class="font-semibold text-red-600" x-text="formatTime()"></span>
                            </p>
                        </div>

                        {{-- Loading Indicator --}}
                        <div class="flex items-center justify-center mb-4">
                            <svg class="animate-spin h-5 w-5 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">{{ __('membership_purchase.payment.waiting') }}</span>
                        </div>
                    </div>

                    {{-- Cancel Button --}}
                    <button
                        wire:click="cancelTransaction"
                        class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors font-medium cursor-pointer">
                        {{ __('membership_purchase.payment.cancel') }}
                    </button>

                @elseif($paymentStatus === \App\Utils\Constants\TransactionStatus::FAILED->value)
                    <div class="text-center py-8">
                        <div class="text-red-600 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">
                            {{ __('membership_purchase.payment.failed_title') }}
                        </h3>
                        <p class="text-gray-600 mb-6">
                            {{ __('membership_purchase.payment.failed_message') }}
                        </p>
                        <button
                            wire:click="backToMembershipList"
                            class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors font-medium cursor-pointer">
                            {{ __('membership_purchase.payment.try_again') }}
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Stage 4: Success --}}
        @if($currentStage === 4)
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="text-center py-8">
                    {{-- Success Icon --}}
                    <div class="text-green-600 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>

                    {{-- Success Title --}}
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        {{ __('membership_purchase.success.title') }}
                    </h2>

                    {{-- Success Message --}}
                    <p class="text-gray-600 mb-6">
                        {{ __('membership_purchase.success.message') }}
                    </p>

                    {{-- Membership Details --}}
                    @if($selectedMembership)
                        <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                            <h3 class="font-semibold text-gray-800 mb-4">
                                {{ __('membership_purchase.success.membership_details') }}
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600">{{ __('membership_purchase.success.membership_name') }}:</span>
                                    <span class="font-semibold">{{ $selectedMembership->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600">{{ __('membership_purchase.success.amount_paid') }}:</span>
                                    <span class="font-semibold">{{ number_format($selectedMembership->price) }} đ</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('membership_purchase.success.duration') }}:</span>
                                    <span
                                        class="font-semibold">{{ $selectedMembership->duration }} {{ __('membership_purchase.success.days') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Notification Sent --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-800">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                            {{ __('membership_purchase.success.notification_sent') }}
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-3">
                        <button
                            wire:click="resetAll"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium cursor-pointer">
                            {{ __('membership_purchase.success.return_home') }}
                        </button>
                        <button
                            wire:click="backToMembershipList"
                            class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors font-medium cursor-pointer">
                            {{ __('membership_purchase.success.purchase_another') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Livewire Scripts for Alpine.js --}}
    @script
    <script>
        Livewire.on('updateExpiryTime', (expiryTime) => {
            // Timer will be handled by Alpine.js x-data
        });
    </script>
    @endscript
</div>
