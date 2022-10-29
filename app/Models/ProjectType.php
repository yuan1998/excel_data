<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
    /**
     * 关联 项目
     * @return MorphToMany
     */
    public function fromData()
    {
        return $this->morphedByMany(FormData::class, 'model', 'project_list', 'model_id', 'project_id');
    }

    public static function clearBeforeData() {

        static::query()
            ->with(['fromData'])
            ->doesntHave()
            ->whereNull('pay_date')
            ->orWhere('pay_date' ,'<', $date)
            ->delete();
    }
}

