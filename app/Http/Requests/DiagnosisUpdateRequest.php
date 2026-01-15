<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiagnosisUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id' => 'sometimes|exists:doctors,id',
            'diagnosis_type' => 'sometimes|string|in:Primary,Secondary,Differential,Provisional',
            'clinical_description' => 'sometimes|string|max:2000',
            'date_of_diagnosis' => 'sometimes|date',
            'diagnosis_status' => 'sometimes|string|in:active,resolved,chronic',
            'supporting_evidence' => 'nullable|string|max:2000',
            'is_confirmed' => 'nullable|boolean',
            'icd10_codes' => 'nullable|array',
            'icd10_codes.*' => 'exists:icd10_codes,id',
        ];
    }

    public function messages()
    {
        return [
            'diagnosis_type.in' => 'The diagnosis type must be one of: Primary, Secondary, Differential, Provisional',
            'diagnosis_status.in' => 'The diagnosis status must be one of: active, resolved, chronic',
        ];
    }
}