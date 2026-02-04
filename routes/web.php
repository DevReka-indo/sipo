<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManageController;
use App\Http\Controllers\ForgotPwController;
use App\Http\Controllers\CetakPDFController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\UndanganController;
use App\Http\Controllers\RisalahController;
use App\Http\Controllers\KirimController;
use App\Http\Controllers\NotifController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BackupRisalahController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\CounterNomorSuratController;
use App\Http\Controllers\KodeBagianController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\Director;
use App\Models\Divisi;
use App\Models\Department;
use App\Models\Seri;
use App\Models\User;

// GUEST
Route::get('/', function () {
    return view('auth.login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/logout', function () {
    return redirect()->route('login');
});

//Route::get('/forgot-password', [ForgotPwController::class, 'showForgotPasswordForm'])->name('forgot-password');
Route::get('/forgot-password', [ForgotPwController::class, 'showForgotPasswordForm'])->name('forgot-password');
Route::post('/send-email', [ForgotPwController::class, 'sendVerificationEmail'])->name('send-email');
Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');
Route::post('/update-password', [ForgotPwController::class, 'updatePassword'])->name('password.update');

// Route::get('/mail-test', function () {
//     Mail::raw('This is a test email', function ($m) {
//         $m->to('nauqi2208@gmail.com')->subject('Mail Test');
//     });
//     return 'Sent';
// });

Route::get('/support', [SupportController::class, 'index'])->name('support');

// SEMUA
Route::middleware(['auth', 'role:1,2,3'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return view('superadmin.dashboard');
    // })->middleware(['auth', 'verified'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // counter nomor surat
    Route::get('/counter-nomor-surat', [CounterNomorSuratController::class, 'index'])->name('counter-nomor-surat.index');

    Route::get('/info', function () {
        return view('info');
    })->name('info');

    Route::get('/edit-profile', [ProfileController::class, 'editProfile'])->name('edit-profile');
    Route::post('/delete-photo', [ProfileController::class, 'deletePhoto'])->name('superadmin.deletePhoto');
    Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('superadmin.updateProfile');

    //Edit Undangan
    Route::get('/undangan/edit/{id}', [UndanganController::class, 'edit'])->name('undangan.edit');
    Route::put('/undangan/update/{id_undangan}', [UndanganController::class, 'update'])->name('undangan/update');
    Route::delete('/undangan/lampiran-existing/{undanganId}/{index}', [UndanganController::class, 'deleteLampiranExisting'])->name('undangan.lampiran.delete-existing');

    // Cetak PDF Controller
    Route::get('/format-cetakLaporan-memo', [CetakPDFController::class, 'laporanmemoPDF'])->name('format-cetakLaporan-memo');
    Route::get('/format-cetakLaporan-undangan', [CetakPDFController::class, 'laporanundanganPDF'])->name('format-cetakLaporan-undangan');
    Route::get('/format-cetakLaporan-risalah', [CetakPDFController::class, 'laporanrisalahPDF'])->name('format-cetakLaporan-risalah');

    Route::get('berkas/cetak/risalah/{id}', [CetakPDFController::class, 'cetakrisalahPDF'])->name('cetakrisalah');
    Route::get('view/risalahPDF/{id_risalah}', [CetakPDFController::class, 'viewrisalahPDF'])->name('view-risalahPDF');

    Route::get('berkas/cetak/memo/{id}', [CetakPDFController::class, 'cetakmemoPDF'])->name('cetakmemo');
    Route::get('view/memoPDF/{id_memo}', [CetakPDFController::class, 'viewmemoPDF'])->name('view-memoPDF');
    Route::get('berkas/unduh/lampiran/{id_memo}', [CetakPDFController::class, 'downloadAllAttachmentsZip'])->name('download-semua-lampiran');
    Route::get('berkas/cetak/undangan/{id}', [CetakPDFController::class, 'cetakundanganPDF'])->name('cetakundangan');
    Route::get('view/undanganPDF/{id_undangan}', [CetakPDFController::class, 'viewundanganPDF'])->name('view-undanganPDF');
    Route::get('berkas/unduh/lampiran-undangan/{id_undangan}', [CetakPDFController::class, 'downloadAllAttachmentsZipUndangan'])->name('download-semua-lampiran-undangan');
    Route::get('berkas/unduh/lampiran-risalah/{id_risalah}', [CetakPDFController::class, 'downloadAllAttachmentsZipRisalah'])->name('download-semua-lampiran-risalah');

    // arsip
    Route::post('/arsip/{document_id}/{jenis_document}/simpan', [ArsipController::class, 'archiveDocument'])->name('arsip.archive');
    Route::get('/arsip/memo', [ArsipController::class, 'indexMemo'])->name('arsip.memo');
    Route::get('/arsip/undangan', [ArsipController::class, 'indexUndangan'])->name('arsip.undangan');
    Route::get('/arsip/risalah', [ArsipController::class, 'indexRisalah'])->name('arsip.risalah');

    Route::delete('/arsip/{document_id}/{jenis_document}', [ArsipController::class, 'restoreDocument'])->name('arsip.restore');
    Route::get('/memo/arsip/{id}', [ArsipController::class, 'view'])->name('view.memo-arsip');
    Route::get('/undangan/arsip/{id}', [ArsipController::class, 'viewUndangan'])->name('view.undangan-arsip');
    Route::get('/risalah/arsip/{id}', [ArsipController::class, 'viewRisalah'])->name('view.risalah-arsip');

    //data perusahaan
    Route::get('/data-perusahaan', [PerusahaanController::class, 'index'])->name('data-perusahaan');

    //notifikasi
    Route::get('/notifikasi', [NotifController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/jumlah', [NotifController::class, 'getUnreadCount'])->name('notifications.count');
    Route::post('/notifications/{id}/tanda-dibaca', [NotifController::class, 'markAllAsRead'])->name('notifications.markAsRead');

    Route::get('/risalah/edit/{id}', [RisalahController::class, 'edit'])->name('risalah.edit');
    Route::put('/risalah/{id}', [RisalahController::class, 'update'])->name('risalah.update');
});

// SUPERADMIN
Route::middleware(['auth', 'role:1'])->group(function () {
    // dashboard
    Route::get('/dashboard.superadmin', [DashboardController::class, 'index'])->name('superadmin.dashboard');

    // memo
    Route::get('/superadmin/memo', [MemoController::class, 'superadmin'])->name('superadmin.memo.index');
    Route::delete('/memo/delete/{id_memo}', [MemoController::class, 'delete'])->name('memo.delete');

    //undangan
    Route::get('/superadmin/undangan', [UndanganController::class, 'superadmin'])->name('superadmin.undangan.index');
    Route::delete('/undangan/delete/{id_undangan}', [UndanganController::class, 'destroy'])->name('undangan.destroy');

    // risalah
    Route::get('/superadmin/risalah', [RisalahController::class, 'superadmin'])->name('superadmin.risalah.index');
    Route::delete('/risalah/delete/{id_risalah}', [RisalahController::class, 'destroy'])->name('risalah.destroy');

    // manage user
    Route::get('/user-manage/edit/{id}', [UserController::class, 'edit'])->name('user-manage.edit');
    Route::delete('/user-manage/delete/{id}', [UserController::class, 'destroy'])->name('user-manage.destroy');
    Route::put('/user-manage/restore/{id}', [UserController::class, 'restore'])->name('user-manage.restore');
    Route::put('/user-manage/update/{id}', [UserController::class, 'update'])->name('user-manage/update');
    Route::get('/role-management', [UserController::class, 'showRole'])->name('user.role');
    Route::get('/user-manage/paginate', [UserManageController::class, 'paginateUsers'])->name('user-manage.paginate');
    Route::get('/user-manage', [UserManageController::class, 'index'])->name('user.manage');
    // Route::get('user-manage/add', [RegisteredUserController::class, 'create'])->name('user-manage/add');
    Route::post('user-manage/add', [RegisteredUserController::class, 'store'])->name('user-manage/add');
    Route::post('user-manage/import', [RegisteredUserController::class, 'import_ajax'])->name('user-manage.import');

    //Perusahaan Controller
    Route::post('/data-perusahaan/update', [PerusahaanController::class, 'update'])->name('data-perusahaan.update');

    // Kode Bagian Controller
    Route::get('/kode-bagian', [KodeBagianController::class, 'index'])->name('kode-bagian.index');
    Route::get('/kode-bagian/create', [KodeBagianController::class, 'create'])->name('kode-bagian.create');
    Route::post('/kode-bagian', [KodeBagianController::class, 'store'])->name('kode-bagian.store');
    Route::get('/kode-bagian/{id}/edit', [KodeBagianController::class, 'edit'])->name('kode-bagian.edit');
    Route::put('/kode-bagian/{id}', [KodeBagianController::class, 'update'])->name('kode-bagian.update');
    Route::delete('/kode-bagian/{id}', [KodeBagianController::class, 'destroy'])->name('kode-bagian.destroy');
    Route::post('{id}/restore', [KodeBagianController::class, 'restore'])->name('kode-bagian.restore');

    // Organization Controller
    Route::put('/organization/{type}/{id}', [OrganizationController::class, 'update'])->name('organization.update');
    Route::delete('/organization/{type}/{id}', [OrganizationController::class, 'delete'])->name('organization.delete');
    Route::get('/organization-manage', [OrganizationController::class, 'index'])->name('organization.manageOrganization');
    Route::post('organization-manage/add', [OrganizationController::class, 'store'])->name('organization-manage/add');

    //pemulihan
    Route::get('/memo-restore', [BackupController::class, 'memo'])->name('memo.backup');
    Route::post('/memo-restore-file/{id}', [BackupController::class, 'RestoreMemo'])->name('memo.restore-file');
    Route::delete('/memo-force-delete/{id}', [BackupController::class, 'forceDeleteMemo'])->name('memo.forceDelete');
    Route::post('/memo/bulk-restore', [BackupController::class, 'bulkRestoreMemo'])->name('memo.bulk-restore');
    Route::delete('/memo/bulk-force-delete', [BackupController::class, 'bulkForceDeleteMemo'])->name('memo.bulk-force-delete');

    Route::get('/undangan-restore', [BackupController::class, 'undangan'])->name('undangan.backup');
    Route::post('/undangan-restore/{id}', [BackupController::class, 'RestoreUndangan'])->name('undangan.restore');
    Route::delete('/undangan-force-delete/{id}', [BackupController::class, 'forceDelete'])->name('undangan.forceDelete');
    Route::post('/undangan/bulk-restore', [BackupController::class, 'bulkRestore'])->name('undangan.bulk-restore');
    Route::delete('/undangan/bulk-force-delete', [BackupController::class, 'bulkForceDelete'])->name('undangan.bulk-force-delete');

    Route::get('/risalah-restore', [BackupRisalahController::class, 'risalah'])->name('risalah.backup');
    Route::post('/risalah-restore/{id}', [BackupRisalahController::class, 'RestoreRisalah'])->name('risalah.restore');
    Route::delete('/risalah/force-delete/{id}', [BackupController::class, 'forceDeleteRisalah'])->name('risalah.forcedestroy');
    Route::post('/risalah/bulk-restore', [BackupController::class, 'bulkRestoreRisalah'])->name('risalah.bulk-restore');
    Route::post('/risalah/bulk-force-delete', [BackupController::class, 'bulkForceDeleteRisalah'])->name('risalah.bulk-force-delete');
});

Route::middleware(['auth', 'role:1,2'])->group(function () {
    Route::get('/memo/edit/{id_memo}', [MemoController::class, 'edit'])->name('memo.edit');
    Route::put('/memo/update/{id_memo}', [MemoController::class, 'update'])->name('memo/update');

    //Edit Memo Baru
    Route::get('/memo/edit-baru/{id_memo}', [MemoController::class, 'editBaru'])->name('memo.edit-baru');
    Route::put('/memo/update-baru/{id_memo}', [MemoController::class, 'updateBaru'])->name('memo/update-baru');

    //Delete Lampiran Memo
    Route::delete('/memo/lampiran-existing/{memoId}/{index}', [MemoController::class, 'deleteLampiranExisting'])->name('memo.lampiran.delete-existing');

    //Delete Lampiran Risalah
    Route::delete('/risalah/lampiran-existing/{id}/{index}', [RisalahController::class, 'deleteLampiranExisting'])->name('risalah.lampiran.delete-existing');

    //Delete Lampiran Risalah
    Route::delete('/risalah/lampiran-existing/{id}/{index}', [RisalahController::class, 'deleteLampiranExisting'])->name('risalah.lampiran.delete-existing');

    //laporan
    Route::get('/laporan-memo', function () {
        return view('superadmin.laporan.laporan-memo');
    })->name('laporan-memo.superadmin');

    Route::get('/laporan-risalah', function () {
        return view('superadmin.laporan.laporan-risalah');
    })->name('laporan-risalah.superadmin');
    Route::get('/laporan-undangan', function () {
        return view('superadmin.laporan.laporan-undangan');
    })->name('laporan-undangan.superadmin');

    Route::get('/cetak-laporan-memo', [LaporanController::class, 'index'])->name('cetak-laporan-memo.superadmin');
    Route::post('/cetak-laporan-memo', [LaporanController::class, 'filterMemosByDate'])->name('cetak-laporan-memo.filter');
    Route::get('/cetak-laporan-undangan', [LaporanController::class, 'undangan'])->name('cetak-laporan-undangan.superadmin');
    Route::post('/cetak-laporan-undangan', [LaporanController::class, 'filterUndanganByDate'])->name('cetak-laporan-undangan.filter');
    Route::get('/cetak-laporan-risalah', [LaporanController::class, 'risalah'])->name('cetak-laporan-risalah.superadmin');
    Route::post('/cetak-laporan-risalah', [LaporanController::class, 'filterRisalahByDate'])->name('cetak-laporan-risalah.filter');
});

// ADMIN
Route::middleware(['auth', 'role:2'])->group(function () {
    // dashboard
    Route::get('/dashboard.admin', [DashboardController::class, 'index'])->name('admin.dashboard');

    // memo
    Route::get('/memo-admin', [MemoController::class, 'index'])->name('admin.memo.index');
    // Route::get('memo-admin/add1', [MemoController::class, 'create'])->name('admin.memo.store');
    Route::get('/memo/{id}', [MemoController::class, 'view'])->name('memo.show');
    Route::get('/kirim-memoAdmin/{id}', [KirimController::class, 'index'])->name('kirim-memoAdmin.admin');
    Route::get('/admin/memo-terkirim', [MemoController::class, 'memoTerkirim'])->name('admin.memo.terkirim');
    Route::get('/admin/memo-diterima', [MemoController::class, 'memoDiterima'])->name('admin.memo.diterima');
    Route::post('/memo/next-seri', [MemoController::class, 'nextSeri'])->name('memo.nextSeri');

    // coba memo dengan TinyMCE
    Route::get('memo-admin/add', [MemoController::class, 'createCoba'])->name('admin.memo.store2');
    // Route::post('memo-admin/store-coba', [MemoController::class, 'storeCoba'])->name('admin.memo.store-coba');
    // Route::get('memo-admin/add-coba', [MemoController::class, 'createCoba'])->name('admin.memo.add-coba');
    // Route::post('memo-admin/store-coba', [MemoController::class, 'storeCoba'])->name('admin.memo.store-coba');

    //undangan
    Route::get('/admin/undangan', [UndanganController::class, 'index'])->name('admin.undangan.index');
    // Undangan (Admin: Masuk/Keluar)
    Route::get('/admin/undangan/terkirim', [UndanganController::class, 'undanganTerkirim'])->name('admin.undangan.terkirim');
    Route::get('/admin/undangan/diterima', [UndanganController::class, 'undanganDiterima'])->name('admin.undangan.diterima');

    // risalah
    Route::get('/risalah/admin', [RisalahController::class, 'index'])->name('admin.risalah.index');
    Route::get('/risalah/tambah', [RisalahController::class, 'create'])->name('admin.risalah.add');
    Route::get('/risalah/tambah-tanpa-undangan', [RisalahController::class, 'createCustom'])->name('admin.risalah-custom.add');
    Route::post('/risalah/store', [RisalahController::class, 'store'])->name('risalah.store');
    Route::get('/risalah/view/{id}', [RisalahController::class, 'view'])->name('view.risalahAdmin');

    Route::get('/risalah/{id}/preview', [RisalahController::class, 'showFile'])->name('risalah.preview');
});

// MANAGER
Route::middleware(['auth', 'role:3'])->group(function () {
    // dashboard
    Route::get('dashboard.manager', [DashboardController::class, 'index'])->name('manager.dashboard');

    // memo
    Route::get('/manager/memo', [KirimController::class, 'memo'])->name('memo.manager');
    Route::get('/memo-terkirim', [KirimController::class, 'memoTerkirim'])->name('memo.terkirim');
    Route::get('/memo-diterima', [KirimController::class, 'memoDiterima'])->name('memo.diterima');
    // Route::get('memo-manager/add', [MemoController::class, 'create'])->name('memo-manager/add');

    Route::get('memo-manager/add2', [MemoController::class, 'createCoba'])->name('memo-manager/add2');

    Route::put('/memo/{id}/update-status', [MemoController::class, 'updateStatus'])->name('memo.updateStatus');
    Route::get('/view-memoTerkirim/{id}', [MemoController::class, 'showTerkirim'])->name('view.memo-terkirim');
    Route::get('/view-memoDiterima/{id}', [MemoController::class, 'showDiterima'])->name('view.memo-diterima');

    //undangan
    Route::get('/manager/undangan', [UndanganController::class, 'index'])->name('undangan.undangan');
    Route::put('/undangan/{id}/update-status', [UndanganController::class, 'updateDocumentStatus'])->name('undangan.updateStatus');
    Route::get('/manager/undangan', [KirimController::class, 'undangan'])->name('undangan.manager');
    // Undangan (Manager) - masuk & terkirim
    Route::get('/manager/undangan/diterima', [KirimController::class, 'undanganDiterima'])->name('undangan.diterima');
    Route::get('/manager/undangan/terkirim', [KirimController::class, 'undanganTerkirim'])->name('undangan.terkirim');

    //risalah
    Route::get('/manager/risalah', [KirimController::class, 'risalah'])->name('risalah.manager');
    Route::get('/risalah-tambah', [KirimController::class, 'create'])->name('add-risalah.manager');
    Route::get('/risalah-tambah-tanpa-undangan', [KirimController::class, 'createCustom'])->name('add-risalah.custom.manager');
    Route::post('/risalah-store', [KirimController::class, 'store'])->name('risalah.store.manager');
    Route::get('/persetujuan-risalah/{id}', [KirimController::class, 'viewRisalah'])->name('persetujuan.risalah');
    Route::put('/risalah/{id}/update-status', [RisalahController::class, 'updateStatus'])->name('risalah.updateStatus');
});

Route::middleware(['auth', 'role:2,3'])->group(function () {
    // memo
    //Edit Memo Baru
    Route::get('/memo/edit-baru/{id_memo}', [MemoController::class, 'editBaru'])->name('memo.edit-baru');
    Route::put('/memo/update-baru/{id_memo}', [MemoController::class, 'updateBaru'])->name('memo/update-baru');

    //Delete Lampiran Memo
    Route::delete('/memo/lampiran-existing/{memoId}/{index}', [MemoController::class, 'deleteLampiranExisting'])->name('memo.lampiran.delete-existing');
    // Route::post('memo/add/doc', [MemoController::class, 'store'])->name('memo-admin.store');
    Route::post('memo/add/doc2', [MemoController::class, 'storeCoba'])->name('admin-memo.store');

    // undangan
    Route::get('undangan/add', [UndanganController::class, 'create'])->name('undangan-admin/add');
    Route::post('undangan/add/doc', [UndanganController::class, 'store'])->name('undangan-superadmin.store');
    Route::get('/undangan/{id}', [UndanganController::class, 'view'])->name('view.undangan');

    //risalah karena ternyata staff juga bisa approval :sob:
    Route::put('/risalah/{id}/update-status', [RisalahController::class, 'updateStatus'])->name('risalah.updateStatus');
});
