<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <x-filament::section>
    @if($isSuperAdmin)

        <x-slot name="description">
            Là nơi chứa tất cả các cấu hình hệ thống, mỗi cấu hình ở dưới đây đều ảnh hưởng đến hệ thống nên sẽ phải
            chỉnh sửa cẩn thận !
        </x-slot>

        {{-- Content --}}
        @livewire('filament.config-form',['isSuperAdmin' => $isSuperAdmin, 'organizerId' => $organizerId])
    @else
        <x-slot name="description">
            Cài đặt tổ chức
        </x-slot>
        {{-- Content --}}
        @livewire('filament.config-form',['isSuperAdmin' => $isSuperAdmin, 'organizerId' => $organizerId])
    @endif
    </x-filament::section>

</x-filament-panels::page>
