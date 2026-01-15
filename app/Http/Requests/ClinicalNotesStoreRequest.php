<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClinicalNotesStoreRequest extends FormRequest
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
            'note_type' => 'required|string|in:SOAP,Progress,Discharge,Consultation,Procedure,Other',
            'subjective' => 'nullable|string|max:2000',
            'objective' => 'nullable|string|max:2000',
            'assessment' => 'nullable|string|max:2000',
            'plan' => 'nullable|string|max:2000',
            'clinical_impression' => 'nullable|string|max:1000',
            'is_confidential' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'note_type.in' => 'The note type must be one of: SOAP, Progress, Discharge, Consultation, Procedure, Other',
        ];
    }
}