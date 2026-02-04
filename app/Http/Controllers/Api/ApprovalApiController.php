<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Kirim_Document, Memo, Undangan, Risalah};
use Illuminate\Support\Facades\Auth;

class ApprovalApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $latestMemo = Kirim_Document::where('id_penerima', $user->id)
            ->where('jenis_document', 'memo')
            ->where('status', 'pending')
            ->orderBy('updated_at', 'desc')
            ->first();
      if ($latestMemo){
            $memo = Memo::where('id_memo', $latestMemo->id_document)->select('judul', 'updated_at')->first();
        } else {
            $memo = null;
        }
        
        $latestRisalah = Kirim_Document::where('id_penerima', $user->id)
            ->where('jenis_document', 'risalah')
            ->where('status', 'pending')
            ->orderBy('updated_at', 'desc')
            ->first();
            if($latestRisalah){
        $risalah = Risalah::where('id_risalah', $latestRisalah->id_document)->select('judul', 'updated_at')->first();
        } else {
           $risalah = null;
        }
        $latestUndangan = Kirim_Document::where('id_penerima', $user->id)
            ->where('jenis_document', 'undangan')
            ->where('status', 'pending')
            ->orderBy('updated_at', 'desc')
            ->first();
            if($latestUndangan){
        $undangan = Undangan::where('id_undangan', $latestUndangan->id_document)->select('judul', 'updated_at')->first();
        } else {
            $undangan = null;
        }
        return response()->json([
            'status' => 'success',
            'data' => [
                'memo' => $memo,
                'risalah' => $risalah,
                'undangan' => $undangan,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
