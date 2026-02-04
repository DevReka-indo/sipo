<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['id', 'firstname', 'lastname', 'nip', 'email', 'password', 'phone_number', 'kode_bagian', 'role_id_role', 'position_id_position', 'director_id_director', 'divisi_id_divisi', 'department_id_department', 'section_id_section', 'unit_id_unit', 'profile_image'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    public function bagianKerja()
    {
        return $this->belongsTo(BagianKerja::class, 'kode_bagian', 'kode_bagian');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_number' => 'string',
        ];
    }
    public function getFullnameAttribute()
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id_role', 'id_role');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id_position', 'id_position');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id_department', 'id_department');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id_section', 'id_section');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id_unit', 'id_unit');
    }
    public function director()
    {
        return $this->belongsTo(Director::class, 'director_id_director', 'id_director');
    }

    public function expo_token()
    {
        return $this->hasOne(NotifTokenModel::class, 'id_user', 'id');
    }
}
