<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\BagianKerja;
use App\Models\Director;
use App\Models\Department;
use App\Models\Section;
use App\Models\Unit;

class UserManageApiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter sort (default 'asc') dan search (optional)
        $sortOrder = $request->query('sort', 'asc');
        $searchTerm = $request->query('search');

        // Query awal dengan eager loading
        $query = User::with(['role', 'divisi', 'position']);

        // Jika ada pencarian, filter berdasarkan firstname atau lastname
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('firstname', 'like', '%' . $searchTerm . '%')
                  ->orWhere('lastname', 'like', '%' . $searchTerm . '%');
            });
        }

        // Urutkan berdasarkan firstname
        $query->orderBy('firstname', $sortOrder);

        // Ambil data semua user (tanpa pagination, untuk Android lebih praktis)
        $users = $query->get();

        $users_count = $users->count();

        // Kembalikan response JSON
        return response()->json([
            'status' => true,
            'message' => 'Data users berhasil diambil',
            'data_count' => $users_count,
            'data' => $users
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['success' => 'User berhasil dihapus'], 200);
    }

    public function bagianKerja(Request $request)
    {
        // Ambil parameter query
        $sortOrder = $request->query('sort', 'asc');
        $searchTerm = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);

        // Query awal
        $query = BagianKerja::query();

        // Jika ada pencarian, filter berdasarkan kode_bagian atau nama_bagian
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('kode_bagian', 'like', '%' . $searchTerm . '%')
                  ->orWhere('nama_bagian', 'like', '%' . $searchTerm . '%');
            });
        }

        // Urutkan berdasarkan nama_bagian
        $query->orderBy('nama_bagian', $sortOrder);

        // Ambil data menggunakan pagination
        $paginator = $query->paginate($perPage)->appends($request->all());

        // Kembalikan response JSON
        return response()->json([
            'status' => true,
            'message' => 'Data bagian kerja berhasil diambil',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    public function destroyBagianKerja($id)
    {
        $bagian = BagianKerja::find($id);

        if (!$bagian) {
            return response()->json([
                'status' => false,
                'message' => 'Bagian kerja tidak ditemukan',
            ], 404);
        }

        $bagian->update([
            'is_active' => false,
        ]);

        $bagian->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kode bagian kerja berhasil dihapus',
        ]);
    }

    // Struktur organisasi
    public function strukturOrganisasi(Request $request)
    {
        $mainDirector = Director::with([
            'subDirectors.divisi.department.section.unit',
            'subDirectors.divisi.department.unit',
            'subDirectors.department.section.unit',
            'subDirectors.department.unit',
            'divisi.department.section.unit',
            'divisi.department.unit',
            'department.section.unit',
            'department.unit',
        ])->where('is_main', 1)->first();

        if (!$mainDirector) {
            return response()->json([
                'status' => false,
                'message' => 'Struktur organisasi tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Struktur organisasi berhasil diambil',
            'data' => $this->mapDirector($mainDirector),
        ]);
    }

    private function mapDirector(Director $director): array
    {
        $node = [
            'type' => 'director',
            'id' => $director->id_director,
            'name' => $director->name_director,
            'kode' => $director->kode_director ?? null,
            'children' => [],
        ];

        foreach ($director->subDirectors ?? [] as $subDirector) {
            $node['children'][] = $this->mapDirector($subDirector);
        }

        $departmentIdsFromDivisi = [];

        foreach ($director->divisi ?? [] as $divisi) {
            $node['children'][] = $this->mapDivisi($divisi, $departmentIdsFromDivisi);
        }

        foreach ($director->department ?? [] as $department) {
            if (!in_array($department->id_department, $departmentIdsFromDivisi, true)) {
                $node['children'][] = $this->mapDepartment($department);
            }
        }

        return $node;
    }

    private function mapDivisi(Divisi $divisi, array &$departmentIdsFromDivisi): array
    {
        $node = [
            'type' => 'divisi',
            'id' => $divisi->id_divisi,
            'name' => $divisi->nm_divisi,
            'kode' => $divisi->kode_divisi ?? null,
            'children' => [],
        ];

        foreach ($divisi->department ?? [] as $department) {
            $departmentIdsFromDivisi[] = $department->id_department;
            $node['children'][] = $this->mapDepartment($department);
        }

        return $node;
    }

    private function mapDepartment(Department $department): array
    {
        $node = [
            'type' => 'department',
            'id' => $department->id_department,
            'name' => $department->name_department,
            'kode' => $department->kode_department ?? null,
            'children' => [],
        ];

        $unitIdsFromSection = [];

        foreach ($department->section ?? [] as $section) {
            foreach ($section->unit ?? [] as $unit) {
                $unitIdsFromSection[] = $unit->id_unit;
            }
        }

        foreach ($department->section ?? [] as $section) {
            $node['children'][] = $this->mapSection($section);
        }

        foreach ($department->unit ?? [] as $unit) {
            if (!in_array($unit->id_unit, $unitIdsFromSection, true)) {
                $node['children'][] = $this->mapUnit($unit);
            }
        }

        return $node;
    }

    private function mapSection(Section $section): array
    {
        $node = [
            'type' => 'section',
            'id' => $section->id_section,
            'name' => $section->name_section,
            'kode' => $section->kode_section ?? null,
            'children' => [],
        ];

        foreach ($section->unit ?? [] as $unit) {
            $node['children'][] = $this->mapUnit($unit);
        }

        return $node;
    }

    private function mapUnit(Unit $unit): array
    {
        return [
            'type' => 'unit',
            'id' => $unit->id_unit,
            'name' => $unit->name_unit,
            'kode' => $unit->kode_unit ?? null,
            'children' => [],
        ];
    }
}
