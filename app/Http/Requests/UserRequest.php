<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
                'required', 'min:3'
            ],
            'email' => [
                'required', 'email', Rule::unique((new User)->getTable())->ignore($this->route()->user->id ?? null)
            ],
            'password' => [
                $this->route()->user ? 'nullable' : 'required', 'confirmed', 'min:6'
            ],
            'roles' => [
                'nullable'
            ],
            'api' => ['nullable'],
            "last_name" => ['nullable'],
            "user_name" => ['nullable'],
            "phrase" => ['nullable'],
            "birth_date" => ['nullable'],
            "gender" =>['nullable'],
            "state" => ['nullable'],
            "city" => ['nullable'],
            "newsletter_email" => ['nullable'],
            "terms_and_conditions" => ['nullable'],
            "image" => ['nullable']
        ];
    }
}
