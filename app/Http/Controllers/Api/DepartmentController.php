<?php

namespace App\Http\Controllers\Api;

use App\Models\DepartmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{

    public function departmentArchives(Request $request)
    {
        $departmentId = $request->get('q');
        $projectId    = $request->get('id', null);

        $department = DepartmentType::find($departmentId);
        if (!$department) {
            return [];
        }
        
        $query = $department->archives()->doesntHave('projects');
        if ($projectId) {
            $query->orWhereHas('projects', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }

        $data = $query->get(['id', DB::raw('title as text')]);

        return $data;
    }
}
