<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TreatmentPlanUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id' => 'sometimes|exists:doctors,id',
            'plan_type' => 'sometimes|string|in:Acute,Chronic,Preventive,Rehabilitation',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'goals' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:2000',
            'status' => 'sometimes|string|in:active,completed,discontinued',
        ];
    }

    public function messages()
    {
        return [
            'plan_type.in' => 'The plan type must be one of: Acute, Chronic, Preventive, Rehabilitation',
            'status.in' => 'The status must be one of: active, completed, discontinued',
            'end_date.after' => 'The end date must be after the start date',
        ];
    }
}