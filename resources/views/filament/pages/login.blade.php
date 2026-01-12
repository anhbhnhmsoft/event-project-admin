<x-filament-panels::page.simple>
    <div class="flex gap-2 justify-center">
        {{-- 🇻🇳 Vietnamese --}}
        <button wire:click="switchLanguage('vi')" type="button" @class([
            'flex items-center  text-gray-700 gap-2 px-3 py-2 rounded-lg transition-all duration-200 border',
            app()->getLocale() === 'vi'
                ? 'bg-primary-600 shadow-lg scale-105 border-blue-500'
                : 'bg-primary  border-gray-200 hover:bg-gray-50 hover:border-primary-300',
        ])
        title="{{ __('Tiếng Việt') }}">
            <img src="{{ asset('images/Vietnam.svg.png') }}" alt="Vietnam flag" class="w-6 h-4 rounded-sm object-cover" />
            <span class="font-medium text-sm">VI</span>
        </button>

        {{-- 🇬🇧 English --}}
        <button wire:click="switchLanguage('en')" type="button" @class([
            'flex items-center gap-2 text-gray-700  px-3 py-2 rounded-lg transition-all duration-200 border',
            app()->getLocale() === 'en'
                ? 'bg-primary-600 shadow-lg scale-105 border-blue-500'
                : 'bg-primary border-gray-200 hover:bg-gray-50 hover:border-primary-300',
        ]) title="{{ __('English') }}">
            <img src="{{ asset('images/Flag_of_the_United_Kingdom.svg.png') }}" alt="UK flag"
                 class="w-6 h-4 rounded-sm object-cover" />
            <span class="font-medium text-sm">EN</span>
        </button>
    </div>

    @if (filament()->hasLogin())
        <x-slot name="heading">
            {{ __('auth.login.heading') }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(
        \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
        scopes: $this->getRenderHookScopes(),
    ) }}

    <form wire:submit.prevent="authenticate" class="space-y-6">
        {{ $this->form }}
        <x-filament::actions :actions="$this->getFormActions()" alignment="right" class="flex justify-center" />
    </form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(
        \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
        scopes: $this->getRenderHookScopes(),
    ) }}
</x-filament-panels::page.simple>
