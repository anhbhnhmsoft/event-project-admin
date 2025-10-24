<filament::page>
    @if ($this->isSuperAdmin)
        <form wire:submit="updateConfig" class="flex flex-col gap-4">
            @foreach ($this->configList as $index => $config)
                <div class="flex flex-col items-start gap-2">
                    <label for="config_{{ $config->config_key }}"
                        class="block text-sm font-bold text-gray-900 dark:text-white">{{ $config->config_key }}</label>
                    @if ($config->config_key === 'LOGO')
                        <x-filament::input.wrapper class="w-full">
                            <input type="file" wire:model="config_value.{{ $config->config_key }}"
                                class="fi-input fi-input-file w-full" accept="image/*" />
                        </x-filament::input.wrapper>
                        @php
                            $newLogo = $config_value['LOGO'] ?? null;
                        @endphp
                        {{-- Cập nhật: Thêm border cho ảnh preview và ảnh hiện tại trong Dark Mode --}}
                        @if (is_object($newLogo) && method_exists($newLogo, 'temporaryUrl'))
                            <img src="{{ $newLogo->temporaryUrl() }}" alt="Preview Logo"
                                class="h-12 mt-2 rounded border border-gray-200 dark:border-gray-700" />
                        @elseif(isset($config->config_value) && $config->config_value)
                            <img src="{{ \App\Utils\Helper::generateURLImagePath($config->config_value) }}"
                                alt="Current Logo"
                                class="h-12 mt-2 rounded border border-gray-200 dark:border-gray-700" />
                        @endif
                    @else
                        <x-filament::input.wrapper class="w-full">
                            <x-filament::input wire:model="config_value.{{ $config->config_key }}" />
                        </x-filament::input.wrapper>
                    @endif

                    <p class="block text-sm italic text-gray-500 dark:text-gray-400">
                        @switch($config->config_key)
                            @case('CLIENT_ID_APP')
                                {{__('admin.config.CLIENT_ID_APP')}}
                                @break
                            @case('API_KEY')
                                {{__('admin.config.API_KEY')}}
                                @break
                            @case('CHECKSUM_KEY')
                                {{__('admin.config.CHECKSUM_KEY')}}
                                @break
                            @case('LINK_ZALO_SUPPORT')
                                {{__('admin.config.LINK_ZALO_SUPPORT')}}
                                @break
                            @case('LINK_FACEBOOK_SUPPORT')
                                {{__('admin.config.LINK_FACEBOOK_SUPPORT')}}
                                @break
                            @default
                                {{ $config->description }}
                        @endswitch
                    </p>
                </div>
                @if (!$loop->last)
                    <hr class="my-4 dark:border-gray-700">
                @endif
            @endforeach
            <x-filament::button type="submit" icon="heroicon-m-pencil" wire:loading.attr="disabled">
                {{ __('common.common_success.save') }}
                <div wire:loading>
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            </x-filament::button>
        </form>
    @else
        <form wire:submit="updateConfigAdmin" class="flex flex-col gap-4">

            @if($this->configList->isNotEmpty())
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('admin.config.system_config') }}</h3>

                @foreach ($this->configList as $index => $config)
                    <div class="flex flex-col items-start gap-2">
                        <label for="config_{{ $config->config_key }}"
                            class="block text-sm font-bold text-gray-900 dark:text-white">{{ $config->config_key }}</label>

                        @if ($config->config_key === 'LOGO')
                            <x-filament::input.wrapper class="w-full">
                                <input type="file" wire:model="config_value.{{ $config->config_key }}"
                                    class="fi-input fi-input-file w-full" accept="image/*" />
                            </x-filament::input.wrapper>
                            @php
                                $newLogo = $config_value['LOGO'] ?? null;
                            @endphp
                            @if (is_object($newLogo) && method_exists($newLogo, 'temporaryUrl'))
                                <img src="{{ $newLogo->temporaryUrl() }}" alt="Preview Logo"
                                    class="h-12 mt-2 rounded border border-gray-200 dark:border-gray-700" />
                            @elseif(isset($config->config_value) && $config->config_value)
                                <img src="{{ \App\Utils\Helper::generateURLImagePath($config->config_value) }}"
                                    alt="Current Logo"
                                    class="h-12 mt-2 rounded border border-gray-200 dark:border-gray-700" />
                            @endif
                        @else
                            <x-filament::input.wrapper class="w-full">
                                <x-filament::input wire:model="config_value.{{ $config->config_key }}" />
                            </x-filament::input.wrapper>
                        @endif

                        <p class="block text-sm italic text-gray-500 dark:text-gray-400">
                            @switch($config->config_key)
                                @case('CLIENT_ID_APP')
                                    {{__('admin.config.CLIENT_ID_APP')}}
                                    @break
                                @case('API_KEY')
                                    {{__('admin.config.API_KEY')}}
                                    @break
                                @case('CHECKSUM_KEY')
                                    {{__('admin.config.CHECKSUM_KEY')}}
                                    @break
                                @case('LINK_ZALO_SUPPORT')
                                    {{__('admin.config.LINK_ZALO_SUPPORT')}}
                                    @break
                                @case('LINK_FACEBOOK_SUPPORT')
                                    {{__('admin.config.LINK_FACEBOOK_SUPPORT')}}
                                    @break
                                @default
                                    {{ $config->description }}
                            @endswitch
                        </p>
                    </div>
                @endforeach
            </div>
            @endif

            <x-filament::button type="submit" icon="heroicon-m-pencil" wire:loading.attr="disabled">
                {{ __('common.common_success.save') }}
                <div wire:loading>
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            </x-filament::button>
        </form>
    @endif
</filament::page>
