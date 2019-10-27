<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediumType extends Model
{

    protected $fillable = [
        'title',
        'keyword',
    ];


    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'channel_has_medium', 'medium_id', 'channel_id');
    }


}
