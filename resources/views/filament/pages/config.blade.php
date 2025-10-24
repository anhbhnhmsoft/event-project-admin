<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <x-filament::section>
    @if($isSuperAdmin)

        <x-slot name="description">
            {{ __('admin.config.super_admin_description') }}
        </x-slot>

        {{-- Content --}}
        @livewire('filament.config-form',['isSuperAdmin' => $isSuperAdmin, 'organizerId' => $organizerId])
    @else
        <x-slot name="description">
            {{ __('admin.config.organizer_description') }}
        </x-slot>
        {{-- Content --}}
        @livewire('filament.config-form',['isSuperAdmin' => $isSuperAdmin, 'organizerId' => $organizerId])
    @endif
    </x-filament::section>

</x-filament-panels::page>
