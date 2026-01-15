<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Icd10CodeUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $icd10CodeId = $this->route('icd10_code');
        
        return [
            'code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('icd10_codes', 'code')->ignore($icd10CodeId),
            ],
            'description' => 'sometimes|string|max:1000',
            'category' => 'nullable|string|max:100',
            'chapter' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'code.unique' => 'An ICD-10 code with this code already exists',
        ];
    }
}