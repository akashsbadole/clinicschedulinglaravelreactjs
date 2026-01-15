<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'clinic_id' => 'nullable|exists:clinics,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'appointment_type' => 'required|string|in:consultation,follow-up,emergency',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'priority' => 'nullable|string|in:low,normal,high,emergency',
            'is_first_visit' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'clinic_id.required' => 'Clinic ID is required',
            'clinic_id.exists' => 'Selected clinic does not exist',
            'patient_id.required' => 'Patient ID is required',
            'patient_id.exists' => 'Selected patient does not exist',
            'doctor_id.required' => 'Doctor ID is required',
            'doctor_id.exists' => 'Selected doctor does not exist',
            'appointment_date.required' => 'Appointment date is required',
            'appointment_date.after_or_equal' => 'Appointment date must be today or in the future',
            'start_time.required' => 'Start time is required',
            'appointment_type.required' => 'Appointment type is required',
            'appointment_type.in' => 'Invalid appointment type',
        ];
    }
}