<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'document_type' => 'required|string|max:100',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => 'required|file|max:10240', // 10MB max
            'is_confidential' => 'nullable|boolean',
            'tags' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'file.max' => 'The document file may not be greater than 10MB',
        ];
    }
}