<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
class SeriRisalah extends Model
{
    use HasFactory;

    protected $table = 'seri_berkas_risalah';

    protected $primaryKey = 'id_seri';

    protected $fillable = ['kode', 'bulan', 'tahun', 'seri_bulanan', 'seri_tahunan'];

    public static function getNextSeri($save = false)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $idUser = Auth::user();
        $user = User::where('id', $idUser->id)->first();

        if ($user->department_id_department != NULL) {
            $divisiId = Department::where('id_department', $user->department_id_department)->first();
            if ($divisiId->kode_department != NULL) {
                $divisiId = $divisiId->kode_department;
            } else if ($divisiId->kode_department == NULL) {
                if ($user->divisi_id_divisi == NULL) {
                    $divisiId = $divisiId->name_department;
                } else {
                    $divisiId = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
                    if ($divisiId->kode_divisi != NULL) {
                        $divisiId = $divisiId->kode_divisi;
                    } else if ($divisiId->kode_divisi == NULL) {
                        $divisiId = $divisiId->nm_divisi;
                    }
                }
            }
        } else if ($user->divisi_id_divisi != NULL) {
            $divisiId = Divisi::where('id_divisi', $user->divisi_id_divisi)->first();
            if ($divisiId->kode_divisi != NULL) {
                $divisiId = $divisiId->kode_divisi;
            } else if ($divisiId->kode_divisi == NULL) {
                $divisiId = $divisiId->nm_divisi;
            }
        } else if ($user->director_id_director != NULL) {
            $divisiId = Director::where('id_director', $user->director_id_director)->first();
            $divisiId = $divisiId->kode_director;
        }

        // Cek apakah ada risalah untuk divisi ini
        $Risalah = DB::table('risalah')
            ->where('kode', $divisiId)
            ->count();


        if ($Risalah === 0) {
            // Jika tidak ada memo, reset seri bulanan dan tahunan ke 1
            $seriBulanan = 1;
            $seriTahunan = 1;
        } else {
            // Ambil nomor seri terakhir berdasarkan tahun & divisi
            $lastSeri = self::where('kode', $divisiId)
                ->where('tahun', $currentYear)
                ->latest('id_seri')
                ->first();

            if (!$lastSeri) {
                // Jika tidak ada data sebelumnya, buat nomor seri pertama
                $seriBulanan = 1;
                $seriTahunan = 1;
            } else {
                // Reset bulanan jika bulan berubah
                if ($lastSeri->bulan != $currentMonth) {
                    $seriBulanan = 1;
                } else {
                    $seriBulanan = $lastSeri->seri_bulanan + 1;
                }

                // Reset tahunan jika tahun berubah
                if ($lastSeri->tahun != $currentYear) {
                    $seriTahunan = 1;
                } else {
                    $seriTahunan = $lastSeri->seri_tahunan + 1;
                }
            }
        }

        if (!$save) {
            return [
                'seri_bulanan' => $seriBulanan,
                'seri_tahunan' => $seriTahunan
            ];
        }

        if ($save) {
            // Simpan ke database hanya jika parameter $save = true
            $newSeri = self::create([
                'kode' => $divisiId,
                'bulan' => $currentMonth,
                'tahun' => $currentYear,
                'seri_bulanan' => $seriBulanan,
                'seri_tahunan' => $seriTahunan,
            ]);

            return [
                'seri_bulanan' => $newSeri->seri_bulanan,
                'seri_tahunan' => $newSeri->seri_tahunan
            ];
        }

        return [
            'seri_bulanan' => $seriBulanan,
            'seri_tahunan' => $seriTahunan
        ];
    }
}
