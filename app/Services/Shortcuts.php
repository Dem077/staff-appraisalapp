<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Spatie\Color\Rgb;
use Filament\Support\Colors\Color;

class Shortcuts
{
    public static function callgetapi($url ,array $data)
    {
        return Http::withHeaders([
            'X-API-KEY' => config('app.appkey'),
            'Accept' => 'application/json',
        ])->timeout(10)->get(config('app.apiurl') . $url, $data);

    }

    public static function callpostapi($url ,array $data)
    {
        return Http::withHeaders([
            'X-API-KEY' => config('app.apikey'),
            'Accept' => 'application/json',
        ])->timeout(10)->post(config('app.apiurl') . $url, $data);

    }

    public static function setcolor($color_rgb)
    {
        return Color::hex(Rgb::fromString($color_rgb)->toHex());
    }

}
