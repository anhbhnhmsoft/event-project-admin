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
                        Chú thích: {{ $config->description }}
                    </p>
                </div>
                @if (!$loop->last)
                    <hr class="my-4 dark:border-gray-700">
                @endif
            @endforeach
            <x-filament::button type="submit" icon="heroicon-m-pencil" wire:loading.attr="disabled">
                Chỉnh sửa
                <div wire:loading>
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            </x-filament::button>
        </form>
    @else
        <form wire:submit="updateConfigAdmin" class="flex flex-col gap-4">

            {{-- Thông tin tổ chức --}}
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Thông tin tổ chức</h3>

                {{-- Tên tổ chức --}}
                <div class="flex flex-col items-start gap-2 mb-4">
                    <label for="organizer_name" class="block text-sm font-bold text-gray-900 dark:text-white">
                        Tên tổ chức <span class="text-red-500">*</span>
                    </label>
                    <x-filament::input.wrapper class="w-full">
                        <x-filament::input wire:model="organizer_value.{{ $this->organizer->name }}" id="organizer_name"
                            placeholder="Nhập tên tổ chức" />
                    </x-filament::input.wrapper>
                    @error('organizer_value.' . $this->organizer->name)
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ảnh đại diện --}}
                <div class="flex flex-col items-start gap-2 mb-4">
                    <label for="organizer_image" class="block text-sm font-bold text-gray-900 dark:text-white">
                        Ảnh đại diện
                    </label>
                    <x-filament::input.wrapper class="w-full">
                        <input type="file" wire:model="organizer_value.image" id="organizer_image"
                            class="fi-input fi-input-file w-full" accept="image/*" />
                    </x-filament::input.wrapper>

                    @php
                        $newImage = $organizer_value['image'] ?? null;
                    @endphp

                    <div class="flex gap-4">
                        @if (is_object($newImage) && method_exists($newImage, 'temporaryUrl'))
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Ảnh mới:</p>
                                {{-- Đã có dark mode styling --}}
                                <img src="{{ $newImage->temporaryUrl() }}" alt="Preview Image"
                                    class="h-24 w-auto object-cover rounded-lg border border-gray-200 dark:border-gray-700" />
                            </div>
                        @endif
                        @if ($this->organizer->image)
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Ảnh hiện tại:</p>
                                {{-- Đã có dark mode styling --}}
                                <img src="{{ \App\Utils\Helper::generateURLImagePath($this->organizer->image) }}"
                                    alt="Current Image"
                                    class="h-24 w-auto object-cover rounded-lg border border-gray-200 dark:border-gray-700" />
                            </div>
                        @endif
                    </div>

                    @error('organizer_value.image')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <p class="block text-sm italic text-gray-500 dark:text-gray-400">
                        Chấp nhận: JPG, PNG, GIF, WEBP, JPEG.
                    </p>
                </div>

                {{-- Mô tả --}}
                <div class="flex flex-col items-start gap-2">
                    <label for="organizer_description" class="block text-sm font-bold text-gray-900 dark:text-white">
                        Mô tả
                    </label>
                    <x-filament::input.wrapper class="w-full">
                        <textarea wire:model="organizer_value.description" id="organizer_description" rows="4"
                            class="fi-input block w-full rounded-lg border-0 py-1.5 px-3 text-base bg-white dark:bg-white/5 text-gray-950 dark:text-white shadow-sm ring-1 ring-inset ring-gray-950/10 dark:ring-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:focus:ring-primary-500 disabled:bg-gray-50 dark:disabled:bg-white/5 disabled:text-gray-500 dark:disabled:text-gray-400 disabled:cursor-not-allowed sm:text-sm sm:leading-6"
                            placeholder="Nhập mô tả về tổ chức..."></textarea>
                    </x-filament::input.wrapper>
                    @error('organizer_value.description')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            @if($this->configList->isNotEmpty())
            <hr class="my-4 dark:border-gray-700">
            {{-- Cấu hình hệ thống --}}
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Cấu hình hệ thống</h3>

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
                            Chú thích: {{ $config->description }}
                        </p>
                    </div>
                    @if (!$loop->last)
                        <hr class="my-4 dark:border-gray-700">
                    @endif
                @endforeach
            </div>
            @endif

            <x-filament::button type="submit" icon="heroicon-m-pencil" wire:loading.attr="disabled">
                Chỉnh sửa
                <div wire:loading>
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            </x-filament::button>
        </form>
    @endif
</filament::page>
