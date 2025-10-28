@php
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($url);
    $fileName = 'qr_code_' . \Illuminate\Support\Str::slug($event->name) . '.png';
@endphp

<div class="">
    <p style="word-break: break-all;">{{ __('admin.events.qr.participation_link') }}: <x-filament::link href="{{$url}}" target="_blank">{{$url}}</x-filament::link></p>
    <img src="{{ $qrUrl }}" style="margin: 0 auto;" alt="QR Code" class="mx-auto">
</div>

<x-filament::link x-data href="#" class="text-primary-600 text-center hover:text-primary-500"
    x-on:click.prevent="
            fetch('{{ $qrUrl }}')
                .then(response => response.blob())
                .then(blob => {
                    const blobUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = '{{ $fileName }}';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    URL.revokeObjectURL(blobUrl);
                })
                .catch(error => console.error('{{ __('admin.events.qr.download_error') }}:', error));
        ">
    {{ __('admin.events.qr.download_qr') }}
</x-filament::link>
