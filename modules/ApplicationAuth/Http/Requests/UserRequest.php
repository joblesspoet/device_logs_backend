<?php

namespace Modules\ApplicationAuth\Http\Requests;

use Illuminate\Support\Facades\Config;
use Modules\ApplicationAuth\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $locales = array_unique(Config::get('application-auth.locales', []));
        if (empty($locales)) {
            $locales = array_unique(
                [
                    Config::get('app.locale'),
                    Config::get('app.fallback_locale'),
                ]
            );

            if (empty($locales)) {
                $locales = ['en'];
            }
        }

        return [
            'name' => [
                'sometimes',
                'string',
                'between:3,255',
            ],
            'email' => [
                'sometimes',
                'string',
                'email:strict,spoof,dns',
            ],
            'locale' => [
                'sometimes',
                'nullable',
                'in:' . implode(',', $locales),
            ],
        ];
    }

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
            'locale' => ucfirst(trans('application-auth::user.locale')),
        ];
    }
}
