<div>
    @assets
        @vite(['resources/css/app.css'])
    @endassets

    <div class="py-4">
        @if ($this->step)
            @if (empty($this->list))
                <x-filament::section class="max-w-4xl mx-auto">
                    <div class="text-center text-gray-500 dark:text-gray-400 p-8">
                        <p class="text-lg font-semibold">{{ __('admin.service_plan.no_plans') }}</p>
                        <p class="mt-2 text-sm">{{ __('admin.service_plan.contact_admin') }}</p>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section>
                    @if ($this->activePlan)
                        <x-filament::section class="max-w-4xl mx-auto mb-6 bg-primary-50 dark:bg-primary-900/10">
                            <x-slot name="heading">{{ __('admin.service_plan.active_time_remaining') }}</x-slot>
                            <div
                                class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-primary-700 dark:text-primary-300">

                                <div
                                    class="mt-3 sm:mt-0 text-sm font-semibold p-2 rounded-lg bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                    {{ __('admin.service_plan.activated_from') }}:
                                    <span class="font-mono">
                                        {{ \Carbon\Carbon::parse($this->activePlan->pivot->start_date)->format('d/m/Y') }}
                                    </span>
                                    - {{ __('admin.service_plan.expires') }}:
                                    <span class="font-mono">
                                        {{ \Carbon\Carbon::parse($this->activePlan->pivot->end_date)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        </x-filament::section>
                    @endif
                    <x-slot name="heading">{{ __('admin.service_plan.suitable_plans') }}</x-slot>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 pt-4">
                        @foreach ($this->list as $item)
                            <div
                                class="flex flex-col bg-white border border-gray-200 rounded-xl shadow-lg dark:bg-gray-800 dark:border-gray-700 hover:shadow-xl transition duration-300">
                                <div class="p-5 flex flex-col h-full">
                                    @if (!empty($item->badge))
                                        @php
                                            $badgeStyle = !empty($item->badge_color)
                                                ? "background-color: {$item->badge_color};"
                                                : 'background-color: #ccc';
                                        @endphp
                                        <x-filament::badge size="md" style="{{ $badgeStyle }}"
                                            class="mb-3 font-semibold self-start">
                                            <p class="text-gray-900 dark:text-gray-100">{{ $item->badge }}</p>
                                        </x-filament::badge>
                                    @endif

                                    <div class="flex flex-col gap-3 mb-4 flex-grow">
                                        <h5
                                            class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2">
                                            {{ $item->name }}
                                        </h5>

                                        <h6 class="text-4xl font-extrabold text-gray-900 dark:text-white">
                                            {{ number_format($item->price, 0, ',', '.') }}
                                            <span class="text-lg font-normal">VND</span>
                                        </h6>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('admin.service_plan.duration') }}: {{ $item->duration }} {{ __('admin.service_plan.months') }}
                                        </span>

                                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 italic">
                                            {{ $item->description }}
                                        </p>
                                    </div>

                                    <x-filament::button wire:key="buy-{{ $item->id }}" class="w-full mt-4"
                                        color="primary" icon="heroicon-m-shopping-cart"
                                        wire:click="onNextStep('{{ $item->id }}')">
                                        {{ __('admin.service_plan.buy') }}
                                    </x-filament::button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif
        @else
            <x-filament::section class="max-w-7xl mx-auto">
                <x-slot name="heading">{{ __('admin.service_plan.payment') }}</x-slot>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    {{-- Thông tin gói --}}
                    <div class="order-1 xl:order-2">
                        <div
                            class="w-full bg-white border border-gray-200 rounded-xl shadow-lg dark:bg-gray-800 dark:border-gray-700 h-fit">
                            <div class="p-4 sm:p-6">
                                @if (!empty($membership->badge))
                                    @php
                                        $badgeStyle = !empty($membership->badge_color)
                                            ? "background-color: {$membership->badge_color};"
                                            : 'background-color: #ccc';
                                    @endphp
                                    <x-filament::badge size="sm" style="{{ $badgeStyle }}" class="mb-3">
                                        <p class="text-gray-900 dark:text-gray-100 text-xs sm:text-sm">
                                            {{ $membership->badge }}
                                        </p>
                                    </x-filament::badge>
                                @endif

                                <div class="flex flex-col gap-4 mb-6">
                                    <h5
                                        class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                        {{ $membership->name }}
                                    </h5>

                                    <div class="flex flex-col sm:flex-row sm:items-baseline sm:gap-2">
                                        <h6 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                            {{ number_format($membership->price, 0, ',', '.') }} VND
                                        </h6>
                                        <span class="text-sm sm:text-base text-gray-500 dark:text-gray-400">
                                            / {{ $membership->duration }} tháng
                                        </span>
                                    </div>

                                    <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ $membership->description }}
                                    </p>
                                </div>

                                <div class="flex flex-col gap-3">
                                    <x-filament::button class="w-full" color="gray" icon="heroicon-m-arrow-left"
                                        wire:click="changeMembershipSelected()">
                                        {{ __('admin.service_plan.choose_other') }}
                                    </x-filament::button>

                                    @if ($paymentStatus == \App\Utils\Constants\TransactionStatus::WAITING->value)
                                        <x-filament::button class="w-full" color="danger" icon="heroicon-m-x-circle"
                                            wire:click="cancelTransaction"
                                            wire:confirm="{{ __('admin.service_plan.confirm_cancel_transaction') }}">
                                            {{ __('admin.service_plan.cancel_transaction') }}
                                        </x-filament::button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- QR Code thanh toán --}}
                    <div class="order-2 xl:order-1">
                        <div x-data="{ loading: true }"
                            x-on:payment-success.window="setTimeout(() => { $wire.redirectAfterSuccess() }, 1000)"
                            class="relative w-full max-w-md mx-auto aspect-square">

                            @if ($paymentStatus == \App\Utils\Constants\TransactionStatus::SUCCESS->value)
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-green-50 dark:bg-green-900/20 rounded-xl border-2 border-green-200 dark:border-green-800">
                                    <div class="text-green-600 dark:text-green-400 mb-3">
                                        <x-heroicon-s-check-circle class="w-12 h-12 sm:w-16 sm:h-16" />
                                    </div>
                                    <h3
                                        class="text-lg sm:text-xl font-semibold text-green-800 dark:text-green-200 text-center px-4">
                                        {{ __('admin.service_plan.payment_success') }}!
                                    </h3>
                                    <p class="text-sm text-green-600 dark:text-green-400 text-center mt-1">
                                        {{ __('admin.service_plan.redirecting') }}...
                                    </p>
                                </div>
                            @elseif($paymentStatus == \App\Utils\Constants\TransactionStatus::WAITING->value)
                                <template x-if="loading">
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-xl">
                                        <x-filament::loading-indicator class="w-8 h-8 text-primary-600" />
                                    </div>
                                </template>

                                @if (!empty($dataTransfer['urlBankQrcode']))
                                    <img src="{{ $dataTransfer['urlBankQrcode'] }}" alt="{{ __('admin.service_plan.qr_code_payment') }}"
                                        class="w-full h-full object-contain rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
                                        x-bind:class="{ 'opacity-0': loading, 'opacity-100': !loading }"
                                        x-on:load="loading = false" x-on:error="loading = false"
                                        style="transition: opacity 0.3s ease;" />
                                @endif
                            @endif
                        </div>

                        {{-- Trạng thái thanh toán --}}
                        <div class="mt-6 bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
                            wire:poll.2s="refreshOrder">
                            <div class="text-center">
                                @if ($paymentStatus == \App\Utils\Constants\TransactionStatus::WAITING->value)
                                    <div class="flex flex-col items-center gap-3">
                                        <span
                                            class="text-yellow-600 dark:text-yellow-400 font-semibold flex items-center justify-center gap-2 text-sm sm:text-base">
                                            <x-heroicon-s-clock class="w-5 h-5" />
                                            {{ __('admin.service_plan.waiting_payment') }}...
                                        </span>

                                        {{-- Countdown section --}}
                                        <div wire:ignore.self wire:key="countdown-{{ $expiryTime ?? 'none' }}"
                                            x-data="{
                                                expiryTime: {{ $expiryTime ?? 'null' }},
                                                remaining: '{{ __('admin.service_plan.loading') }}',
                                                isExpired: false,
                                                intervalId: null,

                                                init() {
                                                    this.startCountdown();
                                                },

                                                startCountdown() {
                                                    if (this.intervalId) clearInterval(this.intervalId);
                                                    this.updateCountdown();
                                                    this.intervalId = setInterval(() => this.updateCountdown(), 1000);
                                                },

                                                updateCountdown() {
                                                    if (!this.expiryTime) {
                                                        this.remaining = '{{ __('admin.service_plan.unknown') }}';
                                                        return;
                                                    }
                                                    const now = Math.floor(Date.now() / 1000);
                                                    const diff = this.expiryTime - now;

                                                    if (diff <= 0) {
                                                        this.remaining = '{{ __('admin.service_plan.expired') }}';
                                                        this.isExpired = true;
                                                        clearInterval(this.intervalId);
                                                        this.intervalId = null;
                                                        $wire.checkExpiry();
                                                    } else {
                                                        const m = Math.floor(diff / 60);
                                                        const s = diff % 60;
                                                        this.remaining = `${m}:${s.toString().padStart(2, '0')}`;
                                                        this.isExpired = false;
                                                    }
                                                }
                                            }"
                                            x-on:livewire:navigating.window="if (intervalId) clearInterval(intervalId)"
                                            class="w-full">
                                            <div class="px-4 py-3 rounded-lg border transition-colors"
                                                :class="{
                                                    'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800': isExpired,
                                                    'bg-gray-100 dark:bg-gray-700 border-gray-200 dark:border-gray-600':
                                                        !isExpired
                                                }">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.service_plan.time_remaining') }}</p>
                                                <p class="text-2xl font-bold font-mono transition-colors"
                                                    :class="{
                                                        'text-red-600 dark:text-red-400': isExpired,
                                                        'text-gray-900 dark:text-gray-100': !isExpired
                                                    }"
                                                    x-text="remaining">
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-2">
                                            <x-heroicon-s-information-circle class="w-4 h-4" />
                                            <span>{{ __('admin.service_plan.auto_cancel') }}</span>
                                        </div>
                                    </div>
                                @elseif ($paymentStatus == \App\Utils\Constants\TransactionStatus::SUCCESS->value)
                                    <span
                                        class="text-green-600 dark:text-green-400 font-semibold flex items-center justify-center gap-2 text-sm sm:text-base">
                                        <x-heroicon-s-check-badge class="w-5 h-5" />
                                        {{ __('admin.service_plan.payment_success') }}!
                                    </span>
                                @elseif ($paymentStatus == \App\Utils\Constants\TransactionStatus::CANCELLED->value)
                                    <span
                                        class="text-orange-600 dark:text-orange-400 font-semibold flex items-center justify-center gap-2 text-sm sm:text-base">
                                        <x-heroicon-s-x-circle class="w-5 h-5" />
                                        <span class="text-center">{{ __('admin.service_plan.transaction_cancelled') }}</span>
                                    </span>
                                @else
                                    <span
                                        class="text-red-600 dark:text-red-400 font-semibold flex items-center justify-center gap-2 text-sm sm:text-base">
                                        <x-heroicon-s-x-circle class="w-5 h-5" />
                                        <span class="text-center">{{ __('admin.service_plan.payment_failed') }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</div>
