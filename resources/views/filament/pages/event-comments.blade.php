<x-filament::page>
    <h2 class="">
        {{ __('admin.events.comments.title') }}: <span class="font-semibold text-orange-600">{{ $this->record->name }}</span>
    </h2>

    {{ $this->table }}
</x-filament::page>
