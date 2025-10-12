<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>{{ $event_name }}</title>
</head>

<body>
    <h2>🎉 Sự kiện {{ $event_name }} đã bắt đầu!</h2>

    <p><strong>Thời gian bắt đầu:</strong> {{ $start_time }}</p>

    @if (!empty($latitude) && !empty($longitude))
        <p>
            <strong>Vị trí:</strong>
            <a href="{{ $map_link }}" target="_blank">
                Xem bản đồ tại đây
            </a>
            (Lat: {{ $latitude }}, Long: {{ $longitude }})
        </p>
    @endif

    <p>Chúc bạn có trải nghiệm tuyệt vời tại sự kiện!</p>
</body>

</html>
