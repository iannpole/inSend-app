<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /api/users — Admin only: list semua user
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $users = User::paginate(15);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'total'        => $users->total(),
                'per_page'     => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/users/{id} — Admin atau user sendiri
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!$request->user()->isAdmin() && (string) $request->user()->_id !== $id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(['data' => new UserResource($user)]);
    }

    /**
     * PUT /api/users/{id} — Update profil (user sendiri atau admin)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!$request->user()->isAdmin() && (string) $request->user()->_id !== $id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name'         => ['sometimes', 'string', 'max:100'],
            'phone'        => ['sometimes', 'nullable', 'string', 'max:20'],
            'address'      => ['sometimes', 'nullable', 'string', 'max:500'],
            'password'     => ['sometimes', 'string', 'min:8', 'confirmed'],
            'avatar'       => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Admin bisa ubah role
        if ($request->user()->isAdmin() && $request->has('role')) {
            $validated['role'] = $request->input('role');
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diupdate',
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * DELETE /api/users/{id} — Admin only
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $user = User::findOrFail($id);

        if ((string) $user->_id === (string) $request->user()->_id) {
            return response()->json(['message' => 'Tidak bisa hapus akun sendiri'], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }

    private function authorizeAdmin(Request $request): void
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa akses ini');
        }
    }
}
