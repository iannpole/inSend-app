<?php

namespace App\Services\Delivery;

use App\Models\DeliveryZone;

class DeliveryService
{
    // Default store location (set to your actual store coordinates)
    private float $storeLat;
    private float $storeLng;

    public function __construct()
    {
        // Default: Jakarta area — update these in .env
        $this->storeLat = (float) env('STORE_LATITUDE', -6.2088);
        $this->storeLng = (float) env('STORE_LONGITUDE', 106.8456);
    }

    /**
     * Calculate delivery fee based on distance (Haversine formula)
     */
    public function calculateFee(float $lat, float $lng): array
    {
        $distance = $this->haversine($this->storeLat, $this->storeLng, $lat, $lng);
        $distance = round($distance, 2);

        // Find applicable zone
        $zone = DeliveryZone::active()
            ->where('max_distance', '>=', $distance)
            ->orderBy('max_distance', 'asc')
            ->first();

        if (!$zone) {
            return [
                'is_deliverable'  => false,
                'distance_km'     => $distance,
                'fee'             => 0,
                'message'         => 'Maaf, lokasi Anda di luar jangkauan pengiriman kami.',
                'zone'            => null,
                'estimated_time'  => null,
            ];
        }

        // Calculate fee: base_fee + (distance * per_km_fee)
        $fee = $zone->base_fee + ($distance * $zone->per_km_fee);
        $fee = round($fee / 500) * 500; // Round to nearest 500

        return [
            'is_deliverable'  => true,
            'distance_km'     => $distance,
            'fee'             => $fee,
            'formatted_fee'   => 'Rp ' . number_format($fee, 0, ',', '.'),
            'zone'            => [
                'id'   => (string) $zone->_id,
                'name' => $zone->name,
            ],
            'estimated_time'  => $zone->estimated_time ?? $this->estimateTime($distance),
            'min_order'       => $zone->min_order ?? 0,
            'message'         => null,
        ];
    }

    /**
     * Calculate delivery fee based on address ID
     */
    public function calculateFeeFromAddress(\App\Models\Address $address): array
    {
        if (!$address->hasCoordinates()) {
            // No coordinates — use default zone fee
            $defaultZone = DeliveryZone::active()->orderBy('base_fee', 'asc')->first();

            return [
                'is_deliverable'  => true,
                'distance_km'     => null,
                'fee'             => $defaultZone ? $defaultZone->base_fee : 15000,
                'formatted_fee'   => 'Rp ' . number_format($defaultZone ? $defaultZone->base_fee : 15000, 0, ',', '.'),
                'zone'            => $defaultZone ? ['id' => (string) $defaultZone->_id, 'name' => $defaultZone->name] : null,
                'estimated_time'  => $defaultZone->estimated_time ?? '45-90 menit',
                'min_order'       => $defaultZone->min_order ?? 0,
                'message'         => 'Ongkir dihitung berdasarkan zona default (koordinat tidak tersedia)',
            ];
        }

        return $this->calculateFee($address->lat, $address->lng);
    }

    /**
     * Get available delivery slots
     */
    public function getAvailableSlots(): array
    {
        $today    = now();
        $slots    = [];
        $timeSlots = [
            ['label' => 'Pagi',  'start' => '08:00', 'end' => '11:00'],
            ['label' => 'Siang', 'start' => '11:00', 'end' => '14:00'],
            ['label' => 'Sore',  'start' => '14:00', 'end' => '17:00'],
            ['label' => 'Malam', 'start' => '17:00', 'end' => '20:00'],
        ];

        // Generate slots for next 3 days
        for ($d = 0; $d < 3; $d++) {
            $date       = $today->copy()->addDays($d);
            $dateStr    = $date->format('Y-m-d');
            $dateLabel  = $d === 0 ? 'Hari ini' : ($d === 1 ? 'Besok' : $date->translatedFormat('l, d M'));

            foreach ($timeSlots as $slot) {
                // Skip past slots for today
                if ($d === 0) {
                    $slotEnd = \Carbon\Carbon::parse($dateStr . ' ' . $slot['end']);
                    if ($today->greaterThanOrEqualTo($slotEnd)) {
                        continue;
                    }
                }

                $slots[] = [
                    'date'       => $dateStr,
                    'date_label' => $dateLabel,
                    'label'      => $slot['label'],
                    'start'      => $slot['start'],
                    'end'        => $slot['end'],
                    'display'    => "{$dateLabel} ({$slot['label']}: {$slot['start']} - {$slot['end']})",
                ];
            }
        }

        return $slots;
    }

    /**
     * Haversine formula — calculate distance between two coordinates (in km)
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Estimate delivery time based on distance
     */
    private function estimateTime(float $distanceKm): string
    {
        if ($distanceKm <= 3) return '20-30 menit';
        if ($distanceKm <= 5) return '30-45 menit';
        if ($distanceKm <= 10) return '45-60 menit';
        return '60-90 menit';
    }
}
