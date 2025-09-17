<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;


class LocationPicker extends Field
{

    protected string $view = 'filament.forms.components.location-picker';

    protected string $googleMapsApiKey = '';

    protected array $defaultLocation = [
        'lat' => 21.0285, // Hà Nội
        'lng' => 105.8542
    ];

    protected int $zoom = 13;
    protected int $height = 400;

    public function googleMapsApiKey(string $key): static
    {
        $this->googleMapsApiKey = $key;
        return $this;
    }

    public function getGoogleMapsApiKey(): string
    {
        return $this->googleMapsApiKey ?: config('services.google.map_key_api', '');
    }

    public function defaultLocation(float $lat, float $lng): static
    {
        $this->defaultLocation = [
            'lat' => $lat,
            'lng' => $lng
        ];
        return $this;
    }

    public function getDefaultLocation(): array
    {
        return $this->defaultLocation;
    }

    public function zoom(int $zoom): static
    {
        $this->zoom = $zoom;
        return $this;
    }

    public function getZoom(): int
    {
        return $this->zoom;
    }

    public function height(int $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
