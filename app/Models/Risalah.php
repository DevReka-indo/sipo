<?php

namespace App\Models;

use App\Http\Resources\UndanganResource;
use App\Http\Controllers\CetakPDFController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Risalah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'risalah';
    protected $primaryKey = 'id_risalah';
    public $timestamps = true;

    protected $fillable = [
        'tgl_dibuat',
        'tgl_disahkan',
        'seri_surat',
        'kode',
        'nomor_risalah',
        'agenda',
        'tempat',
        'waktu_mulai',
        'status',
        'waktu_selesai',
        'tujuan',
        'judul',
        'pembuat',
        'topik',
        'pembahasan',
        'tindak_lanjut',
        'target',
        'pic',
        'lampiran',
        'catatan',
        'nama_pemimpin_acara',
        'nama_notulis_acara',
        'qr_pemimpin_acara',
        'qr_notulis_acara',
        'with_undangan',
        'tujuan',
        'kode_bagian'
    ];

    protected $casts = [
        'tgl_dibuat' => 'datetime',
        'tgl_disahkan' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // Relasi ke tabel RisalahDetail
    public function risalahDetails()
    {
        return $this->hasMany(RisalahDetail::class, 'risalah_id_risalah', 'id_risalah');
    }

    public function kirimDocument()
    {
        return $this->hasMany(Kirim_Document::class, 'id_document');
    }

    public function arsip()
    {
        return $this->morphMany(Arsip::class, 'document');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'pembuat');
    }

    public function up()
    {
        Schema::table('risalah', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('risalah', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    public function tujuanString()
    {
        $pdfController = new CetakPDFController();
        try {
            $tujuan = Undangan::where('judul', $this->judul)->get()->first()->tujuan;
            $idArray = explode(';', $tujuan);
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
        } catch (\Exception $e) {
            return null; // or handle the exception as needed
        }
    }
}
