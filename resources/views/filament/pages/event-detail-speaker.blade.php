<x-filament-panels::page>
    @php
    $event = $this->record;
    $imageUrl = $event->image_represent_path ? \Illuminate\Support\Facades\Storage::url($event->image_represent_path) : null;
    @endphp

    <div class="space-y-6" wire:poll.60s="refreshData">
        <div class="relative overflow-hidden rounded-xl h-[800px] shadow-sm p-6 {{ $imageUrl ? 'text-white' : 'bg-white dark:bg-gray-800' }}">
            @if($imageUrl)
            <div class="absolute inset-0 z-0">
                <img src="{{ $imageUrl }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-transparent"></div>
            </div>
            @endif

            <div class="relative z-10 flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold {{ $imageUrl ? 'text-white' : 'text-gray-900 dark:text-white' }} mb-3">
                        {{ $event->name }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-4 text-sm {{ $imageUrl ? 'text-gray-300' : 'text-gray-600 dark:text-gray-300' }} mb-4">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4" />
                            <span>{{ \Carbon\Carbon::parse($event->day_represent)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4" />
                            <span>{{ $event->start_time }} - {{ $event->end_time }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-map-pin" class="w-4 h-4" />
                            <span>{{ $event->ward?->name }}, {{ $event->district?->name }},
                                {{ $event->province?->name }}</span>
                        </div>
                    </div>

                    {{-- Progress bar sự kiện --}}
                    @if ($isToday)
                    <div class="mt-4">
                        <div class="flex justify-between text-xs {{ $imageUrl ? 'text-gray-300' : 'text-gray-600 dark:text-gray-400' }} mb-2">
                            <span>{{ __('admin.events.detail.event_progress') }}</span>
                            <span class="font-medium">{{ number_format($eventProgress, 1) }}%</span>
                        </div>
                        <div class="w-full {{ $imageUrl ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-700' }} rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full transition-all duration-500"
                                @style(["width: {{ $eventProgress }}%"])></div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Đồng hồ --}}
                <div class="text-right ml-8 flex-shrink-0">
                    <div class="text-xs {{ $imageUrl ? 'text-gray-300' : 'text-gray-500 dark:text-gray-400' }} mb-1">{{ __('admin.events.detail.current_time') }}</div>
                    <div class="text-4xl font-bold {{ $imageUrl ? 'text-white' : 'text-gray-900 dark:text-white' }} font-mono" id="current-time">
                        {{ now()->format('H:i') }}
                    </div>
                    <div class="text-sm {{ $imageUrl ? 'text-gray-300' : 'text-gray-500 dark:text-gray-400' }} mt-1" id="current-date">
                        {{ now()->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid layout: 2 cột --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Cột trái: Lịch trình (2/3) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Lịch trình hiện tại --}}
                @if ($isToday && $currentScheduleIndex !== null)
                @php $current = $schedules[$currentScheduleIndex]; @endphp
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 shadow-lg text-white">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div
                                class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-semibold mb-3">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                                </span>
                                {{ __('admin.events.detail.ongoing') }}
                            </div>
                            <h2 class="text-2xl font-bold mb-2">{{ $current['title'] }}</h2>
                            <div class="flex items-center gap-3 text-sm text-white/90">
                                <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4" />
                                <span class="font-semibold">{{ $current['start_time'] }} -
                                    {{ $current['end_time'] }}</span>
                                @if ($timeRemaining)
                                <span class="font-medium">
                                    • {{ __('admin.events.detail.time_remaining', ['time' => $this->formatTimeRemaining($timeRemaining)]) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="mb-4">
                        <div class="w-full bg-white/30 rounded-full h-2">
                            <div class="bg-white h-2 rounded-full transition-all duration-500"
                                @style(['width: {{ $this->getScheduleProgress($currentScheduleIndex) }}%'])"></div>
                        </div>
                    </div>

                    @if ($current['description'])
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                        <div class="prose prose-invert max-w-none text-white/90">
                            {!! $current['description'] !!}
                        </div>
                    </div>
                    @endif

                    {{-- Tài liệu --}}
                    @if (isset($current['documents']) && count($current['documents']) > 0)
                    <div class="mt-4 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5" />
                            {{ __('admin.events.detail.documents_count', ['count' => count($current['documents'])]) }}
                        </h3>
                        <div class="space-y-2">
                            @foreach ($current['documents'] as $doc)
                            <div class="p-3 bg-white/10 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <x-filament::icon icon="heroicon-o-document" class="w-5 h-5" />
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $doc['title'] }}</div>
                                        @if ($doc['price'] > 0)
                                        <div class="text-sm">{{ number_format($doc['price']) }} VNĐ</div>
                                        @else
                                        <div class="text-sm">{{ __('admin.events.detail.free') }}</div>
                                        @endif
                                    </div>
                                </div>

                        {{-- Tài liệu --}}
                        @if (isset($current['documents']) && count($current['documents']) > 0)
                            <div class="mt-4 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                <h3 class="font-semibold mb-3 flex items-center gap-2">
                                    <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5" />
                                    {{ __('admin.events.detail.documents_count', ['count' => count($current['documents'])]) }}
                                </h3>
                                <div class="space-y-2">
                                    @foreach ($current['documents'] as $doc)
                                        <div class="flex items-center gap-3 p-3 bg-white/10 rounded-lg">
                                            <x-filament::icon icon="heroicon-o-document" class="w-5 h-5" />
                                            <div class="flex-1">
                                                <div class="font-medium">{{ $doc['title'] }}</div>
                                                @if ($doc['price'] > 0)
                                                    <div class="text-sm">{{ number_format($doc['price']) }} {{ __('admin.events.detail.vnd') }}</div>
                                                @else
                                                    <div class="text-sm">{{ __('admin.events.detail.free') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Lịch trình tiếp theo --}}
                @if ($isToday && $nextScheduleIndex !== null && $currentScheduleIndex !== null)
                @php $next = $schedules[$nextScheduleIndex]; @endphp
                <div
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div
                                class="inline-flex items-center gap-2 bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-semibold mb-3">
                                <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4" />
                                {{ __('admin.events.detail.next') }}
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $next['title'] }}
                            </h2>
                            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4" />
                                <span class="font-semibold">{{ $next['start_time'] }} -
                                    {{ $next['end_time'] }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($next['description'])
                    <div class="prose max-w-none text-gray-700 dark:text-gray-300">
                        {!! \Illuminate\Support\Str::limit(strip_tags($next['description']), 200) !!}
                    </div>
                    @endif
                </div>
                @endif

                {{-- Danh sách toàn bộ lịch trình --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-queue-list" class="w-6 h-6" />
                        {{ __('admin.events.detail.full_schedule') }}
                    </h2>
                    <div class="space-y-3">
                        @foreach ($schedules as $index => $schedule)
                        <div
                            class="flex items-start gap-4 p-4 rounded-lg border transition
                                @if ($index === $currentScheduleIndex) bg-green-50 dark:bg-green-900/20 border-green-500 dark:border-green-700
                                @elseif($index === $nextScheduleIndex && $currentScheduleIndex !== null)
                                    bg-blue-50 dark:bg-blue-900/20 border-blue-400 dark:border-blue-700
                                @else
                                    bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700 @endif">

                            {{-- Thời gian --}}
                            <div class="flex-shrink-0 w-24 text-center">
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $schedule['start_time'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.events.detail.to') }}</div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $schedule['end_time'] }}
                                </div>
                            </div>
                            @php
                            $refIndex = $currentScheduleIndex ?? $nextScheduleIndex;
                            $refIndex = is_null($refIndex) ? -1 : $refIndex;
                            @endphp
                            {{-- Nội dung --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $schedule['title'] }}
                                    </h3>

                                    {{-- Badge trạng thái --}}
                                    @if ($index === $currentScheduleIndex)
                                    <span
                                        class="flex-shrink-0 inline-flex items-center gap-1 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                        {{ __('admin.events.detail.ongoing') }}
                                    </span>
                                    @elseif($index === $nextScheduleIndex && $currentScheduleIndex !== null)
                                    <span
                                        class="flex-shrink-0 bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        {{ __('admin.events.detail.next') }}
                                    </span>
                                    @elseif($index < $refIndex)
                                        <span
                                        class="flex-shrink-0 bg-gray-400 dark:bg-gray-600 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        {{ __('admin.events.detail.passed') }}
                                        </span>
                                        @else
                                        <span
                                            class="flex-shrink-0 bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded-full text-xs font-medium">
                                            {{ __('admin.events.detail.upcoming') }}
                                        </span>
                                        @endif
                                </div>

                                {{-- Thông tin bổ sung --}}
                                @if (isset($schedule['documents']) && count($schedule['documents']) > 0)
                                <div class="mt-2 space-y-2">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <x-filament::icon icon="heroicon-o-document-text" class="w-3 h-3" />
                                        {{ __('admin.events.detail.documents_count', ['count' => count($schedule['documents'])]) }}
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($schedule['documents'] as $doc)
                                        @if(isset($doc['files']) && count($doc['files']) > 0)
                                        @foreach($doc['files'] as $file)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($file['file_path']) }}" target="_blank" class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition group/file">
                                            @php
                                            $isImage = in_array(strtolower($file['file_extension']), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            @endphp
                                            <div class="flex-shrink-0 text-gray-400 dark:text-gray-500 group-hover/file:text-primary-500">
                                                @if($isImage)
                                                <x-filament::icon icon="heroicon-o-photo" class="w-4 h-4" />
                                                @else
                                                <x-filament::icon icon="heroicon-o-paper-clip" class="w-4 h-4" />
                                                @endif
                                            </div>
                                            <div class="truncate text-xs font-medium text-gray-700 dark:text-gray-300 group-hover/file:text-primary-600 dark:group-hover/file:text-primary-400">
                                                {{ $file['file_name'] }}
                                            </div>
                                        </a>
                                        @endforeach
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Cột phải: Thông tin (1/3) --}}
            <div class="space-y-6">
                {{-- Người tham gia --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5" />
                        {{ __('admin.events.detail.participants_count', ['count' => count($participants)]) }}
                    </h2>

                    @if (count($participants) > 0)
                    <div class="space-y-3">
                        @foreach ($participants as $participant)
                        <div class="flex items-center gap-3">
                            @if ($participant['avatar'])
                            <img src="{{ Storage::url($participant['avatar']) }}"
                                alt="{{ $participant['name'] }}"
                                class="w-10 h-10 rounded-full object-cover">
                            @else
                            <div
                                class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr($participant['name'], 0, 1)) }}
                            </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 dark:text-white truncate text-sm">
                                    {{ $participant['name'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $participant['role_label'] }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        {{ __('admin.events.detail.no_participants') }}
                    </p>
                    @endif
                </div>

                {{-- Thông tin sự kiện --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5" />
                        {{ __('admin.events.detail.event_info') }}
                    </h2>
                    <div class="space-y-4 text-sm">
                        <div>
                            <div class="text-gray-500 dark:text-gray-400 mb-1 text-xs">{{ __('admin.events.detail.organizer') }}</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $event->organizer?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500 dark:text-gray-400 mb-1 text-xs">{{ __('admin.events.detail.status') }}</div>
                            <div>
                                @php
                                $statusColors = [
                                'upcoming' =>
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'ongoing' =>
                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'finished' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                ];
                                $statusLabels = \App\Utils\Constants\EventStatus::getOptions();
                                @endphp
                                <span
                                    class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$event->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statusLabels[$event->status] ?? $event->status }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500 dark:text-gray-400 mb-1 text-xs">{{ __('admin.events.detail.ticket_type') }}</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                @if ($event->free_to_join)
                                <span class="text-green-600 dark:text-green-400">{{ __('admin.events.detail.free') }}</span>
                                @else
                                <span class="text-primary-600 dark:text-primary-400">{{ __('admin.events.detail.paid') }}</span>
                                @endif
                            </div>
                        </div>

                        @if ($event->short_description)
                        <div>
                            <div class="text-gray-500 dark:text-gray-400 mb-1 text-xs">{{ __('admin.events.detail.description') }}</div>
                            <div class="text-gray-700 dark:text-gray-300 text-sm">
                                {{ \Illuminate\Support\Str::limit($event->short_description, 150) }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript cập nhật thời gian --}}
    @push('scripts')
    <script>
        function updateTime() {
            const now = new Date();
            const timeElement = document.getElementById('current-time');
            const dateElement = document.getElementById('current-date');

            if (timeElement) {
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                timeElement.textContent = `${hours}:${minutes}`;
            }

            if (dateElement) {
                const day = String(now.getDate()).padStart(2, '0');
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const year = now.getFullYear();
                dateElement.textContent = `${day}/${month}/${year}`;
            }
        }

        setInterval(updateTime, 1000);
        updateTime();
    </script>
    @endpush
</x-filament-panels::page>