<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifTokenModel extends Model
{
    protected $table = 'notification_token';
    protected $primaryKey = 'id';

    protected $fillable = ['id_user', 'token', 'platform'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
