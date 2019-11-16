<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectType extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'keyword',
        'spend_keyword',
        'title',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(DepartmentType::class, 'department_id', 'id');
    }

    public function archives()
    {
        return $this->morphToMany(ArchiveType::class, 'model', 'has_archives', 'model_id', 'archive_id');
    }

}

