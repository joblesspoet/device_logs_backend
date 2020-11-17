<?php

namespace App\Http\Requests\Admin\Device;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'status' => 'required',
            'device_name' => 'required',
            'device_model' => 'required',
            'device_version' => 'required',
            'device_picture' => 'required',
        ];
    }
}
