<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeiboOtherData extends Model
{
    protected $fillable = [
        'date',
        'type',
        'account_id',

        'comment_count',
        'effective_chat_count',
        'm_c_count',
        'm_c_turn_count',
        'first_target',
        'account_target',
    ];
}
