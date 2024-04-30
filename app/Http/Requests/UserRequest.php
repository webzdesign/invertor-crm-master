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
                'email'                 => 'required|email',
                'phone'                 => "required|unique:users,phone,{$id},id,deleted_at,NULL",
                'role'                  => 'required',
                'password'              => 'confirmed',
            ];
        } else {
            return [
                'name'                  => 'required',
                'email'                 => 'required|email',
                'phone'                 => 'required|unique:users,phone,NULL,id,deleted_at,NULL',
                'role'                  => 'required',
                'password'              => 'required|confirmed|min:8|max:16',
                'password_confirmation' => 'required|min:8|max:16',
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required'                 => 'Name is required.',
            'email.required'                => 'Email is required.',
            'email.email'                   => 'Enter a vaild email address.',
            'phone.required'                => 'Phone number is required.',
            'phone.unique'                  => 'Phone number already exist.',
            'role.required'                 => 'Role is required.',
            'password.required'             => 'Password is required.',
            'password_confirmation.required'=> 'Confirm password is required.',
        ];
    }
}
