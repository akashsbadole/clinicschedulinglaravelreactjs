<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReferralStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'referring_doctor_id' => 'required|exists:doctors,id',
            'receiving_doctor_id' => 'required|exists:doctors,id',
            'encounter_id' => 'required|exists:encounters,id',
            'referral_date' => 'required|date',
            'reason' => 'required|string|max:2000',
            'status' => 'required|in:pending,accepted,rejected,completed',
            'notes' => 'nullable|string|max:2000',
            'priority' => 'required|in:low,medium,high,urgent',
        ];
    }
}