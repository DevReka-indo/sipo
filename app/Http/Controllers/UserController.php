<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\Director;
use App\Models\Department;
use App\Models\Section;
use App\Models\Unit;
use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;

class UserController extends Controller
{
    public function showRole()
    {
        $role = Role::all();
        return view('user.role', compact('role'));
    }
    // Menampilkan form edit dengan data user
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $divisi = Divisi::all();
        $roles = Role::all();
        $positions = Position::all();

        $mainDirector = Director::with(['subDirectors.divisi.department.section.unit', 'subDirectors.divisi.department.unit', 'subDirectors.department.section.unit', 'subDirectors.department.unit', 'divisi.department.section.unit', 'divisi.department.unit', 'department.section.unit', 'department.unit'])
            ->where('is_main', 1)
            ->first();

        return view('user.manage', compact('mainDirector', 'user', 'divisi', 'roles', 'positions'));
    }

    // Menangani update data user
    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $request->validate([
            'firstname' => 'nullable|string|max:50',
            'lastname' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:25',
            'email' => 'nullable|string|email|max:70|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'phone_number' => 'nullable',
            'role_id_role' => 'nullable',
            'position_id_position' => 'nullable|exists:position,id_position',
            'parent_id' => 'nullable',
            'parent_type' => 'nullable',
            'kode_bagian' => 'nullable|array',
            'kode_bagian.*' => 'nullable|string',
        ]);
        //dd($request->all());

        if ($request->firstname . ' ' . $request?->lastname != $user->firstname . ' ' . $user?->lastname) {
            $newFullname = $request->firstname . ' ' . $request?->lastname;
            $oldFullname = $user->firstname . ' ' . $user?->lastname;
            $memos = Memo::where('nama_bertandatangan', $oldFullname)->get();

            foreach ($memos as $memo) {
                $memo->nama_bertandatangan = $newFullname;
                $memo->save();
            }

            $undangans = Undangan::where('nama_bertandatangan', $oldFullname)->get();

            foreach ($undangans as $undangan) {
                $undangan->nama_bertandatangan = $newFullname;
                $undangan->save();
            }

            $risalahsPemimpin = Risalah::where('nama_pemimpin_acara', $oldFullname)->get();

            foreach ($risalahsPemimpin as $risalah) {
                $risalah->nama_pemimpin_acara = $newFullname;
                $risalah->save();
            }
            $risalahsNotulis = Risalah::where('nama_notulis_acara', $oldFullname)->get();

            foreach ($risalahsNotulis as $risalah) {
                $risalah->nama_notulis_acara = $newFullname;
                $risalah->save();
            }
        }
        if ($request->filled('firstname')) {
            $user->firstname = $request->firstname;
        }
        if ($request->filled('lastname')) {
            $user->lastname = $request->lastname;
        }
        if ($request->filled('nip')) {
            $user->nip = $request->nip;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        if ($request->filled('position_id_position')) {
            $user->position_id_position = $request->position_id_position;
        }
        if ($request->filled('role_id_role')) {
            $user->role_id_role = $request->role_id_role;
        }
        // SEBELUM
        // if ($request->filled('kode_bagian')) {
        //     $user->kode_bagian = implode(';', $request->kode_bagian);
        // }

        // SESUDAH
        if ($request->has('kode_bagian')) {
            // Filter empty values dari array
            $kodeBagian = array_filter($request->kode_bagian, function($value) {
                return !empty($value);
            });

            // Set ke null jika kosong, atau join dengan ; jika ada isinya
            $user->kode_bagian = !empty($kodeBagian) ? implode(';', $kodeBagian) : null;
        }

        $bagian = $request->parent_id;
        $type = $request->parent_type;
        if ($bagian && $type) {
            if ($type == 'director') {
                // Direktur
                $user->director_id_director = $bagian;
                $user->divisi_id_divisi = null;
                $user->department_id_department = null;
                $user->section_id_section = null;
                $user->unit_id_unit = null;
            } elseif ($type == 'divisi') {
                // Divisi
                $user->director_id_director = Divisi::where('id_divisi', $bagian)->value('director_id_director');
                $user->divisi_id_divisi = $bagian;
                $user->department_id_department = null;
                $user->section_id_section = null;
                $user->unit_id_unit = null;
            } elseif ($type == 'department') {
                // Department
                $user->director_id_director = Department::where('id_department', $bagian)->value('director_id_director');
                $user->divisi_id_divisi = Department::where('id_department', $bagian)->value('divisi_id_divisi') ?? null;
                $user->department_id_department = $bagian;
                $user->section_id_section = null;
                $user->unit_id_unit = null;
            } elseif ($type == 'section') {
                // Section
                $user->department_id_department = Section::where('id_section', $bagian)->value('department_id_department');
                $user->director_id_director = Department::where('id_department', $user->department_id_department)->value('director_id_director');
                $user->divisi_id_divisi = Department::where('id_department', $user->department_id_department)->value('divisi_id_divisi') ?? null;
                $user->section_id_section = $bagian;
                $user->unit_id_unit = null;
            } elseif ($type == 'unit') {
                //Unit
                $user->section_id_section = Unit::where('id_unit', $bagian)->value('section_id_section') ?? null;
                $user->department_id_department = Unit::where('id_unit', $bagian)->value('department_id_department') ?? (Section::where('id_section', $user->section_id_section)->value('department_id_department') ?? null);
                $user->divisi_id_divisi = Department::where('id_department', $user->department_id_department)->value('divisi_id_divisi') ?? (Section::where('id_section', $user->section_id_section)->value('divisi_id_divisi') ?? null);
                $user->director_id_director = Department::where('id_department', $user->department_id_department)->value('director_id_director');
                $user->unit_id_unit = $bagian;
            }
        }

        $user->save();

        return redirect()->route('user.manage')->with('success', 'Data user berhasil diperbarui');
    }
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['success' => 'User berhasil dinonaktifkan'], 200);
    }

    public function restore($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->restore();

        return response()->json(['success' => 'User berhasil diaktifkan'], 200);
    }
}
