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
        <h2>{{ __('emails.auth.verify_greeting', ['name' => $user->name]) }}</h2>

        <p>{{ __('emails.auth.verify_thank_you', ['app_name' => config('app.name')]) }}</p>

        <p>{{ __('emails.auth.verify_instruction') }}</p>

        <a href="{{ $url }}" class="button">{{ __('emails.auth.verify_button') }}</a>

        <p>{{ __('emails.auth.verify_copy_link') }}</p>
        <p style="word-break: break-all; color: #3b82f6;">{{ $url }}</p>

        <p><strong>{{ __('emails.auth.verify_expire_note') }}</strong></p>

        <div class="footer">
            <p>{{ __('emails.auth.verify_ignore') }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('emails.common.all_rights_reserved') }}</p>
        </div>
    </div>
</body>

</html>