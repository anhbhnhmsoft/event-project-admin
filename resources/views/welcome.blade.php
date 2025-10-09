<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KAMNEX - Hệ thống thiết bị phòng họp trực tuyến</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon"
        href="https://kamnex.com/wp-content/uploads/2024/05/cropped-f8cb1fdf-4b62-4037-ac5b-8d7474a36c41-32x32.png"
        sizes="32x32">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .logo span {
            color: #fbbf24;
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
    <div class="container">
        <div class="header">
            <div class="logo">KAM<span>NEX</span></div>
            <div class="tagline">Hệ thống thiết bị phòng họp trực tuyến</div>
        </div>

        <div class="content">
            <div class="left-section">
                <h1>Giải pháp phòng họp thông minh cho doanh nghiệp của bạn</h1>
                <p>KAMNEX cung cấp các thiết bị và giải pháp hiện đại trong lĩnh vực hội nghị trực tuyến, phòng họp
                    thông minh, và quản lý giáo dục.</p>

                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">✓</div>
                        <div class="feature-text">Hội nghị trực tuyến chuyên nghiệp (Video conferencing)</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">✓</div>
                        <div class="feature-text">Thiết bị âm thanh hội thảo chất lượng cao</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">✓</div>
                        <div class="feature-text">Hệ thống âm thanh thông báo (PA system)</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">✓</div>
                        <div class="feature-text">Lớp học thông minh với phần mềm quản lý</div>
                    </div>
                </div>

                <div class="cta-buttons">
                    <a href="#contact" class="btn btn-primary">Liên hệ ngay</a>
                    <a href="#about" class="btn btn-secondary">Tìm hiểu thêm</a>
                </div>
            </div>

            <div class="right-section">
                <div class="card-showcase">
                    <h3>Được tin tưởng bởi</h3>
                    <p>Hàng trăm doanh nghiệp lớn - vừa - nhỏ trong và ngoài nước</p>

                    <div class="stats">
                        <div class="stat">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Dự án triển khai</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Khách hàng hài lòng</div>
                        </div>
                    </div>

                    <div class="badge">Chứng nhận ISO 9001 & ISO/IEC 27001</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
