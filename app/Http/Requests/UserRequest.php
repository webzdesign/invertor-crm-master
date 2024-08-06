<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Helper;

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
    public function rules(\Illuminate\Http\Request $request)
    {
        if (request()->method() == 'PUT') {
            $id = decrypt($this->id);

            $validator = [
                'name'                  => 'required',
                'email'                 => "required|email|unique:users,email,{$id},id,deleted_at,NULL",
                'role'                  => 'required',
                'confirm_password'      => 'same:password',
                'country'               => "required",
                'city'                  => "required",
                'phone'                 => "required",
                'address_line_1'        => "required",
                'postal_code'           => "required"
            ];

        } else {
            $validator = [
                'name'                  => 'required',
                'email'                 => "required|email|unique:users,email,NULL,id,deleted_at,NULL",
                'role'                  => 'required',
                'password'              => 'required|min:8|max:16',
                'confirm_password'      => 'same:password',
                'country'               => "required",
                'city'                  => "required",
                'phone'                 => "required",
                'address_line_1'        => "required",
                'postal_code'           => "required"
            ];
        }

        $documents = \App\Models\RequiredDocument::where('role_id', $request->role)->orderBy('sequence', 'ASC')->get();

        if (count($documents) > 0) {
            foreach ($documents as $document) {
                if (isset($request->document[$document->id])) {
    
                    $isRequired = "";
                    
                    if ($document->is_required) {
                        $isRequired = "required|";
                    }    

                    $validator["document.{$document->id}"] = "{$isRequired}max:{$document->maximum_upload_count}";

                    if ($document->allow_only_specific_file_format) {
                        $validator["document.{$document->id}" . '.*'] = "file|max:{$document->maximum_upload_size}|mimes:" . Helper::returnExtensions($document->allowed_file, '', ',');
                    } else {
                        $validator["document.{$document->id}" . '.*'] = "file|max:{$document->maximum_upload_size}";
                    }        
                }                                                        
            }
        }

        return $validator;
    }

    public function messages()
    {
        $validatorMessages = [
            'name.required'                 => 'Name is required.',
            'email.required'                => 'Email is required.',
            'email.email'                   => 'Email format is invalid.',
            'email.unique'                  => 'This email is already exists.',
            'role.required'                 => 'Select a role.',
            'password.required'             => 'Create a Password.',
            'phone.required'                => 'Phone number is required.',
            'password.min'                  => 'Minimum length should be 8 characters.',
            'password.max'                  => 'Maximum length should be 16 characters.',
            'confirm_password.same'         => 'Both password field must be matched.',
            'address_line_1.required'       => 'Address Line 1 is required.',
            'country.required'              => 'Select a Country.',
            'city.required'                 => 'Select a City.',
            'postal_code.required'          => 'Enter postal code.',
        ];

        $request = request();
        $documents = \App\Models\RequiredDocument::where('role_id', $request->role)->orderBy('sequence', 'ASC')->get();

        if (count($documents) > 0) {
            foreach ($documents as $document) {

                if (isset($request->document[$document->id])) {
                            
                    if ($document->is_required) {
                        $validatorMessages["document.{$document->id}" . '.required'] = "Please upload specified document.";
                    }
    
                    $validatorMessages["document.{$document->id}" . '.max'] = "Maximum " . $document->maximum_upload_count . " files can be uploaded.";
    
                    if ($document->allow_only_specific_file_format) {
                        $validatorMessages["document.{$document->id}" . '.*.mimes'] = "Only " . Helper::returnExtensions($document->allowed_file, '.', ',') . " file formats are supported.";
                    }
    
                    $validatorMessages["document.{$document->id}" . '.*.file'] = "Please upload specified document.";
                    $validatorMessages["document.{$document->id}" . '.*.max'] = "Maximum " . Helper::formatBytes($document->maximum_upload_size) . " size of file can be uploaded.";
                }                                                        
            }
        }

        return $validatorMessages;
    }
}
