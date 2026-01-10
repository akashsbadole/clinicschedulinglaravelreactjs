<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicalRecordUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'record_type' => 'sometimes|string|in:diagnosis,prescription,lab_result,imaging,notes,consultation',
            'content' => 'sometimes|string',
            'severity' => 'sometimes|string|in:low,medium,high,critical',
            'is_confidential' => 'sometimes|boolean',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'sometimes|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ];
    }

    public function messages()
    {
        return [
            'record_type.in' => 'The record type must be one of: diagnosis, prescription, lab_result, imaging, notes, consultation',
            'severity.in' => 'The severity must be one of: low, medium, high, critical',
        ];
    }
}