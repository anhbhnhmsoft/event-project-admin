<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Xin chào {{ $user->name }},</h2>

        <p>Cảm ơn bạn đã đăng ký tài khoản tại {{ config('app.name') }}!</p>

        <p>Vui lòng click vào nút bên dưới để xác thực địa chỉ email của bạn:</p>

        <a href="{{ $url }}" class="button">Xác thực Email</a>

        <p>Nếu bạn không thể click vào nút trên, vui lòng copy và paste đường link sau vào trình duyệt:</p>
        <p style="word-break: break-all; color: #3b82f6;">{{ $url }}</p>

        <p><strong>Lưu ý:</strong> Link xác thực sẽ hết hạn sau 60 phút.</p>

        <div class="footer">
            <p>Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email này.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
