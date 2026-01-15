<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (!$this->has('clinic_id') && auth()->check()) {
            $this->merge([
                'clinic_id' => auth()->user()->clinic_id,
            ]);
        }

        if ($this->has('name') && (!$this->has('first_name') || !$this->has('last_name'))) {
            $parts = explode(' ', $this->name, 2);
            $this->merge([
                'first_name' => $parts[0] ?? '',
                'last_name' => $parts[1] ?? '',
            ]);
        }

        if ($this->has('blood_group')) {
            $this->merge([
                'blood_type' => $this->blood_group,
            ]);
        }

        if ($this->has('chronic_conditions')) {
            $this->merge([
                'medical_history' => $this->chronic_conditions,
            ]);
        }
    }

    public function rules()
    {
        return [
            'clinic_id' => 'required|exists:clinics,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|string|in:male,female,other',
            'address' => 'required|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergies' => 'nullable|string|max:1000',
            'medical_history' => 'nullable|string|max:2000',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'date_of_birth.before' => 'The date of birth must be a date in the past.',
            'blood_type.in' => 'The blood type must be a valid blood type (A+, A-, B+, B-, AB+, AB-, O+, O-)',
        ];
    }
}