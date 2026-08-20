<?php

namespace App\Services;

class GeoDistanceService
{
    public function distanceKm(float $latitudeA, float $longitudeA, float $latitudeB, float $longitudeB): float
    {
        $earthRadius = 6371.0088;
        $latitudeDelta = deg2rad($latitudeB - $latitudeA);
        $longitudeDelta = deg2rad($longitudeB - $longitudeA);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * sin($longitudeDelta / 2) ** 2;

        return round($earthRadius * 2 * asin(min(1, sqrt($a))), 2);
    }
}
