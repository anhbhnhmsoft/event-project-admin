<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>{{ $event_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
        .header { background-color: #4CAF50; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px 0; }
        .highlight { color: #4CAF50; font-weight: bold; }
        .info-box { background-color: #fff; padding: 15px; border: 1px dashed #ccc; border-radius: 5px; margin-top: 15px; }
        .info-box p { margin: 5px 0; }
        .footer { margin-top: 20px; text-align: center; font-size: 0.9em; color: #777; }
        .description { font-style: italic; color: #555; border-left: 3px solid #ddd; padding-left: 10px; margin: 15px 0; }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2> Sự kiện {{ $event_name }} sắp diễn ra!</h2>
        </div>

        <div class="content">
            <p>Xin chào,</p>
            <p>Chúng tôi thông báo rằng sự kiện mà bạn quan tâm sắp diễn ra! Hãy tham gia ngay theo thông tin dưới đây:</p>

            <div class="info-box">
                <p><strong>Tên sự kiện:</strong> <span class="highlight">{{ $event_name }}</span></p>
                @isset($organizer_name)
                    <p><strong>Đơn vị tổ chức:</strong> <span class="highlight">{{ $organizer_name }}</span></p>
                @endisset

                @isset($short_description)
                    <div class="description">
                        {{ $short_description }}
                    </div>
                @endisset

                <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">

                <p><strong>Thời gian bắt đầu:</strong> <span class="highlight">{{ $start_time }}</span></p>

                @isset($address)
                    <p><strong>Địa điểm:</strong> {{ $address }}</p>
                @endisset

                @if (!empty($latitude) && !empty($longitude))
                    <p>
                        <strong>Vị trí trên bản đồ:</strong>
                        <a href="{{ $map_link }}" target="_blank" style="color: #1a73e8; text-decoration: none;">
                            [Xem bản đồ tại đây]
                        </a>
                    </p>
                @endif
            </div>

            <p style="margin-top: 20px;">Chúc bạn có trải nghiệm tuyệt vời tại sự kiện!</p>
            <p>Trân trọng,</p>
            <p>Đội ngũ tổ chức sự kiện.</p>
        </div>

        <div class="footer">
            <p>Bạn nhận được email này vì bạn đã đăng ký hoặc quan tâm đến sự kiện này.</p>
        </div>
    </div>
</body>

</html>
