<?php

namespace App\Http\Controllers;

use App\Models\Director;
use App\Models\Divisi;
use App\Models\Department;
use App\Models\Section;
use App\Models\Unit;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        // Ambil direktur utama beserta relasi
        $mainDirector = Director::with([
            'subDirectors.divisi.department.section.unit',
            'subDirectors.divisi.department.unit',
            'subDirectors.department.section.unit',
            'subDirectors.department.unit',
            'divisi.department.section.unit',
            'divisi.department.unit',
            'department.section.unit',
            'department.unit'
        ])->where('is_main', 1)->first();



        $formatDirector = $mainDirector ? $this->formatDirector($mainDirector) : [
            'text' => ['name' => 'Tidak ada data direktur']
        ];
        $formatDirector['stackChildren'] = true;
        return view('superadmin.organization_manage', compact('mainDirector', 'formatDirector'));
    }
    public function formatDirector($director)
    {
        $type = 'director';
        $id = $director->id_director;
        $name = $director->name_director;
        $kode = $director->kode_director ?? '';
        $deleteUrl = route('organization.delete', ['type' => $type, 'id' => $id]);
        $node = [
            'text' => ['name' => $name],
            'innerHTML' =>
            '<div class="custom-node" data-id="' . $id . '">
                <div class="custom-node-title">' . htmlspecialchars($name) . '</div>
                <div class="custom-node-actions">
                    <button class="custom-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-type="' . $type . '" data-id="' . $id . '" data-name="' . htmlspecialchars($name, ENT_QUOTES) . '" data-kode="' . htmlspecialchars($kode, ENT_QUOTES) . '"><i class="far fa-edit" style="color: white;" alt="Edit"></i>
</button>
                    <button class="custom-btn delete-btn" onclick="confirmDelete(\'' . $deleteUrl . '\')"><i class="far fa-trash-alt" style="color: white;" alt="Delete"></i></button>
                </div>
            </div>',
            'stackChildren' => true,
            'HTMLclass' => 'nodeExample1 director',
            'children' => [],
        ];

        // Sub-directors
        foreach ($director->subDirectors ?? [] as $sub) {
            $node['children'][] = $this->formatDirector($sub);
        }

        // Ambil semua department yang sudah ditangani dalam divisi
        $deptIdsInDivisi = [];

        foreach ($director->divisi ?? [] as $divisi) {
            $type = 'divisi';
            $id = $divisi->id_divisi;
            $name = $divisi->nm_divisi;
            $kode = $divisi->kode_divisi ?? '';
            $deleteUrl = route('organization.delete', ['type' => $type, 'id' => $id]);
            $divisiNode = [
                'innerHTML' =>
                '<div class="custom-node">
                    <div class="custom-node-title">' . htmlspecialchars($name) . '</div>
                    <div class="custom-node-actions">
                        <button class="custom-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-type="' . $type . '" data-id="' . $id . '" data-name="' . htmlspecialchars($name, ENT_QUOTES) . '" data-kode="' . htmlspecialchars($kode, ENT_QUOTES) . '"><i class="far fa-edit" style="color: white;" alt="Edit"></i></button>
                        <button class="custom-btn delete-btn" onclick="confirmDelete(\'' . $deleteUrl . '\')"><i class="far fa-trash-alt" style="color: white;" alt="Delete"></i></button>
                    </div>
                </div>',
                'stackChildren' => true,
                'HTMLclass' => 'nodeExample1 divisi',
                'children' => [],
            ];

            foreach ($divisi->department ?? [] as $department) {
                $deptNode = $this->formatDepartment($department);
                $divisiNode['children'][] = $deptNode;

                // Catat ID-nya agar tidak diproses ulang di bawah director
                $deptIdsInDivisi[] = $department->id_department;
            }

            $node['children'][] = $divisiNode;
        }

        // Department langsung di director (cek apakah sudah ditambahkan di divisi)
        // foreach ($director->department ?? [] as $department) {
        //     if (!in_array($department->id_department, $deptIdsInDivisi)) {
        //         $node['children'][] = $this->formatDepartment($department);
        //     }
        // }
        $directDepartments = [];

        foreach ($director->department ?? [] as $department) {
            if (!in_array($department->id_department, $deptIdsInDivisi)) {
                $directDepartments[] = $this->formatDepartment($department);
            }
        }

        // Jika ada department langsung di bawah director
        if (count($directDepartments) > 0) {
            $pseudoDivisi = [
                'pseudo' => true, // bikin node tidak terlihat
                'children' => $directDepartments
            ];


            $node['children'][] = $pseudoDivisi;
        }
        return $node;
    }

    public function formatDepartment($department)
    {
        $type = 'department';
        $id = $department->id_department;
        $name = $department->name_department;
        $kode = $department->kode_department ?? '';
        $deleteUrl = route('organization.delete', ['type' => $type, 'id' => $id]);
        $deptNode = [
            'text' => ['name' => $name],
            'innerHTML' =>
            '<div class="custom-node">
                <div class="custom-node-title">' . htmlspecialchars($name) . '</div>
                <div class="custom-node-actions">
                    <button class="custom-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-type="' . $type . '" data-id="' . $id . '" data-name="' . htmlspecialchars($name, ENT_QUOTES) . '" data-kode="' . htmlspecialchars($kode, ENT_QUOTES) . '"><i class="far fa-edit" style="color: white;" alt="Edit"></i></button>
                    <button class="custom-btn delete-btn" onclick="confirmDelete(\'' . $deleteUrl . '\')"><i class="far fa-trash-alt" style="color: white;" alt="Delete"></i></button>
                </div>
            </div>',
            'stackChildren' => true,
            'HTMLclass' => 'nodeExample1 department',
            'children' => [],
        ];

        // Step 1: Kumpulkan semua unit_id dari section
        $unitIdsInSection = [];
        foreach ($department->section ?? [] as $section) {
            foreach ($section->unit ?? [] as $unit) {
                $unitIdsInSection[] = $unit->id_unit; // Sesuaikan nama kolom ID unit
            }
        }

        // Step 2: Tambahkan section dan unit-unitnya ke dalam tree
        foreach ($department->section ?? [] as $section) {
            $type = 'section';
            $id = $section->id_section;
            $name = $section->name_section;
            $kode = $section->kode_section ?? '';
            $deleteUrl = route('organization.delete', ['type' => $type, 'id' => $id]);
            $sectionNode = [
                'text' => ['name' => $name],
                'innerHTML' =>
                '<div class="custom-node">
                    <div class="custom-node-title">' . htmlspecialchars($name) . '</div>
                    <div class="custom-node-actions">
                        <button class="custom-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-type="' . $type . '" data-id="' . $id . '" data-name="' . htmlspecialchars($name, ENT_QUOTES) . '" data-kode="' . htmlspecialchars($kode, ENT_QUOTES) . '"><i class="far fa-edit" style="color: white;" alt="Edit"></i></button>
                        <button class="custom-btn delete-btn" onclick="confirmDelete(\'' . $deleteUrl . '\')"><i class="far fa-trash-alt" style="color: white;" alt="Delete"></i></button>
                    </div>
                </div>',
                'stackChildren' => true,
                'HTMLclass' => 'nodeExample1 section',
                'children' => [],
            ];

            foreach ($section->unit ?? [] as $unit) {
                $type = 'unit';
                $id = $unit->id_unit;
                $name = $unit->name_unit;
                $kode = $unit->kode_unit ?? '';
                $deleteUrl = route('organization.delete', ['type' => $type, 'id' => $id]);
                $sectionNode['children'][] = [
                    'text' => ['name' => $name],
                    'innerHTML' =>
                    '<div class="custom-node">
                        <div class="custom-node-title">' . htmlspecialchars($name) . '</div>
                        <div class="custom-node-actions">
                            <button class="custom-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-type="' . $type . '" data-id="' . $id . '" data-name="' . htmlspecialchars($name, ENT_QUOTES) . '" data-kode="' . htmlspecialchars($kode, ENT_QUOTES) . '"><i class="far fa-edit" style="color: white;" alt="Edit"></i></button>
                            <button class="custom-btn delete-btn" onclick="confirmDelete(\'' . $deleteUrl . '\')"><i class="far fa-trash-alt" style="color: white;" alt="Delete"></i></button>
                        </div>
                    </div>',
                    'stackChildren' => true,
                    'HTMLclass' => 'nodeExample1 unit',
                ];
            }

            $deptNode['children'][] = $sectionNode;
        }

        // Step 3: Tambahkan unit yang langsung berada di department
        foreach ($department->unit ?? [] as $unit) {
            if (!in_array($unit->id_unit, $unitIdsInSection)) {
                $type = 'unit';
                $id = $unit->id_unit;
                $name = $unit->name_unit;
                $kode = $unit->kode_unit ?? '';
                $deleteUrl = route('organization.delete', ['type' => $type, 'id' => $id]);
                $deptNode['children'][] = [
                    'text' => ['name' => $name],
                    'innerHTML' =>
                    '<div class="custom-node">
                        <div class="custom-node-title">' . htmlspecialchars($name) . '</div>
                        <div class="custom-node-actions">
                            <button class="custom-btn edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-type="' . $type . '" data-id="' . $id . '" data-name="' . htmlspecialchars($name, ENT_QUOTES) . '" data-kode="' . htmlspecialchars($kode, ENT_QUOTES) . '"><i class="far fa-edit" style="color: white;" alt="Edit"></i></button>
                            <button class="custom-btn delete-btn" onclick="confirmDelete(\'' . $deleteUrl . '\')"><i class="far fa-trash-alt" style="color: white;" alt="Delete"></i></button>
                        </div>
                    </div>',
                    'stackChildren' => true,
                    'HTMLclass' => 'nodeExample1 unit',
                ];
            }
        }

        return $deptNode;
    }




    public function store(Request $request)
    {
        $request->validate($this->rules());

        $type = $request->type;
        $name = $request->name;
        if (!empty($request->kode)) {
            $kode = $request->kode;
        } else {
            $kode = NULL;
        }
        $parent = $request->parent_id;

        // Siapkan parent type & id
        $parentType = null;
        $parentId = null;
        if ($parent) {
            [$parentType, $parentId] = explode('-', $parent);
        }

        switch ($type) {
            case 'Director':
                $director = new Director();
                $director->name_director = $name;
                $director->kode_director = $kode;
                $director->is_main = 0;
                if ($parentType == 'director') {
                    $director->parent_director_id = $parentId;
                }
                $director->save();
                break;

            case 'Divisi':
                $divisi = new Divisi();
                $divisi->nm_divisi = $name;
                $divisi->kode_divisi = $kode;
                if ($parentType == 'director') {
                    $divisi->director_id_director = $parentId;
                }
                $divisi->save();
                break;

            case 'Department':
                $department = new Department();
                $department->name_department = $name;
                $department->kode_department = $kode;
                if ($parentType == 'divisi') {
                    $department->divisi_id_divisi = $parentId;
                    $divisi = Divisi::find($parentId);
                    $department->director_id_director = $divisi?->director_id_director;
                } elseif ($parentType == 'director') {
                    $department->director_id_director = $parentId;
                }
                $department->save();
                break;

            case 'Section':
                $section = new Section();
                $section->name_section = $name;
                if ($parentType == 'department') {
                    $section->department_id_department = $parentId;
                }
                $section->save();
                break;

            case 'Unit':
                $unit = new Unit();
                $unit->name_unit = $name;
                if ($parentType == 'section') {
                    $unit->section_id_section = $parentId;
                } elseif ($parentType == 'department') {
                    $unit->department_id_department = $parentId;
                }
                $unit->save();
                break;
        }

        return redirect()->route('organization.manageOrganization')->with('success', 'User added successfully.');
    }

    private function rules()
    {
        return [
            'type' => 'required|in:Director,Divisi,Department,Section,Unit',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|string'
        ];
    }

    public function update(Request $request, $type, $id)
    {
        $name = $request->input('name');
        if (!empty($request->input('kode'))) {
            $kode = $request->input('kode');
        } else {
            $kode = NULL;
        }
        switch ($type) {
            case 'director':
                $model = Director::findOrFail($id);
                $model->name_director = $name;
                $model->kode_director = $kode;
                break;
            case 'divisi':
                $model = Divisi::findOrFail($id);
                $model->nm_divisi = $name;
                $model->kode_divisi = $kode;
                break;
            case 'department':
                $model = Department::findOrFail($id);
                $model->name_department = $name;
                $model->kode_department = $kode;
                break;
            case 'section':
                $model = Section::findOrFail($id);
                $model->name_section = $name;
                break;
            case 'unit':
                $model = Unit::findOrFail($id);
                $model->name_unit = $name;
                break;
        }
        $model->save();
        return back()->with('success', ucfirst($type) . ' berhasil diupdate');
    }

    public function delete($type, $id)
    {
        switch ($type) {
            case 'director':
                $node = Director::findOrFail($id);
                $node->subDirectors()->delete();
                $node->divisi()->delete();
                $node->department()->delete();
                break;
            case 'divisi':
                $node = Divisi::findOrFail($id);
                $node->department()->delete();
                break;
            case 'department':
                $node = Department::findOrFail($id);
                $node->section()->delete();
                $node->unit()->delete();
                break;
            case 'section':
                $node = Section::findOrFail($id);
                $node->unit()->delete();
                break;
            case 'unit':
                $node = Unit::findOrFail($id);
                break;
        }
        $node->delete();
        return response()->json(['success' => true]);
    }
}
