@component('mail::message')
# {{ (app()->getLocale() === 'vi') ? 'Xác thực Email' : 'Verify Email' }}

{{ (app()->getLocale() === 'vi') ? 'Để xác thực email của bạn, vui lòng nhấp vào nút bên dưới:' : 'To verify your email, please click the button below:' }}

@component('mail::button', ['url' => $url])
{{ (app()->getLocale() === 'vi') ? 'Xác thực Email' : 'Verify Email' }}
@endcomponent

{{ (app()->getLocale() === 'vi') ? 'Nếu bạn không thể nhấp vào nút, hãy sao chép và dán URL sau vào trình duyệt:' : 'If you cannot click the button, please copy and paste the URL below into your browser:' }}

{{ $url }}

{{ (app()->getLocale() === 'vi') ? 'Liên kết này sẽ hết hạn sau 60 phút.' : 'This link will expire in 60 minutes.' }}

{{ (app()->getLocale() === 'vi') ? 'Trân trọng' : 'Best regards' }},  
{{ config('app.name') }}
@endcomponent
