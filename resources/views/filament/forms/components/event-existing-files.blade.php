<x-filament::section>

    <x-slot name="heading">
        Danh sách file tài liệu
    </x-slot>


    @php
        $scheduleState = $documentData;
        $files = collect();
        $scheduleItems = is_array($scheduleState) ? array_values($scheduleState) : [];

        foreach ($scheduleItems as $item) {
            if (is_array($item) && isset($item['files']) && is_array($item['files'])) {
                $filePaths = array_values($item['files']);

                foreach ($filePaths as $filePath) {
                    if (is_array($filePath)) {
                        $files->push([
                            'file_name' => $filePath['file_name'] ?? basename($filePath['file_path'] ?? ''),
                            'file' => $filePath['file_path'] ?? '',
                        ]);
                    } elseif (is_string($filePath)) {
                        $files->push([
                            'file_name' => basename($filePath),
                            'file' => $filePath,
                        ]);
                    }
                }
            }
        }
    @endphp

    @if ($files->isEmpty())
        <p class="text-gray-500">Chưa có tài liệu nào.</p>
    @else
        <div class="space-y-3">
            @foreach ($files as $item)
                <div x-data="{ deleting: false, deleted: false }"
                    x-on:file-marked-for-deletion.window="
        if ($event.detail.path === '{{ $item['file'] }}') {
            deleting = true;
            setTimeout(() => deleted = true, 500); 
        }
    "
                    x-show="!deleted" x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 max-h-40" x-transition:leave-end="opacity-0 max-h-0"
                    class="p-3 rounded bg-gray-50 flex justify-between items-center overflow-hidden"
                    :class="{ 'opacity-50': deleting }">
                    <div>
                        <p class="font-medium">{{ $item['file_name'] }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('file_document', $item['file']) }}" target="_blank"
                            class="text-blue-600 underline">
                            Xem / Tải
                        </a>
                        <button type="button" wire:click="markFileForDeletion('{{ $item['file'] }}')"
                            class="text-red-600 underline">
                            Xóa
                        </button>
                    </div>
                </div>
            @endforeach

        </div>
    @endif
</x-filament::section>
