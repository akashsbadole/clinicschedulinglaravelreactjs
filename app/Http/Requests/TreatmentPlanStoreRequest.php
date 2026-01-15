<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TreatmentPlanStoreRequest extends FormRequest
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
            'plan_type' => 'required|string|in:Acute,Chronic,Preventive,Rehabilitation',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'goals' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:2000',
            'status' => 'required|string|in:active,completed,discontinued',
            'medication_orders' => 'nullable|array',
            'medication_orders.*.medication_id' => 'required_with:medication_orders|exists:medications,id',
            'medication_orders.*.dosage' => 'required_with:medication_orders|string|max:100',
            'medication_orders.*.frequency' => 'required_with:medication_orders|string|max:100',
            'medication_orders.*.route' => 'nullable|string|max:50',
            'medication_orders.*.duration' => 'nullable|string|max:100',
            'medication_orders.*.instructions' => 'nullable|string|max:500',
            'medication_orders.*.quantity' => 'nullable|integer|min:1',
            'medication_orders.*.refills' => 'nullable|integer|min:0',
            'medication_orders.*.status' => 'nullable|string|in:active,completed,discontinued',
            'medication_orders.*.start_date' => 'nullable|date',
            'medication_orders.*.end_date' => 'nullable|date|after:medication_orders.*.start_date',
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