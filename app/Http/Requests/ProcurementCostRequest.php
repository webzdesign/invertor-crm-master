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
        $rid = $this->role;

        if (request()->method() == 'PUT') {
            $id = decrypt($this->id);

            return  [
                'product' => ['required', function ($name, $pid, $fail) use ($id, $rid) {
                    if (\App\Models\ProcurementCost::where('id', '!=', $id)->where('product_id', $pid)->where('role_id', $rid)->exists()) {
                        $fail("Cost for this product is already added with this role.");
                    }
                }],
                "category" => "required",
                "role" => "required",
                "base_price" => "required|numeric|min:0",
                "min_sales_price" => "required|numeric|min:0",
                "default_commission_price" => "required|numeric|min:0"
                    
                ];
        } else {
            return [
                'product' => ['required', function ($name, $pid, $fail) use ($rid) {
                    if (\App\Models\ProcurementCost::where('product_id', $pid)->where('role_id', $rid)->exists()) {
                        $fail("Cost for this product is already added.");
                    }
                }],
                "category" => "required",
                "role" => "required",
                "base_price" => "required|numeric|min:0",
                "min_sales_price" => "required|numeric|min:0",
                "default_commission_price" => "required|numeric|min:0"
            ];
        }
    }

    public function messages(): array
    {
        return [
            'product.required' => 'Select a product.', 
            'product.unique' => 'Cost for this product is already added.',
            "category.required" => "Select a category.",
            "role.required" => "Select a role.",

            "base_price.required" => "Enter base price.",
            "base_price.numeric" => "Enter valid format.",
            "base_price.min" => "Base price can't be less than 0.",

            "min_sales_price.required" => "Enter minimum sales price.",
            "min_sales_price.numeric" => "Enter valid format.",
            "min_sales_price.min" => "Minimum sales price can't be less than 0.",

            "default_commission_price.required" => "Enter default commission price.",
            "default_commission_price.numeric" => "Enter valid format.",
            "default_commission_price.min" => "Default commission price can't be less than 0."
        ];
    }
}
