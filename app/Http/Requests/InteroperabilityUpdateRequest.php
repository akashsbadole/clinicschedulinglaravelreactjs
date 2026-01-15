<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InteroperabilityUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'sometimes|exists:patients,id',
            'referring_doctor_id' => 'sometimes|exists:doctors,id',
            'receiving_doctor_id' => 'sometimes|exists:doctors,id',
            'encounter_id' => 'sometimes|exists:encounters,id',
            'referral_reason' => 'sometimes|string|max:255',
            'referral_notes' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'referral_date' => 'sometimes|date',
            'expected_completion_date' => 'nullable|date',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'data_format' => 'sometimes|in:hl7,fhir,ccd,custom',
            'transfer_method' => 'sometimes|in:api,email,fax,postal',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'patient_id.exists' => 'The selected patient does not exist.',
            'referring_doctor_id.exists' => 'The selected referring doctor does not exist.',
            'receiving_doctor_id.exists' => 'The selected receiving doctor does not exist.',
            'encounter_id.exists' => 'The selected encounter does not exist.',
            'referral_reason.string' => 'The referral reason must be a string.',
            'referral_reason.max' => 'The referral reason may not be greater than 255 characters.',
            'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
            'referral_date.date' => 'The referral date must be a valid date.',
            'expected_completion_date.date' => 'The expected completion date must be a valid date.',
            'priority.in' => 'The priority must be one of: low, medium, high, critical.',
            'data_format.in' => 'The data format must be one of: hl7, fhir, ccd, custom.',
            'transfer_method.in' => 'The transfer method must be one of: api, email, fax, postal.',
        ];
    }
}