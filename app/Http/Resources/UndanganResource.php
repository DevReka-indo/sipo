<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class UndanganResource extends JsonResource
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
            'id_undangan' => $this->id_undangan,
            'judul' => $this->judul,
            'isi_undangan' => $this->isi_undangan,
            'status' => $this->status,
            'nomor_undangan' => $this->nomor_undangan,
            'tujuan_string' => $this->tujuanString(),
            'nama_bertandatangan' => $this->nama_bertandatangan,
            'pembuat' => $this->pembuat,
            'nama_pembuat'   => optional($this->user)->fullname,
            'tgl_rapat' => $this->tgl_rapat,
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'tempat' => $this->tempat,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'lampiran_url' => $this->lampiran ? url('/api/undangans/' . $this->id_undangan . '/lampiran') : null,
            'pdf_url' => route('view-undanganPDF', $this->id_undangan),
            'kode' => $this->kode,
            'catatan' => $this->catatan,
        ];
    }
}
