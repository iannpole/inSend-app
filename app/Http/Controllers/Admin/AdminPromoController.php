<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPromoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Promotion::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $promotions = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.promotions.index', compact('promotions', 'search'));
    }

    public function create()
    {
        return view('admin.promotions.form');
    }

    public function store(Request $request)
    {
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
            'bg_color_start' => ['nullable', 'string', 'max:10'],
            'bg_color_end'   => ['nullable', 'string', 'max:10'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion = Promotion::create($validated);

        // --- Trigger Firebase Push Notification ---
        $this->sendFirebaseNotification($promotion);

        return redirect()->route('admin.promotions.index')->with('success', 'Promosi berhasil ditambahkan!');
    }

    /**
     * Script Dasar Firebase Cloud Messaging (Pengirim Notifikasi)
     */
    private function sendFirebaseNotification(Promotion $promo)
    {
        // 1. Dapatkan Server Key dari Firebase Console -> Project Settings -> Cloud Messaging (Legacy)
        // Atau untuk jangka panjang bisa install package "kreait/firebase-php" untuk FCM HTTP v1.
        $serverKey = env('FCM_SERVER_KEY');
        
        if (empty($serverKey)) {
            \Illuminate\Support\Facades\Log::info('FCM_SERVER_KEY kosong. Skip kirim notifikasi.');
            return;
        }

        // 2. Tentukan Topik (Kirim ke semua user yang subscribe ke topik "promotions")
        $topic = '/topics/promotions';

        // 3. Siapkan Teks Notifikasi (Copywriting AIDA)
        $title = "🎉 Promo Baru: {$promo->name}!";
        $body  = "Gunakan kode {$promo->code} untuk diskon. Yuk belanja sekarang sebelum kehabisan!";

        // 4. Siapkan Payload Data (Agar bisa dibuka via Deep Linking di Flutter)
        $data = [
            'type'     => 'promotion',
            'promo_id' => (string) $promo->_id,
            'code'     => $promo->code,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK' // Standard Flutter FCM action
        ];

        try {
            \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to'           => $topic,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Send Error: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(Request $request, string $id)
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:30', 'unique:promotions,code,'.$id.',_id'],
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'type'           => ['required', 'in:percentage,fixed,free_shipping'],
            'value'          => ['required', 'numeric', 'min:0'],
            'min_order'      => ['nullable', 'numeric', 'min:0'],
            'max_discount'   => ['nullable', 'numeric', 'min:0'],
            'usage_limit'    => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'start_date'     => ['nullable', 'date'],
            'end_date'       => ['nullable', 'date'],
            'is_active'      => ['boolean'],
            'image'          => ['nullable', 'image', 'max:2048'],
            'bg_color_start' => ['nullable', 'string', 'max:10'],
            'bg_color_end'   => ['nullable', 'string', 'max:10'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($promotion->image_url) {
                Storage::disk('public')->delete($promotion->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update($validated);
        return redirect()->route('admin.promotions.index')->with('success', 'Promosi berhasil diupdate!');
    }

    public function destroy(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        if ($promotion->image_url) {
            Storage::disk('public')->delete($promotion->image_url);
        }
        $promotion->delete();
        
        return redirect()->route('admin.promotions.index')->with('success', 'Promosi berhasil dihapus!');
    }
}
