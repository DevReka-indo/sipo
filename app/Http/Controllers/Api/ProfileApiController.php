<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProfileApiController extends Controller
{
    public function profileDetails(Request $request)
    {
        $user = Auth::user();
        $orgName = null;
        $director = null;
        if ($user->director_id_director) {
            $director = $user->director->name_director;
            if ($user->divisi_id_divisi) {
                if ($user->department_id_department) {
                    if ($user->section_id_section) {
                        if ($user->unit_id_unit) {
                            $orgName = $user->unit->name_unit;
                        } else {
                            $orgName = $user->section->name_section;
                        }
                    } else {
                        $orgName = $user->department->name_department;
                    }
                } else {
                    $orgName = $user->divisi->nm_divisi;
                }
            }
        }

        return response()->json([
            'id' => $user->id,
            'fullname' => $user->fullname,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'nip' => $user->nip,
            'position' => $user->position->nm_position,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'profile_image' => $user->profile_image
                ? 'data:image/jpeg;base64,' . $user->profile_image
                : null,
            'role_id' => $user->role_id_role,
            'divisi_id' => $user->divisi_id_divisi,
            'direktorat' => $director,
            'organisasi' => $orgName,
        ]);
    }
}
