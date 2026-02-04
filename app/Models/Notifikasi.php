<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Notifikasi extends Model
{
    use HasFactory;
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public $timestamps = false;

    protected $fillable = ['judul', 'judul_document', 'id_user', 'updated_at', 'dibaca'];
    protected $attributes = [
        'dibaca' => false, // Secara default 'dibaca' akan bernilai false
    ];
}
