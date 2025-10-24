<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MICHEC: Hệ thống Tổ chức Sự kiện Toàn diện | Đăng ký, Check-in, Tương tác</title>
    <meta name="description"
        content="MICHEC là nền tảng quản lý sự kiện số hóa từ A-Z, giúp tối ưu hóa quy trình lập kế hoạch, đăng ký, check-in QR/Face Recognition và tương tác khách mời. Yêu cầu demo ngay!">
    <meta name="keywords"
        content="hệ thống tổ chức sự kiện, quản lý sự kiện, phần mềm check-in sự kiện, đăng ký sự kiện, event management system, MICHEC">
    <meta name="author" content="Michec Team">

    <meta property="og:title" content="MICHEC: Hệ thống Tổ chức Sự kiện Toàn diện | Tối ưu hóa A-Z">
    <meta property="og:description"
        content="Số hóa mọi quy trình sự kiện: đăng ký tự động, check-in nhanh chóng, tương tác trực tiếp và phân tích chuyên sâu. Tăng trải nghiệm khách mời, giảm chi phí vận hành.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://michec.vn/">
    <meta property="og:image" content="/logo-michec.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@michec">
    <meta name="twitter:title" content="MICHEC: Hệ thống Tổ chức Sự kiện Toàn diện | Đăng ký, Check-in, Tương tác">
    <meta name="twitter:description"
        content="Tối ưu hóa lập kế hoạch, đăng ký, check-in QR/Face Recognition, tương tác và báo cáo sự kiện với nền tảng MICHEC.">
    <meta name="twitter:image" content="/logo-michec.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" href="/images/logo-michec-icon.png" sizes="32x32">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        /* CSS CƠ BẢN VÀ THAY ĐỔI */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-top: 80px;
            /* Thêm padding để nội dung không bị header cố định che mất */
        }

        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(118, 75, 162, 0.95);
            /* Màu nền tím đậm của gradient */
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            color: white;
            letter-spacing: -1px;
            cursor: pointer;
        }

        .logo span {
            color: #fbbf24;
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

        .btn-login {
            background: none;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        .btn-signup {
            background: #fbbf24;
            color: #764ba2;
            box-shadow: 0 4px 10px rgba(251, 191, 36, 0.3);
        }

        .btn-signup:hover {
            background: #f59e0b;
            transform: translateY(-1px);
        }

        /* PHẦN BODY CONTENT CŨ */

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .hero-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            font-weight: 500;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
            margin-top: 60px;
        }

        .left-section h1 {
            color: white;
            font-size: 48px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .left-section p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 40px;
        }

        .feature {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .feature-icon {
            width: 24px;
            height: 24px;
            background: #fbbf24;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #764ba2;
            font-weight: bold;
            margin-top: 2px;
        }

        .feature-text {
            color: rgba(255, 255, 255, 0.95);
            font-size: 15px;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #fbbf24;
            color: #764ba2;
        }

        .btn-primary:hover {
            background: #f59e0b;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .right-section {
            position: relative;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-showcase {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .card-showcase h3 {
            color: #764ba2;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .card-showcase p {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            padding: 24px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            margin: 24px 0;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #fbbf24;
        }

        .stat-label {
            font-size: 13px;
            color: #999;
            margin-top: 4px;
        }

        .badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 16px;
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

            .right-section {
                height: auto;
            }

            .stats {
                flex-direction: column;
                gap: 20px;
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
                <a href="/admin/login" class="btn-nav btn-login">Đăng nhập</a>
                <a href="{{ route('signup') }}" class="btn-nav btn-signup">Đăng ký</a>
            </div>
        </div>
    </header>

    <!-- Nội dung chính (Main Content) -->
    <div class="main-content">
        <div class="container">
            <div class="hero-header">
                <!-- Giữ lại tagline ở đây để làm phần mở đầu nổi bật -->
                <div class="tagline">Hệ thống Tổ chức & Quản lý Sự kiện Toàn diện</div>
            </div>

            <div class="content">
                <div class="left-section">
                    <h1>Tối ưu hóa mọi sự kiện, từ A đến Z, với MICHEC</h1>
                    <p>MICHEC là nền tảng quản lý sự kiện chuyên nghiệp, giúp doanh nghiệp và tổ chức số hóa toàn bộ quy
                        trình: từ lập kế hoạch, đăng ký, check-in, đến tương tác và đo lường hiệu quả sau sự kiện. Tăng
                        cường trải nghiệm khách mời và tiết kiệm tối đa chi phí tổ chức.</p>

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
                                check-in
                                tức thì.
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
                                theo
                                thời gian thực.
                            </div>
                        </div>
                    </div>

                    <div class="cta-buttons">
                        <a href="#demo" class="btn btn-primary">Yêu cầu Demo ngay</a>
                        <a href="#pricing" class="btn btn-secondary">Xem Bảng giá</a>
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
