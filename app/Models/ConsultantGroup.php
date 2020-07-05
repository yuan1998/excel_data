<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultantGroup extends Model
{
    protected $fillable = [
        'title',
    ];


    public function consultants()
    {
        return $this->belongsToMany(Consultant::class, 'consultant_group_id', 'group_id', 'consultant_id');
    }
}
