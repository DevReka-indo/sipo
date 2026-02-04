<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use App\Models\Unit;
use App\Models\Director;
use App\Models\Section;
use App\Models\BagianKerja;

class UserManageController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $divisi = Divisi::all();
        $roles = Role::all();
        $positions = Position::all();

        $departments = Department::whereNotNull('kode_department')->get();
        $divisis = Divisi::whereNotNull('kode_divisi')->get();

        // Daftar bagian kerja
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        $kodeItems = collect();

        foreach ($departments as $dept) {
            $kodeItems->push([
                'id' => $dept->id_department,
                'kode' => $dept->kode_department,
                'label' => $dept->nama_department, // pastikan pakai nama department
                'tipe' => 'department',
            ]);
        }

        foreach ($divisis as $divisi) {
            $kodeItems->push([
                'id' => $divisi->id_divisi,
                'kode' => $divisi->kode_divisi,
                'label' => $divisi->nm_divisi, // pastikan pakai nama divisi
                'tipe' => 'divisi',
            ]);
        }

        $sortOrder = $request->query('sort', 'asc');
        $view = $request->query('view', 'all'); // default lihat aktif

        if ($view === 'deleted') {
            // Hanya ambil user yang soft delete
            $users = User::onlyTrashed()->with(['role', 'divisi', 'position']);
        } elseif ($view === 'active') {
            //ambil user aktif
            $users = User::with(['role', 'divisi', 'position']);
        } else {
            // Default ambil semua user
            $users = User::withTrashed(['role', 'divisi', 'position']);
        }

        if ($request->filled('search')) {
            $users->where(function ($query) use ($request) {
                $query
                    ->where('firstname', 'like', '%' . $request->search . '%')
                    ->orWhere('lastname', 'like', '%' . $request->search . '%')
                    ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $users->where(function ($query) use ($request) {
                $query->where('role_id_role', $request->role);
            });
        }

        if ($request->filled('kode')) {
            $kode = $request->kode;

            // cek apakah kode berasal dari department atau divisi
            $dept = $departments->firstWhere('kode_department', $kode);
            $div = $divisis->firstWhere('kode_divisi', $kode);

            if ($dept) {
                $users->where('department_id_department', $dept->id_department);
            } elseif ($div) {
                $users->where('divisi_id_divisi', $div->id_divisi);
            }
        }

        $users->orderBy('firstname', $sortOrder);
        $perPage = $request->get('per_page', 10);
        $users = $users->paginate($perPage)->appends($request->all());

        $mainDirector = Director::with(['subDirectors.divisi.department.section.unit', 'subDirectors.divisi.department.unit', 'subDirectors.department.section.unit', 'subDirectors.department.unit', 'divisi.department.section.unit', 'divisi.department.unit', 'department.section.unit', 'department.unit'])
            ->where('is_main', 1)
            ->first();

        $organisasi = [];

        $directors = Director::all();
        $division = Divisi::all();
        $department = Department::all();
        $section = Section::all();
        $unit = Unit::all();

        foreach ($directors as $director) {
            $organisasi[] = [
                'id' => 'director_' . $director->id_director,
                'name' => $director->name_director,
            ];
        }
        foreach ($division as $div) {
            $organisasi[] = [
                'id' => 'division_' . $div->id_divisi,
                'name' => $div->nm_divisi,
            ];
        }
        foreach ($department as $dept) {
            $organisasi[] = [
                'id' => 'department_' . $dept->id_department,
                'name' => $dept->name_department,
            ];
        }
        foreach ($section as $sect) {
            $organisasi[] = [
                'id' => 'section_' . $sect->id_section,
                'name' => $sect->name_section,
            ];
        }
        foreach ($unit as $u) {
            $organisasi[] = [
                'id' => 'unit_' . $u->id_unit,
                'name' => $u->name_unit,
            ];
        }

        // Kirim data ke view user-manage
        return view('superadmin.user-manage', compact('divisi', 'view', 'roles', 'positions', 'users', 'kodeItems', 'sortOrder', 'mainDirector', 'organisasi', 'bagianKerja'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string|max:15',
            'divisi_id_divisi' => 'required|exists:divis,id_divisi',
            'position_id_position' => 'required|exists:position,id_position',
            'role_id_role' => 'required|exists:role,id_role',
            'kode_bagian' => 'nullable|array',
            'kode_bagian.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $kodeBagian = null;
        if ($request->has('kode_bagian')) {
            $kodeBagianFiltered = array_filter($request->kode_bagian, function ($value) {
                return !empty($value);
            });

            $kodeBagian = !empty($kodeBagianFiltered) ? implode(';', $kodeBagianFiltered) : null;
        }

        User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'divisi_id_divisi' => $request->divisi_id_divisi,
            'position_id_position' => $request->position_id_position,
            'role_id_role' => $request->role_id_role,
            'kode_bagian' => $kodeBagian,
        ]);

        return redirect()->route('user.manage')->with('success', 'User berhasil ditambahkan!');
    }

    public function filter(Request $request)
    {
        // Mendapatkan parameter sorting dari request
        $sortOrder = $request->query('sort', 'asc');

        // Melakukan query ke database dengan pengurutan berdasarkan firstname
        $users = User::orderBy('firstname', $sortOrder)->paginate(6);

        // Mengirim data user ke view
        return view('superadmin.user-manage', compact('users', 'sortOrder'));
    }
}
