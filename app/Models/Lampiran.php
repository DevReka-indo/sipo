<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lampiran extends Model
{
    protected $fillable = ['memo_id', 'file_name', 'file_path'];

    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'pembuat', 'id');
    }
}
