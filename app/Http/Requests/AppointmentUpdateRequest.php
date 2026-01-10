<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentUpdateRequest extends FormRequest
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
            'appointment_date' => 'nullable|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'status' => 'nullable|string|in:scheduled,completed,cancelled,no-show',
            'appointment_type' => 'nullable|string|in:consultation,follow-up,emergency',
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
            'appointment_date.after_or_equal' => 'Appointment date must be today or in the future',
            'status.in' => 'Invalid appointment status',
            'appointment_type.in' => 'Invalid appointment type',
        ];
    }
}