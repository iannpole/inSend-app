<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\DeliveryZone;
use App\Services\Delivery\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        private DeliveryService $deliveryService
    ) {}

    /**
     * POST /api/delivery/calculate — Calculate delivery fee
     */
    public function calculate(Request $request): JsonResponse
    {
        // Option 1: by coordinates
        if ($request->filled('lat') && $request->filled('lng')) {
            $request->validate([
                'lat' => ['required', 'numeric'],
                'lng' => ['required', 'numeric'],
            ]);

            $result = $this->deliveryService->calculateFee(
                (float) $request->lat,
                (float) $request->lng
            );

            return response()->json([
                'status' => 'success',
                'data'   => $result,
            ]);
        }

        // Option 2: by address_id
        if ($request->filled('address_id')) {
            $address = Address::where('_id', $request->address_id)
                ->where('user_id', (string) $request->user()->_id)
                ->first();

            if (!$address) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Alamat tidak ditemukan',
                ], 404);
            }

            $result = $this->deliveryService->calculateFeeFromAddress($address);

            return response()->json([
                'status' => 'success',
                'data'   => $result,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Harap berikan koordinat (lat, lng) atau address_id',
        ], 422);
    }

    /**
     * GET /api/delivery/zones — List available delivery zones
     */
    public function zones(): JsonResponse
    {
        $zones = DeliveryZone::active()->orderBy('max_distance', 'asc')->get()
            ->map(function ($zone) {
                return [
                    'id'             => (string) $zone->_id,
                    'name'           => $zone->name,
                    'base_fee'       => $zone->base_fee,
                    'per_km_fee'     => $zone->per_km_fee,
                    'max_distance'   => $zone->max_distance,
                    'min_order'      => $zone->min_order ?? 0,
                    'estimated_time' => $zone->estimated_time,
                    'formatted_fee'  => 'Rp ' . number_format($zone->base_fee, 0, ',', '.') . '+',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $zones,
        ]);
    }

    /**
     * GET /api/delivery/slots — Available delivery time slots
     */
    public function slots(): JsonResponse
    {
        $slots = $this->deliveryService->getAvailableSlots();

        return response()->json([
            'status' => 'success',
            'data'   => $slots,
        ]);
    }
}
