<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /**
     * GET /api/promos — List active promos (public)
     */
    public function index(): JsonResponse
    {
        $promos = Promotion::valid()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($promo) {
                return [
                    'id'            => (string) $promo->_id,
                    'code'          => $promo->code,
                    'name'          => $promo->name,
                    'description'   => $promo->description,
                    'type'          => $promo->type,
                    'display_value' => $promo->display_value,
                    'min_order'     => $promo->min_order ?? 0,
                    'start_date'    => $promo->start_date?->toIso8601String(),
                    'end_date'      => $promo->end_date?->toIso8601String(),
                    'image_url'     => $promo->image_url ? url('storage/' . $promo->image_url) : null,
                    'bg_color_start'=> $promo->bg_color_start,
                    'bg_color_end'  => $promo->bg_color_end,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $promos,
        ]);
    }

    /**
     * POST /api/promos/validate — Validate a promo code
     */
    public function validateCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'         => ['required', 'string'],
            'order_amount' => ['required', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $promo = Promotion::where('code', strtoupper($validated['code']))->first();

        if (!$promo) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode promo tidak ditemukan',
                'valid'   => false,
            ], 404);
        }

        if (!$promo->isValid($validated['order_amount'])) {
            $reasons = [];
            if (!$promo->is_active) $reasons[] = 'Promo tidak aktif';
            if ($promo->start_date && now()->lt($promo->start_date)) $reasons[] = 'Promo belum dimulai';
            if ($promo->end_date && now()->gt($promo->end_date)) $reasons[] = 'Promo sudah berakhir';
            if ($promo->usage_limit && $promo->used_count >= $promo->usage_limit) $reasons[] = 'Kuota promo habis';
            if ($promo->min_order && $validated['order_amount'] < $promo->min_order) {
                $reasons[] = 'Minimum order Rp ' . number_format($promo->min_order, 0, ',', '.');
            }

            return response()->json([
                'status'  => 'error',
                'message' => implode('. ', $reasons) ?: 'Kode promo tidak valid',
                'valid'   => false,
            ], 422);
        }

        $deliveryFee = (float) ($validated['delivery_fee'] ?? 0);
        $discount    = $promo->calculateDiscount($validated['order_amount'], $deliveryFee);

        return response()->json([
            'status'  => 'success',
            'valid'   => true,
            'message' => 'Kode promo valid!',
            'data'    => [
                'code'           => $promo->code,
                'name'           => $promo->name,
                'type'           => $promo->type,
                'display_value'  => $promo->display_value,
                'discount_amount'=> $discount,
                'formatted_discount' => 'Rp ' . number_format($discount, 0, ',', '.'),
                'final_amount'   => max(0, $validated['order_amount'] + $deliveryFee - $discount),
            ],
        ]);
    }

    /**
     * POST /api/promos — Admin create promo
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:30', 'unique:promotions,code'],
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'type'           => ['required', 'in:percentage,fixed,free_shipping'],
            'value'          => ['required', 'numeric', 'min:0'],
            'min_order'      => ['nullable', 'numeric', 'min:0'],
            'max_discount'   => ['nullable', 'numeric', 'min:0'],
            'usage_limit'    => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active'      => ['boolean'],
            'image'          => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        
        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('promotions', 'public');
        }

        $promo = Promotion::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Promo berhasil dibuat',
            'data'    => [
                'id'   => (string) $promo->_id,
                'code' => $promo->code,
                'name' => $promo->name,
            ],
        ], 201);
    }

    /**
     * PUT /api/promos/{id} — Admin update promo
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $promo = Promotion::findOrFail($id);

        $validated = $request->validate([
            'name'           => ['sometimes', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'type'           => ['sometimes', 'in:percentage,fixed,free_shipping'],
            'value'          => ['sometimes', 'numeric', 'min:0'],
            'min_order'      => ['nullable', 'numeric', 'min:0'],
            'max_discount'   => ['nullable', 'numeric', 'min:0'],
            'usage_limit'    => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date'],
            'is_active'      => ['boolean'],
            'image'          => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('promotions', 'public');
        }

        $promo->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Promo berhasil diupdate',
        ]);
    }

    /**
     * DELETE /api/promos/{id} — Admin delete promo
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $promo = Promotion::findOrFail($id);
        $promo->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Promo berhasil dihapus',
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengelola promo');
        }
    }
}
