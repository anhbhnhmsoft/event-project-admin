<div>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="my-3">
                <a href="{{ route('home') }}">
                    <img src="/images/logo-michec.png" class="max-w-68 mx-auto" alt="">
                </a>
            </div>
            @livewire(\Filament\Notifications\Livewire\Notifications::class)
            <div class="mb-8">
                <div class="flex items-center justify-center">
                    <div class="flex items-center w-full max-w-4xl">
                        <div class="flex items-center flex-1">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all
                                {{ $currentStage >= 1 ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600' }}">
                                @if ($currentStage > 1)
                                    <x-heroicon-s-check class="w-6 h-6 text-blue-600" />
                                @else
                                    <span
                                        class="text-sm font-semibold {{ $currentStage == 1 ? 'text-blue-600' : 'text-gray-500' }}">1</span>
                                @endif
                            </div>
                            <div class="ml-3 hidden sm:block">
                                <p
                                    class="text-sm font-medium {{ $currentStage >= 1 ? 'text-primary-600' : 'text-white' }}">
                                    {{ __('organizer.signup.steps.choose_plan') }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex-1 h-0.5 {{ $currentStage >= 2 ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                        </div>

                        {{-- Step 2 --}}
                        <div class="flex items-center flex-1">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all
                                {{ $currentStage >= 2 ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600' }}">
                                @if ($currentStage > 2)
                                    <x-heroicon-s-check class="w-6 h-6 text-blue-600" />
                                @else
                                    <span
                                        class="text-sm font-semibold {{ $currentStage == 2 ? 'text-blue-600' : 'text-gray-500' }}">2</span>
                                @endif
                            </div>
                            <div class="ml-3 hidden sm:block">
                                <p
                                    class="text-sm font-medium {{ $currentStage >= 2 ? 'text-primary-600' : 'text-gray-500' }}">
                                    {{ __('organizer.signup.steps.register') }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex-1 h-0.5 {{ $currentStage >= 3 ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                        </div>

                        {{-- Step 3 --}}
                        <div class="flex items-center flex-1">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all
                                {{ $currentStage >= 3 ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600' }}">
                                @if ($currentStage > 3)
                                    <x-heroicon-s-check class="w-6 h-6 text-blue-600" />
                                @else
                                    <span
                                        class="text-sm font-semibold {{ $currentStage == 3 ? 'text-blue-600' : 'text-gray-500' }}">3</span>
                                @endif
                            </div>
                            <div class="ml-3 hidden sm:block">
                                <p
                                    class="text-sm font-medium {{ $currentStage >= 3 ? 'text-primary-600' : 'text-gray-500' }}">
                                    {{ __('organizer.signup.steps.payment') }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex-1 h-0.5 {{ $currentStage >= 4 ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                        </div>

                        {{-- Step 4 --}}
                        <div class="flex items-center">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all
                                {{ $currentStage >= 4 ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600' }}">
                                <span
                                    class="text-sm font-semibold {{ $currentStage == 4 ? 'text-blue-600' : 'text-gray-500' }}">4</span>
                            </div>
                            <div class="ml-3 hidden sm:block">
                                <p
                                    class="text-sm font-medium {{ $currentStage >= 4 ? 'text-primary-600' : 'text-gray-500' }}">
                                    {{ __('organizer.signup.steps.complete') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STAGE 1: SELECT PLAN --}}
            @if ($currentStage == 1)
                <div class="max-w-6xl mx-auto">
                    {{-- Header --}}
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-gray-900 dark:text-blue-600 mb-4">
                            {{ __('organizer.signup.choose_plan_title') }}
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-white">
                            {{ __('organizer.signup.choose_plan_desc') }}
                        </p>
                    </div>

                    @if (empty($planList))
                        <div class="text-center text-gray-500 dark:text-white p-8">
                            <p class="text-lg font-semibold">{{ __('organizer.signup.no_plans') }}</p>
                            <p class="mt-2 text-sm">{{ __('organizer.signup.come_back_later') }}</p>
                        </div>
                    @else
                        {{-- Plans Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            @foreach ($planList as $index => $plan)
                                <div class="relative flex flex-col bg-white rounded-2xl transition-all duration-300 cursor-pointer
                                                                                                {{ !empty($plan->badge) ? 'border-3 border-blue-500 shadow-2xl scale-105 dark:border-blue-400' : 'border border-gray-200 shadow-lg hover:shadow-xl dark:border-gray-700' }}
                                                                                                dark:bg-gray-800">

                                    {{-- Popular Badge --}}
                                    @if (!empty($plan->badge))
                                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                            <span
                                                class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold text-blue-600 bg-blue-600 shadow-lg uppercase tracking-wide">
                                                {{ $plan->badge }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="p-8 flex flex-col h-full">
                                        {{-- Plan Name --}}
                                        <h3 class="text-2xl font-bold text-gray-900 dark:text-blue-600 mb-2 text-center">
                                            {{ $plan->name }}
                                        </h3>

                                        {{-- Price --}}
                                        <div class="text-center mb-6">
                                            <div class="flex items-baseline justify-center gap-1">
                                                <span class="text-5xl font-bold text-gray-900 dark:text-blue-600">
                                                    {{ number_format($plan->price, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <p class="text-gray-600 dark:text-white mt-1">{{ __('organizer.signup.vnd') }}</p>
                                            <p class="text-sm text-gray-500 dark:text-white mt-2">
                                                {{ __('organizer.signup.duration') }}: **{{ $plan->duration }}
                                                {{ __('organizer.signup.months') }}**
                                            </p>
                                        </div>

                                        {{-- Features List --}}
                                        <div class="flex-grow mb-6 space-y-3">
                                            @if (!empty($plan->description))
                                                @php
                                                    $features = explode("\n", $plan->description);
                                                @endphp
                                                @foreach ($features as $feature)
                                                    @if (trim($feature))
                                                        <div class="flex items-start gap-3">
                                                            <div class="flex-shrink-0 mt-0.5">
                                                                <x-heroicon-s-check-circle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                                            </div>
                                                            <span class="text-sm text-gray-700 dark:text-white">
                                                                {{ trim($feature) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>

                                        {{-- Action Button --}}
                                        @if ($selectedPlan && $selectedPlan->id == $plan->id)
                                            <button type="button" disabled
                                                class="w-full py-3.5 px-6 rounded-xl font-bold text-blue-600 bg-blue-600 border-2 border-blue-600 cursor-not-allowed opacity-75">
                                                {{ __('organizer.signup.selected') }}
                                            </button>
                                        @else
                                            <button type="button" wire:click="selectPlan('{{ $plan->id }}')"
                                                class="w-full py-3.5 px-6 rounded-xl font-bold transition-all duration-200 cursor-pointer
                                                                                                                                text-blue-600 bg-white border-2 border-blue-600 hover:bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-400 dark:hover:bg-gray-700">
                                                {{ __('organizer.signup.choose_this') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Continue Button --}}
                        @if ($selectedPlan)
                            <div class="text-center">
                                <button type="button" wire:click="$set('currentStage', 2)"
                                    class="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 hover:bg-blue-700 text-blue-600 font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                                    {{ __('organizer.signup.continue') }}
                                    <x-heroicon-m-arrow-right class="w-5 h-5" />
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            {{-- STAGE 2: REGISTRATION FORM --}}
            @if ($currentStage == 2)
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    {{-- Registration Form --}}
                    <div class="xl:col-span-2">
                        <x-filament::section>
                            <x-slot name="heading">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-blue-600">
                                    {{ __('organizer.signup.register_info') }}
                                </h2>
                            </x-slot>

                            <form wire:submit="submitRegistration" class="space-y-6">
                                {{-- Organizer Information --}}
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-blue-600 mb-4 border-b pb-2">
                                        {{ __('organizer.signup.org_info') }}
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <x-filament::input.wrapper>
                                                <x-filament::input type="text" class="outline-none w-full dark:text-white"
                                                    wire:model="organizerName"
                                                    placeholder="{{ __('organizer.signup.org_name') }} *" />
                                            </x-filament::input.wrapper>
                                            @error('organizerName')
                                                <span class="text-red-600 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- User Information --}}
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-blue-600 mb-4 border-b pb-2">
                                        {{ __('organizer.signup.admin_info') }}
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <x-filament::input.wrapper>
                                                <x-filament::input type="text" class="outline-none w-full dark:text-white"
                                                    wire:model.live="userName"
                                                    placeholder="{{ __('organizer.signup.full_name') }} *" />
                                            </x-filament::input.wrapper>
                                            @error('userName')
                                                <span class="text-red-600 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <x-filament::input.wrapper>
                                                <x-filament::input type="email" class="outline-none w-full dark:text-white"
                                                    wire:model.live="userEmail" wire:model.debounce.1000ms="userEmail"
                                                    placeholder="{{ __('organizer.signup.email') }} *" />
                                            </x-filament::input.wrapper>
                                            @error('userEmail')
                                                <span class="text-red-600 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <x-filament::input.wrapper>
                                                <x-filament::input type="text" class="outline-none w-full dark:text-white"
                                                    wire:model.live="userPhone"
                                                    placeholder="{{ __('organizer.signup.phone') }} *" />
                                            </x-filament::input.wrapper>
                                            @error('userPhone')
                                                <span class="text-red-600 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <x-filament::input.wrapper>
                                                <x-filament::input type="password"
                                                    class="outline-none w-full dark:text-white" wire:model.live="password"
                                                    placeholder="{{ __('organizer.signup.password') }} *" />
                                            </x-filament::input.wrapper>
                                            @error('password')
                                                <span class="text-red-600 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <x-filament::input.wrapper>
                                                <x-filament::input type="password"
                                                    class="outline-none w-full dark:text-white"
                                                    wire:model.live="password_confirmation"
                                                    placeholder="{{ __('organizer.signup.confirm_password') }} *" />
                                            </x-filament::input.wrapper>
                                            @error('password_confirmation')
                                                <span class="text-red-600 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-3 pt-4 justify-center">
                                    {{-- Nút Trở lại --}}
                                    <x-filament::button type="button" color="gray" icon="heroicon-m-arrow-left"
                                        wire:click="backToPlans"
                                        class="flex-1 sm:max-w-22 max-w-18 sm:text-base text-sm cursor-pointer dark:text-white">
                                        {{ __('organizer.signup.back') }}
                                    </x-filament::button>

                                    {{-- Nút Submit (Thanh Toán) --}}
                                    <x-filament::button type="submit" wire:loading.attr="disabled"
                                        wire:target="submitRegistration" color="primary" icon="heroicon-m-arrow-right"
                                        class="flex-1 sm:max-w-22 max-w-18 sm:text-base text-sm cursor-pointer dark:text-white">
                                        <span wire:loading.remove wire:target="submitRegistration">
                                            {{ __('organizer.signup.pay_now') }}
                                        </span>
                                        <span wire:loading wire:target="submitRegistration">
                                            {{ __('organizer.signup.processing') }}
                                        </span>
                                    </x-filament::button>
                                </div>
                            </form>
                        </x-filament::section>
                    </div>

                    {{-- Selected Plan Summary --}}
                    <div class="xl:col-span-1">
                        @if ($selectedPlan)
                            <x-filament::section class="sticky top-4">
                                <x-slot name="heading">{{ __('organizer.signup.selected_plan') }}</x-slot>

                                <div class="space-y-4">
                                    @if (!empty($selectedPlan->badge))
                                        @php
                                            $badgeStyle = !empty($selectedPlan->badge_color)
                                                ? "background-color: {$selectedPlan->badge_color};"
                                                : 'background-color: #ccc';
                                        @endphp
                                        <x-filament::badge size="sm" style="{{ $badgeStyle }}">
                                            <p class="text-gray-900 dark:text-white">{{ $selectedPlan->badge }}</p>
                                        </x-filament::badge>
                                    @endif

                                    <h4 class="text-xl font-bold text-gray-900 dark:text-blue-600">
                                        {{ $selectedPlan->name }}
                                    </h4>

                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-bold text-gray-900 dark:text-blue-600">
                                            {{ number_format($selectedPlan->price, 0, ',', '.') }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ __('organizer.signup.vnd') }}</span>
                                    </div>

                                    <p class="text-sm text-gray-600 dark:text-white">
                                        {{ __('organizer.signup.duration') }}: {{ $selectedPlan->duration }}
                                        {{ __('organizer.signup.months') }}
                                    </p>

                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <p class="text-sm text-gray-700 dark:text-white">
                                            {{ $selectedPlan->description }}
                                        </p>
                                    </div>
                                </div>
                            </x-filament::section>
                        @endif
                    </div>
                </div>
            @endif

            {{-- STAGE 3: PAYMENT --}}
            @if ($currentStage == 3)
                <div class="max-w-7xl mx-auto">
                    <x-filament::section>
                        <x-slot name="heading">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-blue-600">
                                {{ __('organizer.signup.payment_title') }}
                            </h2>
                        </x-slot>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            {{-- Left: Current Plan + Change Plan Options --}}
                            <div class="space-y-6">
                                {{-- Current Selected Plan --}}
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-blue-600 mb-4">
                                        {{ __('organizer.signup.current_plan') }}
                                    </h3>

                                    @if ($selectedPlan)
                                        <div
                                            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-2 border-blue-500 dark:border-blue-400 rounded-xl p-6 shadow-lg">
                                            @if (!empty($selectedPlan->badge))
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold text-white bg-blue-600 mb-3">
                                                    {{ $selectedPlan->badge }}
                                                </span>
                                            @endif

                                            <h4 class="text-xl font-bold text-gray-900 dark:text-blue-600 mb-2">
                                                {{ $selectedPlan->name }}
                                            </h4>

                                            <div class="flex items-baseline gap-2 mb-3">
                                                <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                                    {{ number_format($selectedPlan->price, 0, ',', '.') }}
                                                </span>
                                                <span
                                                    class="text-sm text-gray-600 dark:text-white">{{ __('organizer.signup.vnd') }}</span>
                                            </div>

                                            <p class="text-sm text-gray-700 dark:text-white">
                                                {{ __('organizer.signup.duration') }}: <span
                                                    class="font-semibold">{{ $selectedPlan->duration }}
                                                    {{ __('organizer.signup.months') }}</span>
                                            </p>

                                            @if (!empty($selectedPlan->description))
                                                <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
                                                    <p class="text-sm text-gray-600 dark:text-white">
                                                        {{ $selectedPlan->description }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div
                                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                                            <p class="text-yellow-800 dark:text-yellow-200">
                                                {{ __('organizer.signup.no_plan_selected') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Change Plan Options --}}
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-blue-600 mb-4">
                                        {{ __('organizer.signup.change_plan') }}
                                    </h4>

                                    <div class="space-y-3">
                                        @foreach ($planList as $plan)
                                                                    <div
                                                                        class="bg-white dark:bg-gray-800 border rounded-xl p-4 transition-all duration-200
                                                                                                                                                                                                                {{ $selectedPlan && $selectedPlan->id == $plan->id
                                            ? 'border-blue-500 dark:border-blue-400 shadow-md ring-2 ring-blue-200 dark:ring-blue-800'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                                                        <div class="flex items-center justify-between gap-4">
                                                                            <div class="flex-grow">
                                                                                <div class="flex items-center gap-2 mb-1">
                                                                                    <h5 class="font-semibold text-gray-900 dark:text-blue-600">
                                                                                        {{ $plan->name }}
                                                                                    </h5>
                                                                                    @if (!empty($plan->badge))
                                                                                        <span
                                                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold text-white bg-blue-600">
                                                                                            {{ $plan->badge }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="flex items-baseline gap-1">
                                                                                    <span class="text-lg font-bold text-gray-900 dark:text-blue-600">
                                                                                        {{ number_format($plan->price, 0, ',', '.') }}
                                                                                    </span>
                                                                                    <span
                                                                                        class="text-xs text-gray-500 dark:text-white">{{ __('organizer.signup.vnd') }}</span>
                                                                                    <span class="text-xs text-gray-500 dark:text-white ml-1">
                                                                                        / {{ $plan->duration }} {{ __('organizer.signup.months') }}
                                                                                    </span>
                                                                                </div>
                                                                            </div>

                                                                            <div>
                                                                                @if ($selectedPlan && $selectedPlan->id == $plan->id)
                                                                                    <button type="button" disabled
                                                                                        class="px-4 py-2 rounded-lg font-medium text-sm text-white bg-blue-600 opacity-75 cursor-not-allowed">
                                                                                        {{ __('organizer.signup.selecting') }}
                                                                                    </button>
                                                                                @else
                                                                                    <button type="button"
                                                                                        wire:click="changePlanOnPayment('{{ $plan->id }}')"
                                                                                        class="px-4 py-2 rounded-lg font-medium text-sm cursor-pointer text-blue-600 bg-blue-50 hover:bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 transition-colors">
                                                                                        {{ __('organizer.signup.select_plan') }}
                                                                                    </button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Right: QR Code & Payment Status --}}
                            <div class="space-y-6">
                                {{-- QR Code --}}
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-blue-600 mb-4">
                                        {{ __('organizer.signup.scan_qr') }}
                                    </h3>

                                    <div x-data="{ loading: true }" class="relative w-full max-w-md mx-auto aspect-square">
                                        @if ($paymentStatus == \App\Utils\Constants\TransactionStatus::SUCCESS->value)
                                            <div
                                                class="absolute inset-0 flex flex-col items-center justify-center bg-green-50 dark:bg-green-900/20 rounded-xl border-2 border-green-200 dark:border-green-800">
                                                <div class="text-green-600 dark:text-green-400 mb-3">
                                                    <x-heroicon-s-check-circle class="w-16 h-16" />
                                                </div>
                                                <h3 class="text-xl font-semibold text-green-800 dark:text-green-200">
                                                    {{ __('organizer.signup.payment_success') }}
                                                </h3>
                                            </div>
                                        @elseif($paymentStatus == \App\Utils\Constants\TransactionStatus::WAITING->value)
                                            <template x-if="loading">
                                                <div
                                                    class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-xl">
                                                    <x-filament::loading-indicator class="w-8 h-8 text-primary-600" />
                                                </div>
                                            </template>

                                            @if (!empty($paymentData['urlBankQrcode']))
                                                <img src="{{ $paymentData['urlBankQrcode'] }}" alt="QR Code Thanh Toán"
                                                    class="w-full h-full object-contain rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
                                                    x-bind:class="{ 'opacity-0': loading, 'opacity-100': !loading }"
                                                    x-on:load="loading = false" x-on:error="loading = false"
                                                    style="transition: opacity 0.3s ease;" />
                                            @endif
                                        @else
                                            <div
                                                class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                                <p class="text-gray-500 dark:text-white">{{ __('organizer.signup.no_qr') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Payment Status & Actions --}}
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
                                    wire:poll.2s="refreshPaymentStatus">
                                    <div class="text-center mb-4">
                                        @if ($paymentStatus == \App\Utils\Constants\TransactionStatus::WAITING->value)
                                            <div class="flex flex-col items-center gap-3">
                                                <span
                                                    class="text-yellow-600 dark:text-yellow-400 font-semibold flex items-center gap-2">
                                                    <x-heroicon-s-clock class="w-5 h-5" />
                                                    {{ __('organizer.signup.waiting_payment') }}
                                                </span>
                                                @if ($expiryTime)
                                                    <p class="text-xs text-gray-500 dark:text-white">
                                                        {{ __('organizer.signup.expired_at') }}
                                                        {{ \Carbon\Carbon::createFromTimestamp($expiryTime)->timezone('Asia/Ho_Chi_Minh')->format('H:i:s d/m/Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        @elseif($paymentStatus == \App\Utils\Constants\TransactionStatus::SUCCESS->value)
                                            <span
                                                class="text-green-600 dark:text-green-400 font-semibold flex items-center justify-center gap-2">
                                                <x-heroicon-s-check-badge class="w-5 h-5" />
                                                {{ __('organizer.signup.payment_success') }}
                                            </span>
                                        @elseif($paymentStatus == \App\Utils\Constants\TransactionStatus::FAILED->value)
                                            <span
                                                class="text-orange-600 dark:text-orange-400 font-semibold flex items-center justify-center gap-2">
                                                <x-heroicon-s-x-circle class="w-5 h-5" />
                                                {{ __('organizer.signup.transaction_cancelled') }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 dark:text-white font-semibold">
                                                {{ __('organizer.signup.status_label') }}
                                                {{ $paymentStatus ?? 'Chưa khởi tạo' }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex flex-col gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button type="button" wire:click="refreshPaymentStatus"
                                            class="w-full px-4 py-2.5  cursor-pointer rounded-lg font-medium text-white bg-green-600 hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                                            <x-heroicon-s-arrow-path class="w-5 h-5" />
                                            {{ __('organizer.signup.check_status') }}
                                        </button>

                                        @if ($paymentStatus == \App\Utils\Constants\TransactionStatus::WAITING->value)
                                            <button type="button" wire:click="cancelTransaction"
                                                wire:confirm="{{ __('organizer.signup.confirm_cancel') }}"
                                                class="w-full cursor-pointer px-4 py-2.5 rounded-lg font-medium text-white bg-red-600 hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                                                <x-heroicon-s-x-circle class="w-5 h-5" />
                                                {{ __('organizer.signup.cancel_transaction') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-filament::section>
                </div>
            @endif

            @if ($currentStage == 4)
                <x-filament::section class="max-w-3xl mx-auto">
                    <div class="text-center py-12">
                        <div class="flex justify-center mb-6">
                            <div
                                class="w-24 h-24 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <x-heroicon-s-check-circle class="w-16 h-16 text-green-600 dark:text-green-400" />
                            </div>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 dark:text-blue-600 mb-4">
                            {{ __('organizer.signup.success_title') }}
                        </h2>

                        <p class="text-lg text-gray-600 dark:text-white mb-8">
                            {{ __('organizer.signup.success_desc') }}
                        </p>

                        @if ($selectedPlan)
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 mb-8">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-blue-600 mb-4">
                                    {{ __('organizer.signup.registered_plan_info') }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-white">
                                            {{ __('organizer.signup.selected_plan') }}
                                        </p>
                                        <p class="text-base font-semibold text-gray-900 dark:text-blue-600">
                                            {{ $selectedPlan->name }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-white">{{ __('organizer.signup.price') }}</p>
                                        <p class="text-base font-semibold text-gray-900 dark:text-blue-600">
                                            {{ number_format($selectedPlan->price, 0, ',', '.') }}
                                            {{ __('organizer.signup.vnd') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-white">{{ __('organizer.signup.duration') }}
                                        </p>
                                        <p class="text-base font-semibold text-gray-900 dark:text-blue-600">
                                            {{ $selectedPlan->duration }} {{ __('organizer.signup.months') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-white">{{ __('organizer.signup.status') }}</p>
                                        <p class="text-base font-semibold text-green-600 dark:text-green-400">
                                            {{ __('organizer.signup.activated') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <x-filament::button size="lg" class="cursor-pointer" color="primary"
                                wire:click="redirectToAdmin">
                                {{ __('organizer.signup.go_to_admin') }}
                            </x-filament::button>
                        </div>

                        <div
                            class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="flex items-start gap-3">
                                <x-heroicon-s-information-circle
                                    class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                        {{ __('organizer.signup.important_note') }}
                                    </p>
                                    <p class="text-sm text-blue-800 dark:text-blue-300">
                                        {{ __('organizer.signup.email_note') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @endif
        </div>
    </div>
</div>