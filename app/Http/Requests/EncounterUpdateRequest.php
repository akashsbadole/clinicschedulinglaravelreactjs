<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EncounterUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'appointment_id' => 'nullable|exists:appointments,id',
            'doctor_id' => 'sometimes|exists:doctors,id',
            'encounter_type' => 'sometimes|string|in:in-person,telemedicine,home-visit',
            'start_time' => 'sometimes|date',
            'end_time' => 'nullable|date|after:start_time',
            'status' => 'sometimes|string|in:active,completed,cancelled',
            'priority' => 'sometimes|string|in:routine,urgent,emergency',
            'chief_complaint' => 'nullable|string|max:1000',
            'history_of_present_illness' => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'encounter_type.in' => 'The encounter type must be one of: in-person, telemedicine, home-visit',
            'status.in' => 'The status must be one of: active, completed, cancelled',
            'priority.in' => 'The priority must be one of: routine, urgent, emergency',
            'end_time.after' => 'The end time must be after the start time',
        ];
    }
}