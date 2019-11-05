<?php

namespace App\Models;

use App\Clients\ZxClient;
use Illuminate\Database\Eloquent\Model;

class ArchiveType extends Model
{
    protected $fillable = [
        'title',
        'data_id',
        'data_parent_id',
        'level',
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


    public static function GrabCrmArchiveData()
    {
        $dom  = ZxClient::tempSearchApi(['phone' => 123]);
        $data = $dom->find('#TempCustSearchIndex-index-select-ztreetree_0 li');
        foreach ($data as $item) {
            $pid   = $item->getAttribute('data-pid');
            $id    = $item->getAttribute('data-id');
            $level = $item->getAttribute('data-level');
            $title = $item->innerHtml;

            static::updateOrCreate([
                'title' => $title,
            ], [
                'data_id'        => $id,
                'data_parent_id' => $pid,
                'level'          => $level
            ]);
        }
        return count($data);
    }
}
