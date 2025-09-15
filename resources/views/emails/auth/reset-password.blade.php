@component('mail::message')
# {{ (app()->getLocale() === 'vi') ? 'Đặt lại mật khẩu của bạn' : 'Reset Your Password' }}

{{ (app()->getLocale() === 'vi') ? 'Mã đặt lại mật khẩu của bạn là: ' . $code . '. Mã này sẽ hết hạn sau 10 phút.' : 'Your password reset code is: ' . $code . '. This code will expire in 10 minutes.' }}

{{ (app()->getLocale() === 'vi') ? 'Trân trọng' : 'Best regards' }},  
{{ config('app.name') }}
@endcomponent
