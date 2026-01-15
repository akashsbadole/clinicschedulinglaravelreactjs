<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Icd10CodeStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:20|unique:icd10_codes,code',
            'description' => 'required|string|max:1000',
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