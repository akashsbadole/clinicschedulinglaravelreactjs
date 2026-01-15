<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabOrderStoreRequest extends FormRequest
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
            'priority' => 'required|string|in:routine,urgent,stat',
            'status' => 'required|string|in:ordered,processing,completed,cancelled',
            'clinical_indication' => 'nullable|string|max:1000',
            'specimen_collection_time' => 'nullable|date',
            'results_available_time' => 'nullable|date',
            'lab_tests' => 'nullable|array',
            'lab_tests.*.test_catalog_id' => 'nullable|exists:test_catalog,id',
            'lab_tests.*.test_name' => 'required_with:lab_tests|string|max:255',
            'lab_tests.*.test_code' => 'nullable|string|max:50',
            'lab_tests.*.specimen_type' => 'nullable|string|max:100',
            'lab_tests.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'priority.in' => 'The priority must be one of: routine, urgent, stat',
            'status.in' => 'The status must be one of: ordered, processing, completed, cancelled',
        ];
    }
}