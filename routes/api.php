<?php

// internal API
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\Api\MemoApiController;
use App\Http\Controllers\Api\UndanganApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\RisalahApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\NotifApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\DisposisiApiController;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\ApprovalApiController;
use App\Http\Controllers\Api\UserManageApiController;

// eksternal API
use App\Http\Controllers\CetakPDFController;

Route::get('/status', function () {
    return response()->json([
        'success' => true,
        'message' => 'API SIPO is running 🚀',
    ]);
});

Route::get('/version', function () {
    return response()->json([
        'version' => '1.0.1',
        'framework' => 'Laravel 12',
    ]);
});

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/tesnotif', [NotifApiController::class, 'tesNotif']);
Route::post('/save-token-manual', [NotifApiController::class, 'saveTokenManual']);
// Route::get('/tesnotif', function() {
//     $token = 'ExponentPushToken[UJfuJXJLKsqDoZ8WOvpAeu]'; // token dari app user
//     $response = Http::post('https://exp.host/--/api/v2/push/send', [
//         'to' => $token,
//         'title' => 'Tes FCM dari Laravel 🚀',
//         'body' => 'Jika ini muncul di aplikasi SIPO, berarti FCM sudah nyambung!',
//         'sound' => 'default',
//     ]);
//     return $response->json();
// });

Route::get('/memos/{id}/lampiran/downloadAll', [MemoController::class, 'downloadAll'])->name('api.memo.lampiran.downloadAll');

// View dokumen from mobile
Route::get('/mobile/risalah/pdf/{token}', [CetakPDFController::class, 'viewRisalahPdfMobile'])->name('mobile.risalah.pdf');
Route::get('/mobile/memo/pdf/{token}', [CetakPDFController::class, 'viewMemoPdfMobile'])->name('mobile.memo.pdf');
Route::get('/mobile/undangan/pdf/{token}', [CetakPDFController::class, 'viewUndanganPdfMobile'])->name('mobile.undangan.pdf');

Route::middleware('auth:sanctum')->group(function () {

    // ===== API Memo =====
    Route::get('/memos', [MemoApiController::class, 'index']); // Index all memo, (old endpoint)
    Route::get('/memos/keluar', [MemoApiController::class, 'memoKeluar']); //Filter Memo Keluar / Memo Terkirim, BE v2
    Route::get('/memos/masuk', [MemoApiController::class, 'memoMasuk']); // Filter Memo Masuk / Memo Diterima, BE v2
    Route::get('/memos/kode', [MemoApiController::class, 'kodeFilter']);
    Route::get('/memos/{id}', [MemoApiController::class, 'show']);

    Route::get('/memos/{id}/lampiran', [MemoController::class, 'lampiran'])->name('api.memo.lampiran'); // Endpoint lampiran utama (cek single / multiple)
    // Route::get('/memos/{id}/lampiran/downloadAll', [MemoController::class, 'downloadAll'])->name('api.memo.lampiran.downloadAll');
    // Endpoint untuk akses lampiran tertentu kalau multiple
    Route::get('/memos/{id}/lampiran/{index}', [MemoController::class, 'lampiranSingle'])->name('api.memo.lampiran.single');
    Route::put('/memos/{id}/update-status', [MemoApiController::class, 'updateStatus'])->name('api.memo.updateStatus');
    Route::get('/memos/{id}/pdf', [CetakPDFController::class, 'viewMemoPdfUrl']);


    // ===== API Risalah =====
    Route::get('/risalahs', [RisalahApiController::class, 'index']);
    Route::get('/risalahs/kode', [RisalahApiController::class, 'kodeFilter']);
    Route::get('/risalahs/{id}', [RisalahApiController::class, 'show']);
    Route::get('/risalahs/{id}/lampiran', [RisalahApiController::class, 'lampiran'])->name('api.risalah.lampiran');
    Route::get('/risalahs/{id}/lampiran/{index}', [RisalahApiController::class, 'lampiranSingle'])->name('api.risalah.lampiran.single');
    Route::put('/risalahs/{id}/update-status', [RisalahApiController::class, 'updateStatus'])->name('api.risalah.updateStatus');
    Route::get('/risalahs/{id}/pdf', [CetakPDFController::class, 'viewRisalahPdfUrl']);
    // Route::get('/mobile/risalah/pdf/{token}', [CetakPDFController::class, 'viewRisalahPdfMobile'])->name('mobile.risalah.pdf');


    // ===== API Undangan =====
    Route::get('/undangans', [UndanganApiController::class, 'index']);
    Route::get('/undangans/masuk', [UndanganApiController::class, 'undanganMasuk']);
    Route::get('/undangans/keluar', [UndanganApiController::class, 'undanganKeluar']);
    Route::get('/undangans/kode', [UndanganApiController::class, 'kodeFilter']);
    Route::get('/undangans/{id}', [UndanganApiController::class, 'show']);
    Route::get('/undangans/{id}/lampiran', [UndanganApiController::class, 'lampiran'])->name('api.undangan.lampiran');
    Route::get('/undangans/{id}/lampiran/{index}', [UndanganApiController::class, 'lampiranSingle'])->name('api.undangan.lampiran.single');
    Route::put('/undangans/{id}/update-status', [UndanganApiController::class, 'updateStatus'])->name('api.undangan.updateStatus');
    Route::get('/undangans/{id}/pdf', [CetakPDFController::class, 'viewUndanganPdfUrl']);


    // ===== DISPOSISI =====
    Route::prefix('disposisi')->group(function () {
        Route::get('/', [DisposisiApiController::class, 'index']);
        Route::get('/cari-dokumen', [DisposisiApiController::class, 'cariDokumen']);
        Route::post('/', [DisposisiApiController::class, 'store']);
        Route::get('/kandidat-penerima', [DisposisiApiController::class, 'kandidatPenerima']);

        Route::get('/{disposisi}', [DisposisiApiController::class, 'show']);
        Route::patch('/{disposisi}/status', [DisposisiApiController::class, 'updateStatus']);
        Route::post('/{disposisi}/teruskan', [DisposisiApiController::class, 'teruskan']);
        Route::get('/{disposisi}/dokumen', [DisposisiApiController::class, 'lihatDokumen']);
    });

    // ===== NOTIFIKASI =====
    Route::prefix('notifikasi')->group(function () {
        Route::get('/', [NotifApiController::class, 'index']);
        Route::get('/status', [NotifApiController::class, 'notifAvailable']);
        Route::post('/token', [NotifApiController::class, 'saveToken']);
        Route::post('/{id}/read', [NotifApiController::class, 'markAsRead']);
        Route::post('/read-all', [NotifApiController::class, 'markAllAsRead']);
    });


    // // ===== API Notifikasi =====
    // Route::get('/notifikasi', [NotifApiController::class, 'index']);
    // Route::get('/notifikasi/status', [NotifApiController::class, 'notifAvailable']);
    // Route::post('/notifikasi/token', [NotifApiController::class, 'saveToken']);
    // Route::post('/notifikasi/{id}/read', [NotifApiController::class, 'markAsRead']);
    // Route::post('/notifikasi/read-all', [NotifApiController::class, 'markAllAsRead']);

    // ===== Endpoint Dashboard - Get all dashboard data =====
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // ===== Endpoint User =====
    Route::get('/users', [UserManageApiController::class, 'index']);

    // ===== Endpoint Profile =====
    Route::get('/profile', [ProfileApiController::class, 'profileDetails']);

    Route::get('/approval', [ApprovalApiController::class, 'index']);

    // ===== Endpoint Bagian Kerja =====
    Route::get('/bagian-kerja', [UserManageApiController::class, 'bagianKerja']);
    Route::delete('/bagian-kerja/{id}', [UserManageApiController::class, 'destroyBagianKerja']);

    // ===== Endpoint Struktur Organisasi =====
    Route::get('/struktur-organisasi', [UserManageApiController::class, 'strukturOrganisasi']);
});
