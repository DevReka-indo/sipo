<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Notifikasi, NotifTokenModel};
use App\Http\Resources\NotifResource;
use Illuminate\Support\Facades\Http;
use SebastianBergmann\Type\FalseType;
use Illuminate\Support\Facades\Log;

class NotifApiController extends Controller
{
    // Ambil 10 notifikasi terakhir untuk user yang login
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Ambil notifikasi berdasarkan divisi user
        $notifications = Notifikasi::where('id_user', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Gunakan MemoResource untuk transformasi tiap notifikasi
        $notificationsFilter = collect(NotifResource::collection($notifications)->resolve())
            ->filter(function ($notif) {
                return $notif['id_document'] !== null;
            })
            ->values();
        [$unreadNotifications, $readNotifications] = $notificationsFilter->partition(function ($notif) {
            return $notif['dibaca'] === false;
        });

        return response()->json([
            'status' => true,
            'message' => 'Daftar 10 notifikasi terbaru',
            'data' => [
                'unread' => $unreadNotifications->values(),
                'read' => $readNotifications->values(),
            ]
        ]);
    }

    // Hitung jumlah notifikasi yang belum dibaca
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'count' => 0
            ], 401);
        }

        $count = Notifikasi::where('id_user', $user->id)
            ->where('dibaca', 0)
            ->count();

        return response()->json([
            'status' => true,
            'count' => $count
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $notification = Notifikasi::where('id_notifikasi', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        if ($notification->dibaca == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Notifikasi sudah ditandai sebagai dibaca'
            ]);
        }
        $notification->dibaca = 1;
        $notification->save();

        return response()->json([
            'status' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca'
        ]);
    }

    // Tandai semua notifikasi sebagai sudah dibaca
    public function markAllAsRead()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $updated = Notifikasi::where('id_user', $user->id)
            ->where('dibaca', 0)
            ->update(['dibaca' => 1]);

        return response()->json([
            'status' => true,
            'message' => "Semua notifikasi ($updated) ditandai sudah dibaca"
        ]);
    }

    public function saveToken(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'token' => 'required|string',
        ]);

        // Simpan token notifikasi untuk user yang login
        $notifToken = \App\Models\NotifTokenModel::updateOrCreate(
            ['id_user' => $user->id],
            ['token' => $request->token]
        );

        return response()->json([
            'status' => true,
            'message' => 'Token notifikasi berhasil disimpan',
            'data' => $notifToken
        ]);
    }

    public function sendToUser($id_user, $title, $body)
    {

        // Get token from your DB
        $tokenData = NotifTokenModel::where('id_user', $id_user)->first();

        if (!$tokenData) {
            return response()->json([
                'status' => false,
                'message' => 'Token not found for this user.'
            ], 404);
        }

        $expoUrl = 'https://exp.host/--/api/v2/push/send';

        // Send push notification via Expo API
        $response = Http::post($expoUrl, [
            'to' => $tokenData->token,
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'priority' => 'high',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Push notification sent successfully.',
            'expo_response' => $response->json()
        ]);
    }

    public function tesNotif()
    {
        // Misal kita tes untuk user id 1
        $id_user = 38;

        // Ambil token dari database
        $tokenData = NotifTokenModel::where('id_user', $id_user)->first();

        if (!$tokenData) {
            return response()->json([
                'status' => false,
                'message' => '❌ Token not found for this user.'
            ], 404);
        }

        // $expoUrl = 'https://exp.host/--/api/v2/push/send';
        $expoUrl = 'https://fcm.googleapis.com/fcm/send';

        // Data notifikasi
        $data = [
            'to' => $tokenData->token,
            'title' => '🔔 Tes Notifikasi',
            'body' => 'Halo Master, ini pesan tes dari backend Laravel!',
            'sound' => 'default',
            'priority' => 'high',
        ];

        // Kirim request ke Expo Push Service
        $response = Http::post($expoUrl, $data);

        // Simpan log untuk debugging
        Log::info('📬 Tes Notifikasi Dikirim', [
            'user_id' => $id_user,
            'token' => $tokenData->token,
            'expo_response' => $response->json(),
        ]);

        return response()->json([
            'status' => true,
            'message' => '✅ Tes notifikasi terkirim!',
            'sent_to' => $tokenData->token,
            'expo_response' => $response->json()
        ]);
    }
}
