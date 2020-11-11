<?php

namespace App\Http\Requests\API\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'reset_password_token' => ['required', 'numeric',
            function($input, $value, $fail){
                if(!User::where($input, $value)->first())
                $fail('Invalid Token!');
            }],
        ];
    }
}
