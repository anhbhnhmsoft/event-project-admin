@component('mail::message')
# {{ $title }}

{{ $message }}

{{ __('emails.membership.expire_thank_you') }}<br>
{{ config('app.name') }}
@endcomponent