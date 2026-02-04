<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Director;
use App\Models\Divisi;
use App\Models\Department;
use App\Models\Position;
use App\Models\Section;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\{Validator, DB};
use Illuminate\Support\Str;
use Symfony\Component\Translation\LocaleSwitcher;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    // public function create(): View
    // {
    //     return view('auth.register');
    // }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     */
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

            //dd($direktur, $divisi, $department, $section, $unit);

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

    public function import_ajax(Request $request)
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
            // Disable PHP execution time limit
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
                                continue; // Lewatkan baris kosong
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

                            // Global fallback: only if $bagian is still null
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
                                        $bagian = $found; // ID
                                        $type = strtolower($className); // model name
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

                            // Normalize input (remove dot, lowercase, trim)
                            $inputPos = strtolower(Str::replace('.', '', trim($value['H'])));

                            // Map abbreviations to full names
                            $positionMap = [
                                'm' => 'manager',
                                'gm' => 'general manager',
                                'sm' => 'senior manager',
                                'spv' => 'supervisor',
                                'staf' => 'staff',
                            ];

                            // Expand abbreviations
                            $inputPos2 = $positionMap[$inputPos] ?? $inputPos;

                            // Try direct match first
                            $positionKey = $inputPos2;

                            // If direct match fails → fuzzy match
                            if (!isset($positions[$positionKey])) {
                                $positionKey = $this->fuzzyMatch($inputPos2, $positions->keys(), 3); // threshold 2–3 is good
                            }

                            $position = $positionKey ? $positions[$positionKey]->id_position : null;
                            //dd($position);
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

                    // if (count($insert) > 0) {
                    //     collect($insert)->chunk(50)->each(function ($chunk) {
                    //         DB::transaction(function () use ($chunk) {
                    //             User::insert($chunk->toArray());
                    //         });
                    //     });
                    // } else {
                    //     return response()->json([
                    //         'status' => false,
                    //         'message' => 'Tidak ada data yang diimport'
                    //     ]);
                    // }
                    //dd($insert);
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
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Tidak ada data yang diimport',
                    ]);
                }
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

    function fuzzyMatch($input, $list, $threshold = 3)
    {
        $best = null;
        $bestDistance = 999;

        foreach ($list as $key) {
            $dist = levenshtein($input, $key);
            //dd($key, $dist);
            if ($dist < $bestDistance) {
                $bestDistance = $dist;
                $best = $key;
            }
        }
        //dd($best, $bestDistance);
        return $bestDistance <= $threshold ? $best : null;
    }
}
