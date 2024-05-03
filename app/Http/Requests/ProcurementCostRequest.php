<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcurementCostRequest extends FormRequest
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
            return  [
                'product' => "required|unique:procurement_costs,product_id,{$id}",
                "category" => "required",
                "base_price" => "required|numeric|min:0"
                    
                ];
        } else {
            return [
                'product' => "required|unique:procurement_costs,product_id",
                "category" => "required",
                "base_price" => "required|numeric|min:0"
            ];
        }
    }

    public function messages(): array
    {
        return [
            'product.required' => 'Select a product.', 
            'product.unique' => 'Cost for this product is already added.',
            "category.required" => "Select a category.",
            "base_price.required" => "Enter base price.",
            "base_price.numeric" => "Enter valid format.",
            "base_price.min" => "Base price can't be less than 0.",
        ];
    }
}
