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
use Illuminate\Support\Facades\Http;

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

Route::get('/tesnotif', [NotifApiController::class, 'tesNotif']);
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/memos', [MemoApiController::class, 'index']);
    Route::get('/memos/kode', [MemoApiController::class, 'kodeFilter']);
    Route::get('/memos/{id}', [MemoApiController::class, 'show']);
    Route::get('/users', [App\Http\Controllers\Api\UserManageApiController::class, 'index']);

    // Endpoint lampiran utama (cek single / multiple)
    Route::get('/memos/{id}/lampiran', [MemoController::class, 'lampiran'])->name('api.memo.lampiran');
    // Route::get('/memos/{id}/lampiran/downloadAll', [MemoController::class, 'downloadAll'])->name('api.memo.lampiran.downloadAll');
    // Endpoint untuk akses lampiran tertentu kalau multiple
    Route::get('/memos/{id}/lampiran/{index}', [MemoController::class, 'lampiranSingle'])->name('api.memo.lampiran.single');
    Route::put('/memos/{id}/update-status', [MemoApiController::class, 'updateStatus'])->name('api.memo.updateStatus');
    Route::get('/memos/{id}/pdf', [CetakPDFController::class, 'viewMemoPdfUrl']);

    Route::get('/risalahs', [RisalahApiController::class, 'index']);
    Route::get('/risalahs/kode', [RisalahApiController::class, 'kodeFilter']);
    Route::get('/risalahs/{id}', [RisalahApiController::class, 'show']);
    Route::get('/risalahs/{id}/lampiran', [RisalahApiController::class, 'lampiran'])->name('api.risalah.lampiran');
    Route::get('/risalahs/{id}/lampiran/{index}', [RisalahApiController::class, 'lampiranSingle'])->name('api.risalah.lampiran.single');
    Route::put('/risalahs/{id}/update-status', [RisalahApiController::class, 'updateStatus'])->name('api.risalah.updateStatus');
    Route::get('/risalahs/{id}/pdf', [CetakPDFController::class, 'viewRisalahPdfUrl']);

    Route::get('/undangans', [UndanganApiController::class, 'index']);
    Route::get('/undangans/kode', [UndanganApiController::class, 'kodeFilter']);
    Route::get('/undangans/{id}', [UndanganApiController::class, 'show']);
    Route::get('/undangans/{id}/lampiran', [UndanganApiController::class, 'lampiran'])->name('api.undangan.lampiran');
    Route::get('/undangans/{id}/lampiran/{index}', [UndanganApiController::class, 'lampiranSingle'])->name('api.undangan.lampiran.single');
    Route::put('/undangans/{id}/update-status', [UndanganApiController::class, 'updateStatus'])->name('api.undangan.updateStatus');
    Route::get('/undangans/{id}/pdf', [CetakPDFController::class, 'viewUndanganPdfUrl']);

    Route::get('/profile', [ProfileApiController::class, 'profileDetails']);

    Route::get('/notifikasi', [NotifApiController::class, 'index']);
    Route::get('/notifikasi/status', [NotifApiController::class, 'notifAvailable']);
    Route::post('/notifikasi/token', [NotifApiController::class, 'saveToken']);
    Route::post('/notifikasi/{id}/read', [NotifApiController::class, 'markAsRead']);
    Route::post('/notifikasi/read-all', [NotifApiController::class, 'markAllAsRead']);

    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    Route::get('/approval', [App\Http\Controllers\Api\ApprovalApiController::class, 'index']);
});
