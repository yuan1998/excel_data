<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportExcelRequest extends FormRequest
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
            'department_id' => 'required',
            'channel_id'    => 'required',
            'dates'         => 'required',
        ];
    }
}
