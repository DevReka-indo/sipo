<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Http\Controllers\Api\NotifApiController;
use Illuminate\Support\Facades\Log;

class NotifService
{
    public function createAndPush(int $userId, string $judul, ?string $judulDocument = null, ?int $idDocument = null, ?string $jenisDocument = 'memo'): Notifikasi
    {
        Log::info('createAndPush DIPANGGIL', [
            'user_id' => $userId,
            'title' => $judul,
            'body' => $judulDocument ?? 'Ada pembaruan dokumen',
            'document_id' => $idDocument,
            'type' => $jenisDocument,
        ]);

        // Simpan / update notifikasi DB (tetap backward-compatible untuk pemanggilan lama)
        $notif = Notifikasi::updateOrCreate(
            [
                'id_user' => $userId,
                'judul' => $judul,
                'judul_document' => $judulDocument,
                'id_document' => $idDocument,
                'jenis_document' => $jenisDocument,
            ],
            [
                'dibaca' => 0,
                'updated_at' => now(),
            ],
        );

        // Push notification
        // $push = app(NotifApiController::class);

        // $push->sendToUser($userId, $judul, $judulDocument ?? 'Ada pembaruan dokumen');

        return $notif;
    }
}
