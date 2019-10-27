<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveType extends Model
{
    protected $fillable = [
        'title'
    ];
    public $timestamps = false;

    public function projects()
    {
        return $this->morphedByMany(ProjectType::class, 'model', 'has_archives', 'archive_id', 'model_id');
    }

    public function department()
    {
        return $this->morphedByMany(DepartmentType::class, 'model', 'has_archives', 'archive_id', 'model_id');

    }
}
