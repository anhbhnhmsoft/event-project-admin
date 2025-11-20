<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('app.meta.title') }}</title>
    <meta name="description"
        content="{{ __('app.meta.description') }}">
    <meta name="keywords"
        content="{{ __('app.meta.keywords') }}">
    <meta name="author" content="{{ __('app.meta.author') }}">

    <meta property="og:title" content="{{ __('app.meta.og_title') }}">
    <meta property="og:description"
        content="{{ __('app.meta.og_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://michec.vn/">
    <meta property="og:image" content="/logo-michec.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@michec">
    <meta name="twitter:title" content="{{ __('app.meta.twitter_title') }}">
    <meta name="twitter:description"
        content="{{ __('app.meta.twitter_description') }}">
    <meta name="twitter:image" content="/logo-michec.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="/images/logo-michec-icon.png" sizes="32x32">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        /* Định nghĩa màu sắc mới - Chỉ sử dụng tông xanh biển */
        :root {
            --color-blue-dark: #1f73b7;
            /* Xanh biển đậm - Primary */
            --color-blue-light: #5b9bd5;
            /* Xanh biển sáng - Accent */
            --color-text-dark: #333333;
            --color-text-light: #555555;
            --color-white: #ffffff;
            --color-grey-light: #f4f7f9;
        }

        /* --- CSS CƠ BẢN VÀ THAY ĐỔI --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-white);
            /* Nền trắng */
            min-height: 100vh;
            padding-top: 80px;
            color: var(--color-text-dark);
        }

        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(31, 115, 183, 0.95);
            /* Xanh biển đậm */
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 15px 20px;
            z-index: 1000;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: var(--color-white);
            letter-spacing: -1px;
            cursor: pointer;
        }

        .logo span {
            color: var(--color-blue-light);
            /* Logo highlight màu xanh sáng */
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-nav {
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        /* Nút Đăng nhập trên navbar */
        .btn-login {
            background: none;
            color: var(--color-white);
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--color-white);
        }

        /* Nút Đăng ký trên navbar - Sử dụng màu xanh sáng hơn */
        .btn-signup {
            background: var(--color-blue-light);
            color: var(--color-white);
            box-shadow: 0 4px 10px rgba(91, 155, 213, 0.3);
        }

        .btn-signup:hover {
            background: #4784c0;
            transform: translateY(-1px);
        }

        /* PHẦN BODY CONTENT */

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .hero-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .tagline {
            color: var(--color-blue-dark);
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 6px 16px;
            display: inline-block;
            background-color: var(--color-grey-light);
            /* Nền xám nhạt */
            border-radius: 20px;
            border: 1px solid var(--color-blue-light);
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-top: 60px;
        }

        .left-section h1 {
            color: var(--color-blue-dark);
            font-size: 52px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 25px;
        }

        .left-section p {
            color: var(--color-text-light);
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
        }

        .feature {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .feature-icon {
            width: 28px;
            height: 28px;
            background: var(--color-blue-light);
            /* Xanh biển sáng */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--color-white);
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(91, 155, 213, 0.2);
            margin-top: 2px;
        }

        .feature-text {
            color: var(--color-text-dark);
            font-size: 16px;
            line-height: 1.5;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 16px 36px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        /* Nút CTA chính - Xanh biển sáng */
        .btn-primary {
            background: var(--color-blue-light);
            color: var(--color-white);
            box-shadow: 0 10px 20px rgba(91, 155, 213, 0.3);
        }

        .btn-primary:hover {
            background: #4784c0;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(91, 155, 213, 0.4);
        }

        /* Nút CTA phụ - Viền xanh đậm trên nền trắng */
        .btn-secondary {
            background: var(--color-white);
            color: var(--color-blue-dark);
            border: 2px solid var(--color-blue-dark);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary:hover {
            background: var(--color-blue-dark);
            color: var(--color-white);
            border-color: var(--color-blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(31, 115, 183, 0.2);
        }

        .right-section {
            position: relative;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-showcase {
            background: var(--color-white);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(31, 115, 183, 0.15);
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .card-showcase h3 {
            color: var(--color-blue-dark);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .card-showcase p {
            color: var(--color-text-light);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            padding: 24px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            margin: 24px 0;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--color-blue-light);
            /* Xanh biển sáng */
        }

        .stat-label {
            font-size: 13px;
            color: var(--color-text-light);
            margin-top: 4px;
        }

        .badge {
            display: inline-block;
            background: var(--color-grey-light);
            color: var(--color-blue-dark);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 16px;
            border: 1px solid var(--color-blue-light);
        }

        /* --- MEDIA QUERIES (RESPONSIVE) --- */
        @media (max-width: 900px) {
            .left-section h1 {
                font-size: 40px;
            }
        }

        @media (max-width: 768px) {
            .navbar-content {
                padding-right: 10px;
            }

            .content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .left-section h1 {
                font-size: 36px;
            }

            .left-section {
                text-align: center;
            }

            .left-section p {
                text-align: left;
            }

            .features {
                align-items: flex-start;
            }

            .cta-buttons {
                justify-content: center;
            }

            .right-section {
                height: auto;
            }

            .stats {
                flex-direction: row;
                gap: 15px;
            }

            .stat {
                flex: 1;
            }
        }

        @media (max-width: 480px) {
            .stats {
                flex-direction: column;
                gap: 20px;
            }

            .left-section h1 {
                font-size: 30px;
            }

            .left-section p {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <!-- Thanh điều hướng cố định (Fixed Header) -->
    <header class="navbar">
        <div class="navbar-content">
            <!-- Logo -->
            <div class="logo">MI<span>CHEC</span></div>

            <!-- Nút Đăng nhập và Đăng ký -->
            <div class="nav-buttons">
                <a href="/admin/login" class="btn-nav btn-login">{{ __('app.nav.login') }}</a>
                <a href="{{ route('signup') }}" class="btn-nav btn-signup">{{ __('app.nav.register') }}</a>
            </div>
        </div>
    </header>

    <!-- Nội dung chính (Main Content) -->
    <div class="main-content">
        <div class="container">
            <div class="hero-header">
                <!-- Tagline mới, sử dụng màu xanh -->
                <div class="tagline">{{ __('app.welcome.tagline') }}</div>
            </div>

            <div class="content">
                <div class="left-section">
                    <h1>{{ __('app.welcome.main_title') }}</h1>
                    <p>{{ __('app.welcome.main_description') }}</p>

                    <div class="features">
                        <div class="feature">
                            <div class="feature-icon">✓</div>
                            <div class="feature-text">
                                **Quản lý Đăng ký & Bán vé:** Tạo Landing Page và cổng đăng ký, bán vé tự động.
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">✓</div>
                            <div class="feature-text">
                                **Check-in Nhanh chóng:** Hệ thống QR Code, Face Recognition hoặc NFC cho tốc độ
                                check-in tức thì.
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">✓</div>
                            <div class="feature-text">
                                **Tương tác Trực tiếp:** Tính năng Q&A, Poll/Vote và Mini Game để kết nối khán giả.
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">✓</div>
                            <div class="feature-text">
                                **Phân tích & Báo cáo Chuyên sâu:** Đo lường hành vi, hiệu quả ROI và dữ liệu khách mời
                                theo thời gian thực.
                            </div>
                        </div>
                    </div>

                    <div class="cta-buttons">
                        <a href="#demo" class="btn btn-primary">{{ __('app.welcome.request_demo') }}</a>
                        <a href="#pricing" class="btn btn-secondary">{{ __('app.welcome.view_pricing') }}</a>
                    </div>
                </div>

                <div class="right-section">
                    <div class="card-showcase">
                        <h3>Giải pháp tối ưu cho mọi loại hình sự kiện</h3>
                        <p>Hệ thống MICHEC đã được kiểm chứng và tin dùng để tổ chức các sự kiện quy mô lớn và nhỏ.</p>

                        <div class="stats">
                            <div class="stat">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Sự kiện đã tổ chức</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">200K+</div>
                                <div class="stat-label">Khách mời đã phục vụ</div>
                            </div>
                        </div>

                        <div class="badge">Nền tảng sự kiện số 1 Việt Nam</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
