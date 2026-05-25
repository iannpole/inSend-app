<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * GET /api/addresses
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->_id;

        $addresses = Address::byUser($userId)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => AddressResource::collection($addresses),
        ]);
    }

    /**
     * POST /api/addresses
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label'          => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:20'],
            'street'         => ['required', 'string', 'max:500'],
            'city'           => ['required', 'string', 'max:100'],
            'province'       => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'string', 'max:10'],
            'district'       => ['nullable', 'string', 'max:100'],
            'lat'            => ['nullable', 'numeric'],
            'lng'            => ['nullable', 'numeric'],
            'is_default'     => ['boolean'],
            'notes'          => ['nullable', 'string', 'max:200'],
        ]);

        $userId = (string) $request->user()->_id;
        $validated['user_id'] = $userId;

        // If setting as default, unset other defaults
        if (!empty($validated['is_default'])) {
            Address::byUser($userId)->update(['is_default' => false]);
        }

        // If this is the first address, auto-set as default
        $existingCount = Address::byUser($userId)->count();
        if ($existingCount === 0) {
            $validated['is_default'] = true;
        }

        $address = Address::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat berhasil ditambahkan',
            'data'    => new AddressResource($address),
        ], 201);
    }

    /**
     * GET /api/addresses/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $address = Address::findOrFail($id);

        if ((string) $address->user_id !== (string) $request->user()->_id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new AddressResource($address),
        ]);
    }

    /**
     * PUT /api/addresses/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $address = Address::findOrFail($id);

        if ((string) $address->user_id !== (string) $request->user()->_id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'label'          => ['sometimes', 'string', 'max:50'],
            'recipient_name' => ['sometimes', 'string', 'max:100'],
            'phone'          => ['sometimes', 'string', 'max:20'],
            'street'         => ['sometimes', 'string', 'max:500'],
            'city'           => ['sometimes', 'string', 'max:100'],
            'province'       => ['sometimes', 'string', 'max:100'],
            'postal_code'    => ['sometimes', 'string', 'max:10'],
            'district'       => ['nullable', 'string', 'max:100'],
            'lat'            => ['nullable', 'numeric'],
            'lng'            => ['nullable', 'numeric'],
            'is_default'     => ['boolean'],
            'notes'          => ['nullable', 'string', 'max:200'],
        ]);

        // If setting as default, unset other defaults
        if (!empty($validated['is_default'])) {
            $userId = (string) $request->user()->_id;
            Address::byUser($userId)
                ->where('_id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat berhasil diupdate',
            'data'    => new AddressResource($address),
        ]);
    }

    /**
     * DELETE /api/addresses/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $address = Address::findOrFail($id);

        if ((string) $address->user_id !== (string) $request->user()->_id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted the default, assign new default
        if ($wasDefault) {
            $userId = (string) $request->user()->_id;
            $next = Address::byUser($userId)->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat berhasil dihapus',
        ]);
    }

    /**
     * PATCH /api/addresses/{id}/default — Set as default address
     */
    public function setDefault(Request $request, string $id): JsonResponse
    {
        $address = Address::findOrFail($id);

        if ((string) $address->user_id !== (string) $request->user()->_id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $userId = (string) $request->user()->_id;
        Address::byUser($userId)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat default berhasil diubah',
            'data'    => new AddressResource($address),
        ]);
    }
}
