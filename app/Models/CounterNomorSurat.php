<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Model CounterNomorSurat
 *
 * Model untuk mengelola counter nomor surat dengan fitur:
 * - Counter independen per divisi
 * - Auto-generate nomor surat
 * - Tracking seri per tahun dan per bulan
 * - Foreign key relationship dengan BagianKerja
 *
 * @property int $id
 * @property string $tanggal_permintaan
 * @property string $seri_tahun Nomor urut dalam tahun (01, 02, 03...)
 * @property string $seri_bulan Nomor urut dalam bulan (01, 02, 03...)
 * @property string $perusahaan
 * @property string $kode_tipe_surat
 * @property string $divisi Foreign key ke bagian_kerja.kode_bagian
 * @property string $bulan
 * @property int $tahun
 * @property string $pic_peminta
 * @property string $jenis
 * @property string $perihal
 * @property string|null $nomor_surat_generated
 * @property bool $is_used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CounterNomorSurat extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'counter_nomor_surat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tanggal_permintaan',
        'seri_tahun',
        'seri_bulan',
        'perusahaan',
        'kode_tipe_surat',
        'divisi',
        'bulan',
        'tahun',
        'pic_peminta',
        'jenis',
        'perihal',
        'nomor_surat_generated',
        'is_used',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_permintaan' => 'date',
        'tahun' => 'integer',
        'is_used' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel bagian_kerja
     * Counter nomor surat belongs to bagian kerja
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bagianKerja()
    {
        return $this->belongsTo(BagianKerja::class, 'divisi', 'kode_bagian');
    }

    /**
     * Scope untuk filter berdasarkan tahun
     *
     * @param Builder $query
     * @param int $tahun
     * @return Builder
     */
    public function scopeByTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope untuk filter berdasarkan bulan
     *
     * @param Builder $query
     * @param string $bulan
     * @return Builder
     */
    public function scopeByBulan(Builder $query, string $bulan): Builder
    {
        return $query->where('bulan', $bulan);
    }

    /**
     * Scope untuk filter berdasarkan jenis
     *
     * @param Builder $query
     * @param string $jenis
     * @return Builder
     */
    public function scopeByJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis', $jenis);
    }

    /**
     * Scope untuk filter berdasarkan divisi
     *
     * @param Builder $query
     * @param string $divisi
     * @return Builder
     */
    public function scopeByDivisi(Builder $query, string $divisi): Builder
    {
        return $query->where('divisi', $divisi);
    }

    /**
     * Scope untuk nomor surat yang sudah digunakan
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUsed(Builder $query): Builder
    {
        return $query->where('is_used', true);
    }

    /**
     * Scope untuk nomor surat yang belum digunakan
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnused(Builder $query): Builder
    {
        return $query->where('is_used', false);
    }

    /**
     * Get nomor seri terakhir untuk tahun berjalan per divisi
     * Setiap divisi memiliki counter independen
     *
     * @param int $tahun
     * @param string $kodeTipeSurat
     * @param string $divisi
     * @return int
     */
    public static function getLastSeriTahun(int $tahun, string $kodeTipeSurat, string $divisi): int
    {
        $last = static::where('tahun', $tahun)
            ->where('kode_tipe_surat', $kodeTipeSurat)
            ->where('divisi', $divisi) // Counter per divisi
            ->orderBy('seri_tahun', 'desc')
            ->first();

        return $last ? (int) $last->seri_tahun : 0;
    }

    /**
     * Get nomor seri terakhir untuk bulan berjalan per divisi
     * Setiap divisi memiliki counter independen
     *
     * @param int $tahun
     * @param string $bulan
     * @param string $kodeTipeSurat
     * @param string $divisi
     * @return int
     */
    public static function getLastSeriBulan(int $tahun, string $bulan, string $kodeTipeSurat, string $divisi): int
    {
        $last = static::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('kode_tipe_surat', $kodeTipeSurat)
            ->where('divisi', $divisi) // Counter per divisi
            ->orderBy('seri_bulan', 'desc')
            ->first();

        return $last ? (int) $last->seri_bulan : 0;
    }

    /**
     * Generate nomor surat otomatis
     * Format: [Seri Tahun].[Seri Bulan]/[Perusahaan]/[Kode Tipe]/[Divisi]/[Bulan]/[Tahun]
     * Contoh: 100.01/REKA/GEN/TI/I/2026
     *         └┬┘ └┬┘
     *          │   └─ Seri Bulan (nomor urut dalam bulan)
     *          └───── Seri Tahun (nomor urut dalam tahun)
     *
     * @param array $data
     * @return string
     */
    public static function generateNomorSurat(array $data): string
    {
        $seriBulan = str_pad($data['seri_bulan'], 2, '0', STR_PAD_LEFT);
        $seriTahun = str_pad($data['seri_tahun'], 2, '0', STR_PAD_LEFT);
        $perusahaan = $data['perusahaan'] ?? 'REKA';
        $kode = $data['kode_tipe_surat'] ?? 'GEN';
        $divisi = $data['divisi'];
        $bulan = $data['bulan'];
        $tahun = $data['tahun'];

        // ✅ BENAR - Tahun.Bulan
        return "{$seriTahun}.{$seriBulan}/{$perusahaan}/{$kode}/{$divisi}/{$bulan}/{$tahun}";
        //          └─ Tahun       └─ Bulan
    }

    /**
     * Generate dan simpan nomor surat baru
     * Counter independen per divisi
     *
     * Format nomor surat: [Seri Bulan].[Seri Tahun]/[Perusahaan]/[Kode Tipe]/[Divisi]/[Bulan]/[Tahun]
     * Contoh: 01.100/REKA/GEN/TI/I/2026
     *
     * Contoh penggunaan:
     * $counter = CounterNomorSurat::createNomorSurat([
     *     'tanggal_permintaan' => now(),
     *     'perusahaan' => 'REKA',        // Optional, default: REKA
     *     'kode_tipe_surat' => 'GEN',    // Optional, default: GEN
     *     'divisi' => 'TI',
     *     'bulan' => 'I',
     *     'tahun' => 2026,
     *     'pic_peminta' => 'John Doe',
     *     'jenis' => 'Memo',
     *     'perihal' => 'Permohonan Laptop',
     * ]);
     *
     * @param array $data
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function createNomorSurat(array $data): self
    {
        // Validasi divisi harus ada
        if (!isset($data['divisi'])) {
            throw new \InvalidArgumentException('Divisi harus diisi');
        }

        // Set default values
        if (!isset($data['perusahaan'])) {
            $data['perusahaan'] = 'REKA';
        }
        if (!isset($data['kode_tipe_surat'])) {
            $data['kode_tipe_surat'] = 'GEN';
        }

        // Get next seri untuk divisi ini
        $lastSeriTahun = static::getLastSeriTahun(
            $data['tahun'],
            $data['kode_tipe_surat'],
            $data['divisi'] // Counter per divisi
        );

        $lastSeriBulan = static::getLastSeriBulan(
            $data['tahun'],
            $data['bulan'],
            $data['kode_tipe_surat'],
            $data['divisi'] // Counter per divisi
        );

        $data['seri_tahun'] = str_pad($lastSeriTahun + 1, 2, '0', STR_PAD_LEFT);
        $data['seri_bulan'] = str_pad($lastSeriBulan + 1, 2, '0', STR_PAD_LEFT);

        // Generate nomor surat
        $data['nomor_surat_generated'] = static::generateNomorSurat($data);

        return static::create($data);
    }

    /**
     * Get konversi bulan ke romawi
     *
     * @param int $bulan Bulan dalam angka (1-12)
     * @return string Bulan dalam romawi (I-XII)
     */
    public static function getBulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        return $romawi[$bulan] ?? 'I';
    }

    /**
     * Get list jenis dokumen yang tersedia
     *
     * @return array
     */
    public static function getJenisList(): array
    {
        return static::select('jenis')
            ->distinct()
            ->whereNotNull('jenis')
            ->pluck('jenis')
            ->toArray();
    }

    /**
     * Get statistik counter per bulan
     *
     * @param int $tahun
     * @return array
     */
    public static function getStatistikPerBulan(int $tahun): array
    {
        return static::where('tahun', $tahun)
            ->selectRaw('bulan, jenis, COUNT(*) as total')
            ->groupBy('bulan', 'jenis')
            ->orderBy('bulan')
            ->get()
            ->toArray();
    }

    /**
     * Get counter status per divisi
     * Menampilkan seri terakhir setiap divisi
     *
     * @param int $tahun
     * @param string $kodeTipeSurat
     * @return array
     */
    public static function getCounterPerDivisi(int $tahun, string $kodeTipeSurat): array
    {
        return static::where('tahun', $tahun)
            ->where('kode_tipe_surat', $kodeTipeSurat)
            ->selectRaw('divisi, MAX(CAST(seri_tahun AS UNSIGNED)) as last_seri_tahun')
            ->groupBy('divisi')
            ->orderBy('divisi')
            ->get()
            ->toArray();
    }

    /**
     * Get detail counter untuk divisi tertentu
     *
     * @param string $divisi
     * @param int $tahun
     * @param string $kodeTipeSurat
     * @return array
     */
    public static function getCounterDetailDivisi(string $divisi, int $tahun, string $kodeTipeSurat): array
    {
        $lastSeriTahun = static::getLastSeriTahun($tahun, $kodeTipeSurat, $divisi);

        $perBulan = static::where('tahun', $tahun)
            ->where('divisi', $divisi)
            ->where('kode_tipe_surat', $kodeTipeSurat)
            ->selectRaw('bulan, MAX(CAST(seri_bulan AS UNSIGNED)) as last_seri_bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->toArray();

        return [
            'divisi' => $divisi,
            'tahun' => $tahun,
            'kode_tipe_surat' => $kodeTipeSurat,
            'last_seri_tahun' => $lastSeriTahun,
            'detail_per_bulan' => $perBulan,
        ];
    }
}
