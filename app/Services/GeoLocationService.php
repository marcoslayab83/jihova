<?php

namespace App\Services;

use App\Models\StoreBranch;

class GeoLocationService
{
    public const DEFAULT_LAT = 10.4880;

    public const DEFAULT_LNG = -66.8792;

    public const DEFAULT_ZONE_LABEL = 'Centro · Plaza Bolívar';

    /**
     * @return list<array{key: string, label: string, latitude: float, longitude: float}>
     */
    public function presetZones(): array
    {
        return [
            [
                'key' => 'centro',
                'label' => 'Centro · Plaza Bolívar',
                'latitude' => 10.5061,
                'longitude' => -66.9146,
            ],
            [
                'key' => 'chacao',
                'label' => 'Chacao · Zona Este',
                'latitude' => 10.4959,
                'longitude' => -66.8537,
            ],
            [
                'key' => 'las-mercedes',
                'label' => 'Las Mercedes · Mall Central',
                'latitude' => 10.4806,
                'longitude' => -66.8615,
            ],
            [
                'key' => 'zona-norte',
                'label' => 'Altamira · Zona Norte',
                'latitude' => 10.4998,
                'longitude' => -66.8495,
            ],
            [
                'key' => 'zona-sur',
                'label' => 'Santa Mónica · Zona Sur',
                'latitude' => 10.4742,
                'longitude' => -66.8895,
            ],
        ];
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    public function distanceToBranchKm(StoreBranch $branch, float $customerLat, float $customerLng): float
    {
        return $this->distanceKm(
            (float) $branch->latitude,
            (float) $branch->longitude,
            $customerLat,
            $customerLng
        );
    }

    public function isWithinRadius(StoreBranch $branch, float $customerLat, float $customerLng): bool
    {
        return $this->distanceToBranchKm($branch, $customerLat, $customerLng) <= (float) $branch->delivery_radius_km;
    }

    public function deliveryFeeForBranch(StoreBranch $branch, float $customerLat, float $customerLng): float
    {
        $distance = $this->distanceToBranchKm($branch, $customerLat, $customerLng);
        $baseFee = (float) ($branch->base_delivery_fee ?? 2.50);
        $extraKm = max(0.0, $distance - 1.5);

        return round($baseFee + ($extraKm * 0.65), 2);
    }

    public function estimatedMinutesForBranch(StoreBranch $branch, float $customerLat, float $customerLng): int
    {
        $distance = $this->distanceToBranchKm($branch, $customerLat, $customerLng);
        $transitMinutes = max(10, (int) ceil($distance * 4.2));

        return (int) $branch->avg_prep_time_minutes + $transitMinutes;
    }
}
