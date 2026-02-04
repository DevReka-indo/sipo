<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CetakPDFController;

class Undangan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'undangan';
    protected $primaryKey = 'id_undangan';
    public $timestamps = true;

    protected $fillable = [
        'judul',
        'kepada',
        'tujuan',
        'isi_undangan',
        'tgl_dibuat',
        'tgl_disahkan',
        'qr_approved_by',
        'status',
        'pembuat',
        'catatan',
        'lampiran',
        'nomor_undangan',
        'nama_bertandatangan',
        'kode',
        'seri_surat',
        'tgl_rapat',
        'tempat',
        'waktu_mulai',
        'waktu_selesai',
        'kode_bagian'
    ];

    protected $casts = [
        'tgl_dibuat' => 'datetime',
        'tgl_disahkan' => 'datetime',
    ];

    /**
     * Get the division associated with the document.
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }
    public function arsip()
    {
        return $this->morphMany(Arsip::class, 'document');
    }
    public function kirimDocument()
    {
        return $this->hasMany(Kirim_Document::class, 'id_document', 'id_undangan');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'pembuat', 'id');
    }
    public function tujuanString()
    {
        $pdfController = new CetakPDFController();

        $idArray = explode(';', $this->tujuan);
        $listNama = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
            ->whereIn('id', $idArray)
            ->get()
            ->map(function ($user, $key) use ($pdfController) {
                $level = $pdfController->detectLevel($user);
                $user->level_kerja = $level;
                $user->bagian_text = $pdfController->getBagianText($user, $level);
                return $user;
            })
            ->sortBy(function ($user) {
                return optional($user->position)->id_position;
            })
            ->values();

        $tujuanNames = $listNama->map(function ($user, $index) {
            return $user->position->nm_position . ' '
                . $user->bagian_text . ' '
                . '(' . $user->firstname . ' ' . $user->lastname . ')';
        });

        return $tujuanNames;
    }
}
