<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class RisalahResource extends JsonResource
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
            'id_risalah' => $this->id_risalah,
            'judul' => $this->judul,
            'agenda' => $this->agenda,
            'status' => $this->status,
            'nomor_risalah' => $this->nomor_risalah,
            'risalah_details' => $this->risalahDetails,
            'tgl_dibuat' => $this->tgl_dibuat,

            // 'pembuat' => $this->pembuat,

            //detail risalah
            'nama_bertandatangan' => $this->nama_bertandatangan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'lampiran_url' => $this->lampiran ? url('/api/risalahs/' . $this->id_risalah . '/lampiran') : null,
            'kode' => $this->kode_bagian,
            'catatan' => $this->catatan,
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'tempat' => $this->tempat,

            //people who terlibat
            'pembuat'   => optional($this->user)->fullname,
            'pemimpin_acara' => $this->nama_pemimpin_acara,
            'notulis' => $this->nama_notulis_acara,
            'tujuan_string' => $this->whenNotNull($this->tujuanString()), // ← add this
        ];
    }
}
