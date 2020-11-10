<?php

namespace Modules\ApplicationAuth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Modules\ApplicationAuth\Entities\User;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !($user = $this->user()) ||
               !($user instanceof User);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'min:8',
                'max:10485760',
                'confirmed',
            ],
            'reset_password_token' => [
                'required',
                'numeric',
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
            'email' => ucfirst(trans('application-auth::user.email')),
            'password' => ucfirst(trans('application-auth::user.password')),
            'password_confirmation' => ucfirst(trans('application-auth::user.password_confirmation')),
            'reset_password_token' => ucfirst(trans('application-auth::passwords.token')),
        ];
    }
}
