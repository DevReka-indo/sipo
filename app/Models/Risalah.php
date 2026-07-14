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
        'pemimpin_acara_user_id',
        'nama_notulis_acara',
        'notulis_acara_user_id',
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
        return $this->hasMany(Kirim_Document::class, 'id_document', 'id_risalah')
            ->where('jenis_document', 'risalah');
    }
    // public function kirimDocument()
    // {
    //     return $this->hasMany(Kirim_Document::class, 'id_document');
    // }

    public function arsip()
    {
        return $this->morphMany(Arsip::class, 'document');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'pembuat');
    }

    // public function up()
    // {
    //     Schema::table('risalah', function (Blueprint $table) {
    //         $table->softDeletes();
    //     });
    // }

    // public function down()
    // {
    //     Schema::table('risalah', function (Blueprint $table) {
    //         $table->dropSoftDeletes();
    //     });
    // }

    // public function tujuanString()
    // {
    //     $pdfController = new CetakPDFController();
    //     try {
    //         $tujuan = Undangan::where('judul', $this->judul)->get()->first()->tujuan;
    //         $idArray = explode(';', $tujuan);
    //         $listNama = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
    //             ->whereIn('id', $idArray)
    //             ->get()
    //             ->map(function ($user, $key) use ($pdfController) {
    //                 $level = $pdfController->detectLevel($user);
    //                 $user->level_kerja = $level;
    //                 $user->bagian_text = $pdfController->getBagianText($user, $level);
    //                 return $user;
    //             })
    //             ->sortBy(function ($user) {
    //                 return optional($user->position)->id_position;
    //             })
    //             ->values();

    //         $tujuanNames = $listNama->map(function ($user, $index) {
    //             return $user->position->nm_position . ' '
    //                 . $user->bagian_text . ' '
    //                 . '(' . $user->firstname . ' ' . $user->lastname . ')';
    //         });

    //         return $tujuanNames;
    //     } catch (\Exception $e) {
    //         return null; // or handle the exception as needed
    //     }
    // }

    public function tujuanString()
    {
        $pdfController = new CetakPDFController();

        try {
            $tujuan = null;

            if ($this->with_undangan) {
                $undangan = Undangan::where('judul', $this->judul)->first();
                $tujuan = $undangan?->tujuan;
            }

            if (empty($tujuan)) {
                $tujuan = $this->tujuan;
            }

            if (empty($tujuan)) {
                return collect();
            }

            $idArray = collect(explode(';', $tujuan))
                ->map(fn ($id) => trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($idArray)) {
                return collect();
            }

            $listNama = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereIn('id', $idArray)
                ->get()
                ->map(function ($user) use ($pdfController) {
                    $level = $pdfController->detectLevel($user);
                    $user->level_kerja = $level;
                    $user->bagian_text = $pdfController->getBagianText($user, $level);

                    return $user;
                })
                ->sortBy(function ($user) {
                    return optional($user->position)->id_position;
                })
                ->values();

            return $listNama->map(function ($user, $index) {
                $position = optional($user->position)->nm_position ?? '-';
                $bagian = $user->bagian_text ?? '-';
                $nama = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));

                return ($index + 1) . '. ' . $position . ' ' . $bagian . ' (' . $nama . ')';
            });
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function pemimpinAcara()
    {
        return $this->belongsTo(User::class, 'pemimpin_acara_user_id');
    }

    public function notulisAcara()
    {
        return $this->belongsTo(User::class, 'notulis_acara_user_id');
    }

    public function pembuatUser()
    {
        return $this->belongsTo(User::class, 'pembuat');
    }
}
