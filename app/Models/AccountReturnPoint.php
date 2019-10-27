<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountReturnPoint extends Model
{
    protected $fillable = [
        'name',
        'rebate',
        'keyword',
        'form_type',
        'is_default',
    ];


}
