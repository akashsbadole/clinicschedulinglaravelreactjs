<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcedureStoreRequest extends FormRequest
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
            'performed_by' => 'nullable|exists:users,id',
            'procedure_catalog_id' => 'required|exists:procedure_catalog,id',
            'scheduled_time' => 'nullable|date',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'status' => 'required|string|in:scheduled,in-progress,completed,cancelled',
            'indication' => 'nullable|string|max:2000',
            'findings' => 'nullable|string|max:2000',
            'complications' => 'nullable|string|max:2000',
            'follow_up_instructions' => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'status.in' => 'The status must be one of: scheduled, in-progress, completed, cancelled',
            'end_time.after' => 'The end time must be after the start time',
        ];
    }
}