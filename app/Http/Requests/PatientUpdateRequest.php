<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Patient;

class PatientUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $patient = $this->route('id') ? Patient::find($this->route('id')) : null;
        $userId = $patient ? $patient->user_id : null;

        return [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:tomorrow',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergies' => 'nullable|string|max:1000',
            'medical_history' => 'nullable|string|max:2000',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:50',
            'last_visit_date' => 'nullable|date|before:tomorrow',
        ];
    }

    public function messages()
    {
        return [
            'blood_type.in' => 'The blood type must be a valid blood type (A+, A-, B+, B-, AB+, AB-, O+, O-)',
            'last_visit_date.before' => 'The last visit date cannot be in the future.',
        ];
    }
}