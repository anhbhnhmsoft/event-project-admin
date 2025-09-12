<form wire:submit="updateConfig" class="flex flex-col gap-4">
    @foreach($this->configList as $index => $config)
        <div class="flex flex-col items-start gap-2">
            <label for="config_{{ $config->config_key }}"
                   class="block text-sm font-bold text-gray-700">{{ $config->config_key }}</label>

            @if($config->config_key === 'LOGO')
                <x-filament::input.wrapper class="w-full">
                    <input type="file"
                           wire:model="config_value.{{$config->config_key}}"
                           class="fi-input fi-input-file w-full" accept="image/*" />
                </x-filament::input.wrapper>
                @php
                    $newLogo = $config_value['LOGO'] ?? null;
                @endphp
                @if(is_object($newLogo) && method_exists($newLogo, 'temporaryUrl'))
                    <img src="{{ $newLogo->temporaryUrl() }}" alt="Preview Logo" class="h-12 mt-2 rounded" />
                @elseif(isset($config->config_value) && $config->config_value)
                    <img src="{{ \App\Utils\Helper::generateURLImagePath($config->config_value) }}" alt="Current Logo" class="h-12 mt-2 rounded" />
                @endif
            @else
                <x-filament::input.wrapper class="w-full">
                    <x-filament::input
                        wire:model="config_value.{{$config->config_key}}"
                    />
                </x-filament::input.wrapper>
            @endif

            <p class="block text-sm italic text-gray-500">
                Chú thích: {{$config->description}}
            </p>
        </div>
        @if(!$loop->last)
            <hr class="my-4">
        @endif
    @endforeach
    <x-filament::button
        type="submit"
        icon="heroicon-m-pencil"
        wire:loading.attr="disabled"
    >
        Chỉnh sửa
        <div wire:loading>
            <x-filament::loading-indicator class="h-5 w-5" />
        </div>
    </x-filament::button>
</form>

