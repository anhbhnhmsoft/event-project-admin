<!DOCTYPE html>
<html>

<head>
    <title>Registration Successful</title>
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
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .content {
            padding: 20px;
        }

        .ticket-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .label {
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>{{ $data['event_name'] ?? 'Event' }}</h2>
        </div>
        <div class="content">
            <p>Xin chào / Hello <strong>{{ $data['user_name'] }}</strong>,</p>

            <p>
                Bạn đã đăng ký tham gia sự kiện thành công. Dưới đây là thông tin vé của bạn:<br>
                <em>You have successfully registered for the event. Here is your ticket information:</em>
            </p>

            <div class="ticket-info">
                <p>
                    <span class="label">Sự kiện / Event:</span> {{ $data['event_name'] }}<br>
                    <span class="label">Mã vé / Ticket Code:</span> <strong>{{ $data['ticket_code'] }}</strong>
                    @if(!empty($data['seat_name']))
                    <br>
                    <span class="label">Ghế / Seat:</span> {{ $data['seat_name'] }}
                    @endif
                </p>
            </div>

            <p>
                Vui lòng mang theo mã vé này khi đến tham dự sự kiện.<br>
                <em>Please bring this ticket code when you attend the event.</em>
            </p>
        </div>
        <div class="footer">
            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.<br>Thank you for using our service.</p>
        </div>
    </div>
</body>

</html>