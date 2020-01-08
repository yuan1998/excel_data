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
        'is_default',
        'type',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id', 'id');
    }

    public function spendData()
    {
        return $this->hasMany(SpendData::class, 'account_id', 'id');
    }

    public function formData()
    {
        return $this->hasMany(FormData::class, 'account_id', 'id');
    }


}
