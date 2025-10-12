@component('mail::message')
# {{ $title }}

{{ $message }}

Cảm ơn bạn đã đồng hành cùng chúng tôi,<br>
{{ config('app.name') }}
@endcomponent
