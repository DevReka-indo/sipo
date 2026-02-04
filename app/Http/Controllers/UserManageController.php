<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;
use App\Models\Department;
use App\Models\Unit;
use App\Models\Director;
use App\Models\Section;
use App\Models\BagianKerja;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserManageController extends Controller
{
    public function showRole()
    {
        $role = Role::all();
        return view('user.role', compact('role'));
    }

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
        try {
            $request->validate(
                [
                    'firstname' => 'required|string|max:50',
                    'lastname' => 'nullable|string|max:50',
                    'nip' => 'required|string|max:25',
                    'email' => 'required|string|email|max:70|unique:users',
                    'password' => 'required|min:8|confirmed',
                    'phone_number' => 'required|numeric|digits_between:10,15',
                    'role_id_role' => 'required|exists:role,id_role',
                    'position_id_position' => 'required|exists:position,id_position',
                    'parent_id' => 'required',
                    'parent_type' => 'required',
                    'kode_bagian' => 'nullable|array',
                    'kode_bagian.*' => 'nullable|string',
                ],
                [
                    'firstname.required' => 'Nama depan wajib diisi.',
                    'firstname.max' => 'Nama depan tidak boleh lebih dari 50 karakter.',
                    'lastname.required' => 'Nama belakang wajib diisi.',
                    'lastname.max' => 'Nama belakang tidak boleh lebih dari 50 karakter.',
                    'nip.required' => 'nip wajib diisi.',
                    'nip.max' => 'nip tidak boleh lebih dari 25 karakter.',
                    'nip.unique' => 'nip sudah digunakan, silakan pilih yang lain.',
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid.',
                    'email.max' => 'Email tidak boleh lebih dari 70 karakter.',
                    'email.unique' => 'Email sudah digunakan, silakan gunakan email lain.',
                    'password.required' => 'Password wajib diisi.',
                    'password.min' => 'Password harus minimal 8 karakter.',
                    'password.confirmed' => 'Konfirmasi password tidak sesuai.',
                    'phone_number.required' => 'Nomor telepon wajib diisi.',
                    'phone_number.numeric' => 'Nomor telepon harus berupa angka.',
                    'phone_number.digits_between' => 'Nomor telepon harus antara 10 hingga 15 digit.',
                    'role_id_role.required' => 'Role wajib dipilih.',
                    'role_id_role.exists' => 'Role yang dipilih tidak valid.',
                    'position_id_position.required' => 'Posisi wajib dipilih.',
                    'position_id_position.exists' => 'Posisi yang dipilih tidak valid.',
                    'parent_id.required' => 'Bagian wajib dipilih.',
                    'parent_type.required' => 'Tipe bagian wajib diisi.',
                ],
            );

            $bagian = $request->parent_id;
            $type = $request->parent_type;

            $direktur = $divisi = $department = $section = $unit = null;
            if ($type == 'director') {
                $direktur = $bagian;
            } elseif ($type == 'divisi') {
                $direktur = Divisi::where('id_divisi', $bagian)->value('director_id_director');
                $divisi = $bagian;
            } elseif ($type == 'department') {
                $direktur = Department::where('id_department', $bagian)->value('director_id_director');
                $divisi = Department::where('id_department', $bagian)->value('divisi_id_divisi') ?? null;
                $department = $bagian;
            } elseif ($type == 'section') {
                $department = Section::where('id_section', $bagian)->value('department_id_department');
                $direktur = Department::where('id_department', $department)->value('director_id_director');
                $divisi = Department::where('id_department', $department)->value('divisi_id_divisi') ?? null;
                $section = $bagian;
            } elseif ($type == 'unit') {
                $section = Unit::where('id_unit', $bagian)->value('section_id_section') ?? null;
                $department = Unit::where('id_unit', $bagian)->value('department_id_department') ?? (Section::where('id_section', $section)->value('department_id_department') ?? null);
                $divisi = Department::where('id_department', $department)->value('divisi_id_divisi') ?? (Section::where('id_section', $section)->value('divisi_id_divisi') ?? null);
                $direktur = Department::where('id_department', $department)->value('director_id_director');
                $unit = $bagian;
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
                'role_id_role' => $request->role_id_role,
                'position_id_position' => $request->position_id_position,
                'director_id_director' => $direktur,
                'divisi_id_divisi' => $divisi,
                'department_id_department' => $department,
                'section_id_section' => $section,
                'unit_id_unit' => $unit,
                'kode_bagian' => $kodeBagian,
            ]);

            return redirect()->route('user.manage')->with('success', 'User berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage() . ' di baris ' . $e->getLine())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage() . ' di baris ' . $e->getLine())
                ->withInput();
        }
    }

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

        if ($request->has('kode_bagian')) {
            $kodeBagian = array_filter($request->kode_bagian, function ($value) {
                return !empty($value);
            });

            $user->kode_bagian = !empty($kodeBagian) ? implode(';', $kodeBagian) : null;
        }

        $bagian = $request->parent_id;
        $type = $request->parent_type;
        if ($bagian && $type) {
            if ($type == 'director') {
                $user->director_id_director = $bagian;
                $user->divisi_id_divisi = null;
                $user->department_id_department = null;
                $user->section_id_section = null;
                $user->unit_id_unit = null;
            } elseif ($type == 'divisi') {
                $user->director_id_director = Divisi::where('id_divisi', $bagian)->value('director_id_director');
                $user->divisi_id_divisi = $bagian;
                $user->department_id_department = null;
                $user->section_id_section = null;
                $user->unit_id_unit = null;
            } elseif ($type == 'department') {
                $user->director_id_director = Department::where('id_department', $bagian)->value('director_id_director');
                $user->divisi_id_divisi = Department::where('id_department', $bagian)->value('divisi_id_divisi') ?? null;
                $user->department_id_department = $bagian;
                $user->section_id_section = null;
                $user->unit_id_unit = null;
            } elseif ($type == 'section') {
                $user->department_id_department = Section::where('id_section', $bagian)->value('department_id_department');
                $user->director_id_director = Department::where('id_department', $user->department_id_department)->value('director_id_director');
                $user->divisi_id_divisi = Department::where('id_department', $user->department_id_department)->value('divisi_id_divisi') ?? null;
                $user->section_id_section = $bagian;
                $user->unit_id_unit = null;
            } elseif ($type == 'unit') {
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

    public function import(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_user' => ['required', 'mimes:xlsx'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            set_time_limit(0);
            ini_set('max_execution_time', 0);

            $file = $request->file('file_user');

            DB::beginTransaction();

            try {
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();

                $data = $sheet->toArray(null, false, true, true);

                $insert = [];

                if (count($data) > 1) {
                    foreach ($data as $baris => $value) {
                        if ($baris > 6) {
                            if (empty($value['A']) && empty($value['C']) && empty($value['D']) && empty($value['E']) && empty($value['F']) && empty($value['G']) && empty($value['H'])) {
                                continue;
                            }

                            $levelOrg = trim($value['G']);
                            $fullLengthOrg = strtolower($levelOrg);
                            $type = strtolower(strtok($levelOrg, ' '));

                            switch ($type) {
                                case 'director':
                                case 'direktur':
                                    $bagian = Director::where('name_director', 'like', '%' . trim(substr($levelOrg, strlen($type))) . '%')->value('id_director');
                                    break;
                                case 'divisi':
                                case 'division':
                                    $bagian = Divisi::where('nm_divisi', 'like', '%' . trim(substr($levelOrg, strlen($type))) . '%')->value('id_divisi');
                                    break;
                                case 'department':
                                case 'departemen':
                                case 'departmen':
                                    $bagian = Department::where('name_department', 'like', '%' . trim(substr($levelOrg, strlen($type))) . '%')->value('id_department');
                                    break;
                                case 'section':
                                case 'bagian':
                                    $bagian = Section::where('name_section', 'like', '%' . trim(substr($levelOrg, strlen($type))) . '%')->value('id_section');
                                    break;
                                case 'unit':
                                    $bagian = Unit::where('name_unit', 'like', '%' . trim(substr($levelOrg, strlen($type))) . '%')->value('id_unit');
                                    break;
                                default:
                                    $bagian = null;
                                    break;
                            }

                            if (!$bagian) {
                                $models = [
                                    'Director' => ['model' => Director::class, 'column' => 'name_director', 'id' => 'id_director'],
                                    'Divisi' => ['model' => Divisi::class, 'column' => 'nm_divisi', 'id' => 'id_divisi'],
                                    'Department' => ['model' => Department::class, 'column' => 'name_department', 'id' => 'id_department'],
                                    'Section' => ['model' => Section::class, 'column' => 'name_section', 'id' => 'id_section'],
                                    'Unit' => ['model' => Unit::class, 'column' => 'name_unit', 'id' => 'id_unit'],
                                ];

                                foreach ($models as $className => $meta) {
                                    $found = $meta['model']::where($meta['column'], 'like', '%' . $fullLengthOrg . '%')->value($meta['id']);

                                    if ($found) {
                                        $bagian = $found;
                                        $type = strtolower($className);
                                        break;
                                    }
                                }
                            }

                            $roleMap = [
                                'staff' => 2,
                                'staf' => 2,
                                'superadmin' => 1,
                                'super admin' => 1,
                            ];

                            $input = strtolower(trim($value['H']));
                            if ($input == '') {
                                $role = null;
                            } else {
                                $role = $roleMap[$input] ?? 3;
                            }

                            $direktur = $divisi = $department = $section = $unit = null;

                            if ($type == 'director' || $type == 'direktur') {
                                $direktur = $bagian;
                            } elseif ($type == 'divisi' || $type == 'division') {
                                $direktur = Divisi::where('id_divisi', $bagian)->value('director_id_director');
                                $divisi = $bagian;
                            } elseif ($type == 'department' || $type == 'departemen' || $type == 'departmen') {
                                $direktur = Department::where('id_department', $bagian)->value('director_id_director');
                                $divisi = Department::where('id_department', $bagian)->value('divisi_id_divisi') ?? null;
                                $department = $bagian;
                            } elseif ($type == 'section' || $type == 'bagian') {
                                $department = Section::where('id_section', $bagian)->value('department_id_department');
                                $direktur = Department::where('id_department', $department)->value('director_id_director');
                                $divisi = Department::where('id_department', $department)->value('divisi_id_divisi') ?? null;
                                $section = $bagian;
                            } elseif ($type == 'unit') {
                                $department = Unit::where('id_unit', $bagian)->value('department_id_department') ?? null;
                                $section = Unit::where('id_unit', $bagian)->value('section_id_section') ?? null;
                                $direktur = Department::where('id_department', $department)->value('director_id_director');
                                $divisi = Department::where('id_department', $department)->value('divisi_id_divisi') ?? null;
                                $unit = $bagian;
                            }

                            $positions = Position::all()->mapWithKeys(function ($pos) {
                                if (strpos($pos->nm_position, ') ') !== false) {
                                    $trim = trim(explode(')', $pos->nm_position, 2)[1]);
                                    $cleaned = Str::replace('.', '', $trim);
                                } else {
                                    $cleaned = $pos->nm_position;
                                }

                                return [strtolower($cleaned) => $pos];
                            });

                            $inputPos = strtolower(Str::replace('.', '', trim($value['H'])));

                            $positionMap = [
                                'm' => 'manager',
                                'gm' => 'general manager',
                                'sm' => 'senior manager',
                                'spv' => 'supervisor',
                                'staf' => 'staff',
                            ];

                            $inputPos2 = $positionMap[$inputPos] ?? $inputPos;

                            $positionKey = $inputPos2;

                            if (!isset($positions[$positionKey])) {
                                $positionKey = $this->fuzzyMatch($inputPos2, $positions->keys(), 3);
                            }

                            $position = $positionKey ? $positions[$positionKey]->id_position : null;
                            $insert[] = [
                                'firstname' => $value['A'],
                                'lastname' => !empty($value['B']) ? $value['B'] : null,
                                'nip' => $value['C'],
                                'email' => $value['D'],
                                'password' => Hash::make($value['E']),
                                'phone_number' => $value['F'],
                                'role_id_role' => $role,
                                'position_id_position' => $position,
                                'director_id_director' => $direktur,
                                'divisi_id_divisi' => $divisi,
                                'department_id_department' => $department,
                                'section_id_section' => $section,
                                'unit_id_unit' => $unit,
                                'created_at' => now(),
                            ];
                        }
                    }

                    if (count($insert) === 0) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Tidak ada data yang diimport',
                        ]);
                    }

                    collect($insert)
                        ->chunk(50)
                        ->each(function ($chunk) {
                            User::insert($chunk->toArray());
                        });

                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data berhasil diimport',
                    ]);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data yang diimport',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                ]);
            }
        }

        return response()->json(
            [
                'status' => false,
                'message' => 'Akses tidak valid',
            ],
            400,
        );
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

    private function fuzzyMatch($input, $list, $threshold = 3)
    {
        $best = null;
        $bestDistance = 999;

        foreach ($list as $key) {
            $dist = levenshtein($input, $key);
            if ($dist < $bestDistance) {
                $bestDistance = $dist;
                $best = $key;
            }
        }

        return $bestDistance <= $threshold ? $best : null;
    }
}
