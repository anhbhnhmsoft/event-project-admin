<x-filament::section>
    <x-slot name="heading">
        Danh sách file tài liệu
    </x-slot>

    @php
        $hasDocuments = !empty($documentData) && count($documentData) > 0;
    @endphp

    @if (!$hasDocuments)
        <p class="text-gray-500">Chưa có tài liệu nào.</p>
    @else
        <div class="space-y-4">
            @foreach ($documentData as $documentIndex => $document)
                @php
                    $files = collect();

                    if (isset($document['files']) && is_array($document['files'])) {
                        foreach ($document['files'] as $file) {
                            $files->push([
                                'file_name' => $file['file_name'] ?? basename($file['file_path'] ?? ''),
                                'file_path' => $file['file_path'] ?? '',
                                'file_id' => $file['id'] ?? null,
                            ]);
                        }
                    }

                    $documentTitle = $document['title'] ?? ('Tài liệu #' . ((int) $documentIndex + 1));
                    $documentPrice = $document['price'] ?? 0;
                    $documentId = $document['id'] ?? $documentIndex;
                @endphp

                @if ($files->isNotEmpty())
                    <div x-data="{ expanded: true }" class="border border-gray-200 rounded-lg overflow-hidden">
                        <!-- Document Header - Clickable -->
                        <button type="button" @click="expanded = !expanded"
                            class="w-full bg-gray-100 px-4 py-3 border-b border-gray-200 hover:bg-gray-150 transition-colors">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3 text-left">
                                    <!-- Expand Icon -->
                                    <svg x-show="!expanded" class="w-5 h-5 text-gray-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    <svg x-show="expanded" class="w-5 h-5 text-gray-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>

                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $documentTitle }}</h3>
                                        @if ($documentPrice > 0)
                                            <p class="text-sm text-gray-600 mt-0.5">
                                                Giá: {{ number_format($documentPrice, 0, ',', '.') }} VNĐ
                                            </p>
                                        @else
                                            <p class="text-sm text-green-600 mt-0.5">Miễn phí</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">{{ $files->count() }} file</span>
                            </div>
                        </button>

                        <!-- Files List - Collapsible -->
                        <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 max-h-0"
                            x-transition:enter-end="opacity-100 max-h-screen"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 max-h-screen"
                            x-transition:leave-end="opacity-0 max-h-0" class="divide-y divide-gray-200">
                            @foreach ($files as $item)
                                <div x-data="{ deleting: false, deleted: false }"
                                    x-on:file-marked-for-deletion.window="
                                        if ($event.detail.path === '{{ $item['file_path'] }}') {
                                            deleting = true;
                                            setTimeout(() => deleted = true, 500);
                                        }
                                    "
                                    x-show="!deleted" x-transition:leave="transition ease-in duration-500"
                                    x-transition:leave-start="opacity-100 max-h-40"
                                    x-transition:leave-end="opacity-0 max-h-0"
                                    class="px-4 py-3 bg-white hover:bg-gray-50 flex justify-between items-center overflow-hidden transition-colors"
                                    :class="{ 'opacity-50': deleting }">

                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $item['file_name'] }}</p>
                                        <p class="text-sm text-gray-500 truncate mt-0.5">
                                            {{ basename($item['file_path']) }}</p>
                                    </div>

                                    <div class="flex gap-3 ml-4 flex-shrink-0">
                                        <a href="{{ route('file_document', $item['file_path']) }}" target="_blank"
                                            class="text-blue-600 hover:text-blue-800 underline text-sm">
                                            Xem / Tải
                                        </a>
                                        <button type="button"
                                            wire:click="markFileForDeletion('{{ $item['file_path'] }}')"
                                            class="text-red-600 hover:text-red-800 underline text-sm">
                                            Xóa
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</x-filament::section>
