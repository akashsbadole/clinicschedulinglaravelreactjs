<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiagnosisStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'encounter_id' => 'required|exists:encounters,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'diagnosis_type' => 'required|string|in:Primary,Secondary,Differential,Provisional',
            'clinical_description' => 'required|string|max:2000',
            'date_of_diagnosis' => 'required|date',
            'diagnosis_status' => 'required|string|in:active,resolved,chronic',
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