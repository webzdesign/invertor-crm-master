<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
                'name'  => "required|unique:roles,name,{$id},id,deleted_at,NULL",
            ];
        } else {
            return [
                'name'  => 'required|unique:roles,name,NULL,id,deleted_at,NULL',
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required'     => 'Role name is required.',
            'name.unique'       => 'Role name already exist.'
        ];
    }
}
