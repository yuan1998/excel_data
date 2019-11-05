<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DispatchRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type'           => 'required|in:zx,kq',
            'keyword'        => 'required|string',
            'rule_name'      => 'required|string',
            'all_day'        => 'required|boolean',
            'dispatch_open'  => 'required|boolean',
            'dispatch_users' => 'array',
            'start_time'     => 'required|string',
            'end_time'       => 'required|string',
            'order'          => 'integer',

        ];
    }
}
