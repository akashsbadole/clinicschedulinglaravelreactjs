<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReferralUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'sometimes|exists:patients,id',
            'referring_doctor_id' => 'sometimes|exists:doctors,id',
            'receiving_doctor_id' => 'sometimes|exists:doctors,id',
            'encounter_id' => 'sometimes|exists:encounters,id',
            'referral_date' => 'sometimes|date',
            'reason' => 'sometimes|string|max:2000',
            'status' => 'sometimes|in:pending,accepted,rejected,completed',
            'notes' => 'nullable|string|max:2000',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ];
    }
}