@extends('components.layouts.view')

@section('content')
<div class="relative w-screen h-screen overflow-hidden font-['Inter']">

    <div class="swiper eventSwiper w-full h-full">
        <div class="swiper-wrapper">
            <!-- Slide 1: Welcome / Main Info -->
            <div class="swiper-slide relative pt-20 flex items-center justify-center overflow-hidden">
                <!-- Background with Gradient & Pattern -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#334155] z-0"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] z-0"></div>
                <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-purple-500/20 rounded-full blur-3xl animate-pulse delay-1000"></div>

                <div class="relative z-10 max-w-6xl w-full mx-auto px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Left: Text Content -->
                    <div class="text-white space-y-8 text-center lg:text-left">
                        @if($event->image_represent_path)
                        <div class="lg:hidden mb-8 flex justify-center">
                            <div class="relative w-48 h-48 rounded-full p-1 bg-gradient-to-tr from-blue-400 to-purple-500 shadow-2xl">
                                <img src="{{ asset('storage/' . $event->image_represent_path) }}" class="w-full h-full object-cover rounded-full border-4 border-[#0f172a]" />
                            </div>
                        </div>
                        @endif

                        <div>
                            <h1 class="text-5xl lg:text-7xl font-black leading-tight tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-blue-100 to-blue-200 drop-shadow-sm">
                                {{ $event->name }}
                            </h1>
                        </div>

                        @if($event->short_description)
                        <p class="text-lg lg:text-xl text-blue-100/80 leading-relaxed max-w-2xl font-light">
                            {{ $event->short_description }}
                        </p>
                        @endif

                        <div class="flex flex-wrap justify-center lg:justify-start gap-6 pt-4">
                            <div class="flex items-center gap-3 bg-white/5 border border-white/10 px-5 py-3 rounded-2xl backdrop-blur-md hover:bg-white/10 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs text-blue-200/60 uppercase tracking-wider">Date</p>
                                    <p class="text-white font-bold">{{ $event->day_represent }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 bg-white/5 border border-white/10 px-5 py-3 rounded-2xl backdrop-blur-md hover:bg-white/10 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs text-purple-200/60 uppercase tracking-wider">Time</p>
                                    <p class="text-white font-bold">{{ $event->start_time }} - {{ $event->end_time }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 bg-white/5 border border-white/10 px-5 py-3 rounded-2xl backdrop-blur-md hover:bg-white/10 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs text-emerald-200/60 uppercase tracking-wider">Location</p>
                                    <p class="text-white font-bold">{{ $event->address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Image -->
                    @if($event->image_represent_path)
                    <div class="hidden lg:flex justify-center relative">
                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-500 to-purple-600 blur-[100px] opacity-40 rounded-full"></div>
                        <div class="relative w-[450px] h-[450px] rounded-[2rem] p-2 bg-gradient-to-br from-white/20 to-white/5 backdrop-blur-sm border border-white/20 shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-700 ease-out">
                            <img src="{{ asset('storage/' . $event->image_represent_path) }}" class="w-full h-full object-cover rounded-[1.5rem] shadow-inner" />
                        </div>
                    </div>
                    @endif
                </div>
                <div class="relative z-10 max-w-6xl w-full mx-auto px-6 py-6">
                    @foreach($event->participants as $participant)
                    <div class="flex items-center gap-2">
                        @if($participant->avatar_path)
                        <img src="{{ asset('storage/' . $participant->avatar_path) }}" class="w-12 h-12 rounded-full" />
                        @else
                        <div class="w-12 h-12 rounded-full bg-gray-500/20 flex items-center justify-center text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7-7h14a7 7 0 00-7 7z" />
                            </svg>
                        </div>
                        @endif
                        <p class="text-lg text-white font-bold">{{ $participant->user->name }}</p>
                        <p class="text-lg text-gray-300">{{ App\Utils\Constants\EventUserRole::label($participant->role) }}</p>
                    </div>
                    @endforeach
                </div>

            </div>
            <!-- Slide 2: Schedule -->
            <div class="swiper-slide relative flex items-center justify-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#111827] to-[#1e1b4b] z-0"></div>
                <div class="absolute inset-0 opacity-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] z-0"></div>
                <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-3xl"></div>

                <div class="relative z-10 max-w-7xl w-full mx-auto px-6 h-full flex flex-col justify-center py-12">
                    <!-- Header Section -->
                    <div class="text-center mb-10">
                        <h2 class="text-4xl lg:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-200 via-white to-indigo-200 drop-shadow-sm mb-3">
                            {{ __('admin.events.schedule') }}
                        </h2>
                        <div class="h-1 w-24 bg-gradient-to-r from-indigo-500 to-purple-500 mx-auto rounded-full"></div>
                    </div>

                    <!-- Schedule Grid -->
                    <div class="overflow-y-auto max-h-[calc(100vh-280px)] pr-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 content-start scrollbar-thin scrollbar-thumb-indigo-500/30 scrollbar-track-transparent hover:scrollbar-thumb-indigo-500/50">

                        @forelse($event->schedules()->orderBy('start_time')->get() as $schedule)

                        <div class="group relative bg-gradient-to-br from-white/5 to-white/[0.02] hover:from-white/10 hover:to-white/5 border border-white/10 rounded-2xl p-5 transition-all duration-300 hover:border-indigo-400/40 hover:shadow-xl hover:shadow-indigo-500/20 transform hover:-translate-y-1 backdrop-blur-sm flex flex-col h-full">

                            <!-- Time Badge -->
                            <div class="mb-4">
                                <div class="inline-flex items-center gap-2 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-400/30 rounded-xl px-4 py-2.5 group-hover:border-indigo-400/50 transition-all duration-300 group-hover:shadow-lg group-hover:shadow-indigo-500/20">
                                    <svg class="w-4 h-4 text-indigo-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="text-left">
                                        <div class="text-indigo-200 font-bold text-sm leading-none">
                                            {{ $schedule->start_time }}
                                        </div>
                                        <div class="text-indigo-400 text-xs font-medium mt-0.5">
                                            {{ $schedule->end_time }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-grow space-y-3">
                                <h3 class="text-lg font-bold text-white group-hover:text-indigo-200 transition-colors leading-tight line-clamp-2">
                                    {{ $schedule->title }}
                                </h3>

                                @if($schedule->description)
                                <div class="text-gray-300 text-sm leading-relaxed line-clamp-3">
                                    {!! strip_tags($schedule->description) !!}
                                </div>
                                @endif

                                @if($schedule->speaker)
                                <div class="flex items-center gap-2 pt-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500/30 to-purple-500/30 flex items-center justify-center border border-indigo-400/30 flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-indigo-400/60 uppercase tracking-wide leading-none">Speaker</p>
                                        <p class="text-indigo-300 font-semibold text-sm truncate mt-0.5">
                                            {{ $schedule->speaker }}
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Files Section -->
                            @php
                            $files = $schedule->files ?? collect();
                            $fileCount = $files->count();
                            @endphp

                            @if($fileCount > 0)
                            <div class="mt-4 pt-4 border-t border-white/10">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-xs text-gray-400 font-medium">
                                            {{ $fileCount }} {{ $fileCount === 1 ? 'file' : 'files' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-1.5 max-h-24 overflow-y-auto scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                                    @foreach($files->take(3) as $file)
                                    <a href="{{ asset('storage/' . $file->file_path) }}"
                                        download="{{ $file->file_name }}"
                                        class="flex items-center gap-2 p-2 rounded-lg bg-white/5 hover:bg-emerald-500/10 border border-white/5 hover:border-emerald-500/30 transition-all duration-200 group/file">
                                        <div class="w-7 h-7 rounded bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center flex-shrink-0 border border-emerald-400/20 group-hover/file:border-emerald-400/40 transition-colors">
                                            <span class="text-emerald-300 text-[10px] font-bold uppercase">
                                                {{ $file->file_extension }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs text-white font-medium truncate group-hover/file:text-emerald-300 transition-colors">
                                                {{ $file->file_name }}
                                            </p>
                                            <p class="text-[10px] text-gray-500">
                                                {{ number_format($file->file_size / 1024, 1) }} KB
                                            </p>
                                        </div>
                                        <svg class="w-3.5 h-3.5 text-emerald-400 opacity-0 group-hover/file:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                    @endforeach

                                    @if($fileCount > 3)
                                    <div class="text-center pt-1">
                                        <span class="text-xs text-indigo-400/60">+{{ $fileCount - 3 }} more files</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Hover indicator -->
                            <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>

                            <!-- Decorative gradient line -->
                            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-b-2xl"></div>
                        </div>

                        @empty

                        <!-- Empty State -->
                        <div class="col-span-full text-center py-20 bg-gradient-to-br from-white/5 to-white/[0.02] rounded-3xl border-2 border-white/10 border-dashed">
                            <div class="relative inline-block mb-6">
                                <div class="absolute inset-0 bg-indigo-500/20 blur-2xl rounded-full"></div>
                                <div class="relative text-7xl">📅</div>
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-3">
                                {{ __('admin.events.no_schedule_yet') }}
                            </h3>
                            <p class="text-gray-400 text-lg">
                                {{ __('admin.events.event_schedule_will_be_updated_soon') }}
                            </p>
                        </div>

                        @endforelse

                    </div>
                </div>
            </div>

            <!-- Slide 3: Gallery (if images exist) -->
            @if($event->images && is_array($event->images) && count($event->images) > 0)

            @foreach($event->images as $image)
            <div class="swiper-slide relative flex items-center justify-center overflow-hidden">
                <div class="relative aspect-square overflow-hidden rounded-2xl border border-white/10 bg-white/5 shadow-2xl">
                    <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                    <div class="absolute inset-0 bg-black/0 transition-colors duration-300"></div>
                </div>
            </div>
            @endforeach
            @endif

        </div>

        <!-- Pagination -->
        <div class="swiper-pagination !bottom-8"></div>

        <!-- Navigation -->
        <div class="swiper-button-next !text-white/50 hover:!text-white transition-colors !w-12 !h-12 after:!text-2xl"></div>
        <div class="swiper-button-prev !text-white/50 hover:!text-white transition-colors !w-12 !h-12 after:!text-2xl"></div>
    </div>
</div>

<style>
    .swiper-pagination-bullet {
        background: white;
        opacity: 0.3;
        width: 10px;
        height: 10px;
        transition: all 0.3s;
    }

    .swiper-pagination-bullet-active {
        opacity: 1;
        width: 30px;
        border-radius: 5px;
        background: #6366f1;
        /* Indigo 500 */
    }
</style>
@endsection