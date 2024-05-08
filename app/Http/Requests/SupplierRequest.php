<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if (request()->method() == 'PUT') {
            $id = decrypt($this->id);
            return [
                'name'                  => 'required',
                'email'                 => "required|email|unique:users,email,{$id},id,deleted_at,NULL",
                'country'               => "required",
                'postal_code'           => "required"
            ];
        } else {
            return [
                'name'                  => 'required',
                'email'                 => "required|email|unique:users,email,NULL,id,deleted_at,NULL",
                'country'               => "required",
                'postal_code'           => "required"
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
            'country.required'              => 'Select a Country.',
            'postal_code.required'          => 'Enter postal code.',
        ];
    }
}
