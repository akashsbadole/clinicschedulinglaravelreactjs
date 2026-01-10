<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'specialization' => 'nullable|string|max:255',
            'qualifications' => 'nullable|string|max:1000',
            'years_of_experience' => 'nullable|integer|min:0|max:50',
            'consultation_fee' => 'nullable|numeric|min:0|max:10000',
            'bio' => 'nullable|string|max:2000',
            'availability' => 'nullable|string|max:255',
            'is_accepting_new_patients' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'years_of_experience.max' => 'Years of experience cannot exceed 50 years.',
            'consultation_fee.max' => 'Consultation fee cannot exceed 10000.',
        ];
    }
}