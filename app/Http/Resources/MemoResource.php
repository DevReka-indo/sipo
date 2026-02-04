<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class MemoResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id_memo' => $this->id_memo,
            'judul' => $this->judul,
            'isi_memo' => $this->isi_memo,
            'status' => $this->status,
            'nomor_memo' => $this->nomor_memo,
            'tujuan_string' => explode(';', $this->tujuan_string),
            'nama_bertandatangan' => $this->nama_bertandatangan,
            'pembuat' => $this->pembuat,
            'nama_pembuat'   => optional($this->user)->fullname,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'lampiran_url' => $this->lampiran ? url('/api/memos/' . $this->id_memo . '/lampiran') : null,
            'kode' => $this->kode,
            'catatan' => $this->catatan,
        ];
    }
}
