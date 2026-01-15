<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VitalSignsStoreRequest extends FormRequest
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
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'temperature' => 'nullable|numeric|min:30|max:50',
            'systolic_bp' => 'nullable|integer|min:40|max:300',
            'diastolic_bp' => 'nullable|integer|min:20|max:200',
            'pulse_rate' => 'nullable|integer|min:20|max:250',
            'respiratory_rate' => 'nullable|integer|min:5|max:100',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'height.min' => 'Height must be at least 0 cm',
            'height.max' => 'Height cannot exceed 300 cm',
            'weight.min' => 'Weight must be at least 0 kg',
            'weight.max' => 'Weight cannot exceed 500 kg',
            'temperature.min' => 'Temperature must be at least 30°C',
            'temperature.max' => 'Temperature cannot exceed 50°C',
            'systolic_bp.min' => 'Systolic blood pressure must be at least 40 mmHg',
            'systolic_bp.max' => 'Systolic blood pressure cannot exceed 300 mmHg',
            'diastolic_bp.min' => 'Diastolic blood pressure must be at least 20 mmHg',
            'diastolic_bp.max' => 'Diastolic blood pressure cannot exceed 200 mmHg',
            'pulse_rate.min' => 'Pulse rate must be at least 20 bpm',
            'pulse_rate.max' => 'Pulse rate cannot exceed 250 bpm',
            'respiratory_rate.min' => 'Respiratory rate must be at least 5 breaths/min',
            'respiratory_rate.max' => 'Respiratory rate cannot exceed 100 breaths/min',
            'oxygen_saturation.min' => 'Oxygen saturation must be at least 0%',
            'oxygen_saturation.max' => 'Oxygen saturation cannot exceed 100%',
        ];
    }
}