<?php

namespace App\Http\Requests;

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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (request()->method() == 'PUT') {
            $id = decrypt($this->id);
            return [
                'name'                  => 'required',
                'email'                 => "required|email|unique:users,email,{$id},id,deleted_at,NULL",
                'role'                  => 'required',
                'confirm_password'      => 'same:password',
                'country'               => "required",
                'state'                 => "required",
                'city'                  => "required",
                'address_line_1'        => "required",
                'address_line_2'        => "required",
            ];
        } else {
            return [
                'name'                  => 'required',
                'email'                 => "required|email|unique:users,email,NULL,id,deleted_at,NULL",
                'role'                  => 'required',
                'password'              => 'required|min:8|max:16',
                'confirm_password'      => 'same:password',
                'country'               => "required",
                'state'                 => "required",
                'city'                  => "required",
                'address_line_1'        => "required",
                'address_line_2'        => "required",
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required'                 => 'Name is required.',
            'email.required'                => 'Email is required.',
            'email.email'                   => 'Email format is invalid.',
            'email.unique'                  => 'This email is already exists.',
            'role.required'                 => 'Select a role.',
            'password.required'             => 'Create a Password.',
            'password.min'                  => 'Minimum length should be 8 characters.',
            'password.max'                  => 'Maximum length should be 16 characters.',
            'confirm_password.required'     => 'Both Password field must be matched.',
            'address_line_1.required'       => 'Address Line 1 is required.',
            'address_line_2.required'       => 'Address Line 2 is required.',
            'country.required'              => 'Select a Country.',
            'state.required'                => 'Select a State.',
            'city.required'                 => 'Select a City.',
        ];
    }
}
