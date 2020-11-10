<?php

namespace Modules\ApplicationAuth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Modules\ApplicationAuth\Entities\User;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return ($user = $this->user()) &&
               ($user instanceof User);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_password' => [
                'required',
                'string',
                'max:10485760',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:10485760',
                'confirmed',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'old_password' => ucfirst(trans('application-auth::change_password.old_password')),
            'password' => ucfirst(trans('application-auth::change_password.password')),
            'password_confirmation' => ucfirst(trans('application-auth::change_password.password_confirmation')),
        ];
    }
}
