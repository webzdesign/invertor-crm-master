<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $array = [];

        if (request()->method() == 'PUT') {
            $id = decrypt($this->id);
            $array['unique_number'] = "required|unique:products,unique_number,{$id}";
        } else {
            $array['unique_number'] = "required|unique:products,unique_number";
        }

            $array['category'] = 'required';
            $array['name'] = 'required';
            $array['pprice'] = 'required|numeric';
            $array['sprice'] = 'required|numeric';

            return $array;
    }

    public function messages(): array
    {
        return [
            'unique_number.required' => 'Product number is required.',
            'unique_number.unique' => 'This product number is already exists.',
            'category.required' => 'Select a category.',
            'name.required' => 'Product name is required.',
            'pprice.required' => 'Purchase price is required.',
            'pprice.numeric' => 'Enter valid price format.',
            'sprice.required' => 'Sales price is required',
            'sprice.numeric' => 'Enter valid price format.',
        ];
    }
}
