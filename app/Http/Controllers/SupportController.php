<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class SupportController extends Controller
{
    /**
     * Tampilkan halaman support.blade.php
     *
     * Route contoh: Route::get('/support', [SupportController::class, 'index']);
     */
    public function index()
    {
        return view('support');
    }
}
