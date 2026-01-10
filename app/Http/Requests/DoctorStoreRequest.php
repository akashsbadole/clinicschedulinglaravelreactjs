<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DoctorStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clinic_id' => 'required|exists:clinics,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'license_number' => 'required|string|max:50|unique:doctors,license_number',
            'qualifications' => 'required|string|max:1000',
            'years_of_experience' => 'required|integer|min:0|max:50',
            'consultation_fee' => 'required|numeric|min:0|max:10000',
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