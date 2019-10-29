<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountData extends Model
{
    protected $fillable = [
        'name',
        'rebate',
        'channel_id',
        'keyword',
        'crm_keyword',
    ];


}
