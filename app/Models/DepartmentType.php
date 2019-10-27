<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Model|Collection|static[]|static|null find($departmentId)
 */
class DepartmentType extends Model
{
    protected $fillable = [
        'title'
    ];

    public function archives()
    {
        return $this->morphToMany(ArchiveType::class, 'model', 'has_archives', 'model_id', 'archive_id');
    }

    public function projects()
    {
        return $this->hasMany(ProjectType::class, 'department_id', 'id');
    }

}
