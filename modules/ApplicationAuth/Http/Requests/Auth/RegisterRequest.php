<?php

namespace Modules\ApplicationAuth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'between:3,255',
            ],
            'email' => [
                'required',
                'unique:users,email',
                'email:strict,spoof,dns',
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
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => trans('application-auth::register.email_exists'),
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
            'name' => ucfirst(trans('application-auth::user.name')),
            'email' => ucfirst(trans('application-auth::user.email')),
            'password' => ucfirst(trans('application-auth::user.password')),
            'password_confirmation' => ucfirst(trans('application-auth::user.password_confirmation')),
        ];
    }

    
}
