<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            return ['name' => "required|unique:categories,name,{$id}"];
        } else {
            return ['name' => "required|unique:categories,name"];
        }
    }

    public function messages(): array
    {
        return ['name.required' => 'Name is required.', 'name.unique' => 'This category is already exists.'];
    }
}
