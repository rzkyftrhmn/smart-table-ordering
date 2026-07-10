<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantLocation extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'radius_meters',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_meters' => 'integer',
    ];

    /**
     * Helper ini ambil row itu (atau bikin default kalau belum ada sama sekali).
     */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'latitude' => 0,
            'longitude' => 0,
            'radius_meters' => 100,
        ]);
    }

    /**
     * Hitung jarak (dalam meter) dari titik lat/long tertentu ke lokasi resto.
     */
    public function distanceFrom(float $latitude, float $longitude): float
    {
        $earthRadius = 6371000; // meter

        $latFrom = deg2rad((float) $this->latitude);
        $lonFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDiff = $latTo - $latFrom;
        $lonDiff = $lonTo - $lonFrom;

        $a = sin($latDiff / 2) ** 2 +
             cos($latFrom) * cos($latTo) *
             sin($lonDiff / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Cek apakah titik lat/long tertentu masih dalam radius yang diizinkan.
     */
    public function isWithinRadius(float $latitude, float $longitude): bool
    {
        return $this->distanceFrom($latitude, $longitude) <= $this->radius_meters;
    }
}