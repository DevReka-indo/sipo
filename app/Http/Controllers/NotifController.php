<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Notifikasi, Memo, Undangan, Risalah};

class NotifController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $notifications = Notifikasi::where('id_user', $user->id)
            ->orderBy('id_notifikasi', 'desc')
            ->get();
        $role = Auth::user()->role_id_role;

        foreach ($notifications as $n) {

            $notifJudul = strtolower($n->judul);
            $docJudul = $n->judul_document;
            if (str_contains($notifJudul, 'memo')) {
                $doc = Memo::where('judul', $docJudul)->first();
                $docId = $doc->id_memo ?? null;
                if ($doc) {
                    if ($role == 3 && $doc->status == 'approve') {
                        $type = 'memo-diterima';
                    } elseif ($role == 3 && $doc->status != 'approve') {
                        $type = 'memo-terkirim';
                    } elseif ($role == 2) {
                        $type = 'memo';
                    }
                } else {
                    $type = 'memo-null';
                }
            } elseif (str_contains($notifJudul, 'undangan')) {
                $doc = Undangan::where('judul', $docJudul)->first();
                $docId = $doc->id_undangan ?? null;
                if ($doc) {
                    $type = 'undangan';
                } else {
                    $type = 'undangan-null';
                }
            } elseif (str_contains($notifJudul, 'risalah')) {
                $doc = Risalah::where('judul', $docJudul)->first();
                $docId = $doc->id_risalah ?? null;
                if ($doc) {
                    $type = 'risalah';
                } else {
                    $type = 'risalah-null';
                }
            }

            if ($role == 2) { // ADMIN
                if ($docId) {
                    if ($type == 'memo') {
                        $n->redirect_url = route('memo.show', $docId);
                    } elseif ($type == 'undangan') {
                        $n->redirect_url = route('view.undangan', $docId);
                    } elseif ($type == 'risalah') {
                        $n->redirect_url = route('view.risalahAdmin', $docId);
                    }
                } else {
                    if ($type == 'memo-null') {
                        $n->redirect_url = route('admin.memo.index');
                    } elseif ($type == 'undangan-null') {
                        $n->redirect_url = route('admin.undangan.index');
                    } elseif ($type == 'risalah-null') {
                        $n->redirect_url = route('admin.risalah.index');
                    }
                }
            } elseif ($role == 3) { // MANAGER
                if ($docId) {
                    if ($type == 'memo-terkirim') {
                        $n->redirect_url = route('view.memo-terkirim', $docId);
                    } elseif ($type == 'memo-diterima') {
                        $n->redirect_url = route('view.memo-diterima', $docId);
                    } elseif ($type == 'undangan') {
                        $n->redirect_url = route('view.undangan', $docId);
                    } elseif ($type == 'risalah') {
                        $n->redirect_url = route('persetujuan.risalah', $docId);
                    }
                } elseif (!$docId) {
                    if ($type == 'memo-null') {
                        $n->redirect_url = route('memo.terkirim');
                    } elseif ($type == 'memo-diterima') {
                        $n->redirect_url = route('memo.diterima');
                    } elseif ($type == 'undangan-null') {
                        $n->redirect_url = route('undangan.manager');
                    } elseif ($type == 'risalah-null') {
                        $n->redirect_url = route('risalah.manager');
                    }
                }
            }
        }
        //dd($notifications);
        return response()->json(['notifications' => $notifications]);
    }

    // Ambil jumlah notifikasi yang belum dibaca
    public function getUnreadCount()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $count = Notifikasi::where('id_user', $user->id)
            ->where('dibaca', 0)
            ->count();

        return response()->json(['count' => $count]);
    }

    // Tandai notifikasi sebagai sudah dibaca
    public function markAllAsRead($id)
    {
        $user = Auth::user();
        if ($user) {
            // Gunakan id_notifikasi sesuai dengan struktur database Anda
            Notifikasi::where('id_user', $user->id)
                ->where('id_notifikasi', $id) // atau 'id' sesuai kolom primary key
                ->update(['dibaca' => 1]);
        }
        return response()->json(['success' => true]);
    }
    // Di model Notification
    public function getIconColor()
    {
        $colors = [
            'undangan' => 'success',
            'memo' => 'primary',
            'risalah' => 'info'
        ];

        return $colors[strtolower($this->type ?? '')] ?? 'secondary';
    }

    public function getIconClass()
    {
        $icons = [
            'undangan' => 'fa-solid fa-calendar-week',
            'memo' => 'fa-solid fa-file-text',
            'risalah' => 'fa-solid fa-clipboard'
        ];

        return $icons[strtolower($this->type ?? '')] ?? 'fa-solid fa-file';
    }
}
