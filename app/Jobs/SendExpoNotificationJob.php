<?php

namespace App\Jobs;

use App\Models\Notifikasi;
use App\Models\NotifTokenModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendExpoNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $notifikasiId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $notifikasiId)
    {
        $this->notifikasiId = $notifikasiId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notif = Notifikasi::where(
            'id_notifikasi',
            $this->notifikasiId
        )->first();

        if (!$notif) {

            Log::warning('Notifikasi tidak ditemukan', [
                'id_notifikasi' => $this->notifikasiId,
            ]);

            return;
        }

        // Ambil semua token user
        $tokens = NotifTokenModel::where('id_user', $notif->id_user)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {

            Log::info('User tidak memiliki token notifikasi', [
                'id_user' => $notif->id_user,
            ]);

            return;
        }

        foreach ($tokens as $token) {

            // Validasi format token expo
            if (
                !str_starts_with($token, 'ExponentPushToken') &&
                !str_starts_with($token, 'ExpoPushToken')
            ) {

                Log::warning('Token expo tidak valid', [
                    'token' => $token,
                ]);

                continue;
            }

            try {

                $payload = [
                    'to' => $token,
                    'sound' => 'default',

                    // TITLE
                    'title' => $notif->judul ?? 'Notifikasi Baru',

                    // BODY
                    'body' => $notif->judul_document
                        ?? 'Ada notifikasi baru',

                    'priority' => 'high',

                    'data' => [
                        'id_notifikasi' => $notif->id_notifikasi,
                        'id_document' => $notif->id_document,
                        'jenis_document' => $notif->jenis_document,
                    ],
                ];

                $response = Http::timeout(15)
                    ->acceptJson()
                    ->post(
                        'https://exp.host/--/api/v2/push/send',
                        $payload
                    );

                $responseData = $response->json();

                // Logging response expo
                Log::info('Expo push response', [
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);

                // Kalau gagal HTTP
                if (!$response->successful()) {

                    Log::error('HTTP Expo push gagal', [
                        'token' => $token,
                        'response' => $responseData,
                    ]);

                    continue;
                }

                // Cek response error dari expo
                if (
                    isset($responseData['data']['status']) &&
                    $responseData['data']['status'] === 'error'
                ) {

                    $details = $responseData['data']['details'] ?? [];

                    Log::error('Expo push error', [
                        'token' => $token,
                        'response' => $responseData,
                    ]);

                    // Hapus token invalid
                    if (
                        isset($details['error']) &&
                        $details['error'] === 'DeviceNotRegistered'
                    ) {

                        NotifTokenModel::where('token', $token)
                            ->delete();

                        Log::warning('Token expo dihapus karena invalid', [
                            'token' => $token,
                        ]);
                    }
                }

            } catch (\Throwable $e) {

                Log::error('Gagal kirim push notification', [
                    'id_notifikasi' => $this->notifikasiId,
                    'token' => $token,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
