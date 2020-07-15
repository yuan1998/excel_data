<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataOriginRequest extends FormRequest
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
            'title'          => 'required',
            'file_name'      => 'required',
            'data_field'     => 'required',
            'property_field' => 'required',
            'data_type'      => 'required',
            'channel_id'     => 'required',
        ];
    }
}
