<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} - Event Screen</title>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="{{ asset('swiper/swiper-bundle.min.css') }}" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #000;
            overflow: hidden;
        }

        .swiper {
            width: 100vw;
            height: 100vh;
        }

        .swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }

        /* Slide 1 - Event Info */
        .event-info-slide {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 60px;
            color: white;
        }

        .event-info-content {
            max-width: 1200px;
            width: 100%;
            text-align: center;
        }

        .event-logo {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 20px;
            margin: 0 auto 40px;
            display: block;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 5px solid rgba(255, 255, 255, 0.2);
        }

        .event-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 2px 20px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }

        .event-description {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.95;
            line-height: 1.6;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .event-meta {
            display: flex;
            justify-content: center;
            gap: 50px;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .meta-icon {
            font-size: 2.5rem;
            opacity: 0.9;
        }

        .meta-label {
            font-size: 1rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .meta-value {
            font-size: 1.8rem;
            font-weight: 700;
        }

        /* Slide 2 - Schedule */
        .schedule-slide {
            background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);
            padding: 60px;
            color: white;
        }

        .schedule-content {
            max-width: 1400px;
            width: 100%;
        }

        .schedule-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 50px;
            text-align: center;
            text-shadow: 2px 2px 20px rgba(0, 0, 0, 0.3);
        }

        .schedule-timeline {
            display: grid;
            gap: 25px;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            padding-right: 20px;
        }

        .schedule-timeline::-webkit-scrollbar {
            width: 8px;
        }

        .schedule-timeline::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .schedule-timeline::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .schedule-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 30px;
            align-items: center;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .schedule-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .schedule-time {
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 15px;
        }

        .schedule-details h3 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .schedule-details p {
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Slide 3+ - Images */
        .image-slide {
            background: #000;
        }

        .image-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Swiper Pagination */
        .swiper-pagination-bullet {
            width: 15px;
            height: 15px;
            background: white;
            opacity: 0.5;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
            background: white;
        }

        /* Navigation Buttons */
        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            backdrop-filter: blur(10px);
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 25px;
        }

        /* Auto-play indicator */
        .autoplay-progress {
            position: absolute;
            right: 30px;
            bottom: 30px;
            z-index: 10;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
        }

        .autoplay-progress svg {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            stroke-width: 4px;
            stroke: white;
            fill: none;
            stroke-dashoffset: calc(125.6 * (1 - var(--progress)));
            stroke-dasharray: 125.6;
            transform: rotate(-90deg);
        }

        @media (max-width: 768px) {
            .event-title {
                font-size: 2.5rem;
            }

            .event-description {
                font-size: 1.2rem;
            }

            .schedule-title {
                font-size: 2.5rem;
            }

            .schedule-item {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .schedule-time {
                font-size: 1.5rem;
            }

            .schedule-details h3 {
                font-size: 1.5rem;
            }

            .schedule-details p {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="swiper eventSwiper">
        <div class="swiper-wrapper">
            <!-- Slide 1: Event Info -->
            <div class="swiper-slide event-info-slide">
                <div class="event-info-content">
                    @if($event->image_represent_path)
                    <img src="{{ asset('storage/' . $event->image_represent_path) }}" alt="{{ $event->name }}" class="event-logo">
                    @endif

                    <h1 class="event-title">{{ $event->name }}</h1>

                    @if($event->short_description)
                    <p class="event-description">{{ $event->short_description }}</p>
                    @endif

                    <div class="event-meta">
                        <div class="meta-item">
                            <div class="meta-icon">📅</div>
                            <div class="meta-label">Ngày</div>
                            <div class="meta-value">{{ $event->day_represent->format('d/m/Y') }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">🕐</div>
                            <div class="meta-label">Thời gian</div>
                            <div class="meta-value">{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">📍</div>
                            <div class="meta-label">Địa điểm</div>
                            <div class="meta-value">{{ $event->address }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2: Schedule -->
            <div class="swiper-slide schedule-slide">
                <div class="schedule-content">
                    <h2 class="schedule-title">Lịch Trình Sự Kiện</h2>
                    <div class="schedule-timeline">
                        @forelse($event->schedules()->orderBy('start_time')->get() as $schedule)
                        <div class="schedule-item">
                            <div class="schedule-time">
                                {{ $schedule->start_time->format('H:i') }}<br>-<br>{{ $schedule->end_time->format('H:i') }}
                            </div>
                            <div class="schedule-details">
                                <h3>{{ $schedule->title }}</h3>
                                @if($schedule->description)
                                <p>{!! strip_tags($schedule->description) !!}</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="schedule-item">
                            <div class="schedule-details" style="grid-column: 1 / -1; text-align: center;">
                                <h3>Chưa có lịch trình</h3>
                                <p>Lịch trình sự kiện sẽ được cập nhật sớm</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Slide 3+: Banner Images -->
            @if($event->images && is_array($event->images) && count($event->images) > 0)
            @foreach($event->images as $image)
            <div class="swiper-slide image-slide">
                <img src="{{ asset('storage/' . $image) }}" alt="{{ $event->name }}">
            </div>
            @endforeach
            @endif
        </div>

        <!-- Pagination -->
        <div class="swiper-pagination"></div>

        <!-- Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>

        <!-- Autoplay Progress -->
        <div class="autoplay-progress">
            <svg viewBox="0 0 40 40">
                <circle cx="20" cy="20" r="20"></circle>
            </svg>
            <span></span>
        </div>
    </div>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        const progressCircle = document.querySelector(".autoplay-progress svg");
        const progressContent = document.querySelector(".autoplay-progress span");

        const swiper = new Swiper('.eventSwiper', {
            // Enable keyboard control
            keyboard: {
                enabled: true,
                onlyInViewport: false,
            },

            // Autoplay
            autoplay: {
                delay: 10000, // 10 seconds per slide
                disableOnInteraction: false,
            },

            // Loop
            loop: true,

            // Speed
            speed: 800,

            // Effect
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },

            // Pagination
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },

            // Navigation
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },

            // Events
            on: {
                autoplayTimeLeft(s, time, progress) {
                    progressCircle.style.setProperty("--progress", 1 - progress);
                    progressContent.textContent = `${Math.ceil(time / 1000)}s`;
                }
            }
        });

        // Fullscreen on F11
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F11') {
                e.preventDefault();
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            }
        });
    </script>
</body>

</html>