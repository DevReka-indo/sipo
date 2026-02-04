<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\{Memo, Risalah, Undangan};

class NotifResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tipe = match (true) {
            str_contains($this->judul, 'Memo') => 'memo',
            str_contains($this->judul, 'Risalah') => 'risalah',
            str_contains($this->judul, 'Undangan') => 'undangan',
            default => 'unknown',
        };

        $id_document = match ($tipe) {
            'memo' => Memo::where('judul', $this->judul_document)->value('id_memo'),
            'risalah' => Risalah::where('judul', $this->judul_document)->value('id_risalah'),
            'undangan' => Undangan::where('judul', $this->judul_document)->value('id_undangan'),
            default => null,
        };


        return [
            'id_notifikasi' => $this->id_notifikasi,
            'judul' => $this->judul,
            'judul_document' => $this->judul_document,
            'tipe_document' => $tipe,
            'dibaca' => (bool)$this->dibaca,
            'id_document' => $id_document,
            'id_user' => $this->id_user,
            'updated_at' => $this->updated_at,
        ];
    }
}
