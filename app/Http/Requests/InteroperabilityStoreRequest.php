<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InteroperabilityStoreRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'referring_doctor_id' => 'required|exists:doctors,id',
            'receiving_doctor_id' => 'required|exists:doctors,id',
            'encounter_id' => 'required|exists:encounters,id',
            'referral_reason' => 'required|string|max:255',
            'referral_notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'referral_date' => 'required|date',
            'expected_completion_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,critical',
            'data_format' => 'required|in:hl7,fhir,ccd,custom',
            'transfer_method' => 'required|in:api,email,fax,postal',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'The patient ID is required.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'referring_doctor_id.required' => 'The referring doctor ID is required.',
            'referring_doctor_id.exists' => 'The selected referring doctor does not exist.',
            'receiving_doctor_id.required' => 'The receiving doctor ID is required.',
            'receiving_doctor_id.exists' => 'The selected receiving doctor does not exist.',
            'encounter_id.required' => 'The encounter ID is required.',
            'encounter_id.exists' => 'The selected encounter does not exist.',
            'referral_reason.required' => 'The referral reason is required.',
            'referral_reason.string' => 'The referral reason must be a string.',
            'referral_reason.max' => 'The referral reason may not be greater than 255 characters.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
            'referral_date.required' => 'The referral date is required.',
            'referral_date.date' => 'The referral date must be a valid date.',
            'expected_completion_date.date' => 'The expected completion date must be a valid date.',
            'priority.required' => 'The priority is required.',
            'priority.in' => 'The priority must be one of: low, medium, high, critical.',
            'data_format.required' => 'The data format is required.',
            'data_format.in' => 'The data format must be one of: hl7, fhir, ccd, custom.',
            'transfer_method.required' => 'The transfer method is required.',
            'transfer_method.in' => 'The transfer method must be one of: api, email, fax, postal.',
        ];
    }
}