<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicalRecordStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clinic_id' => 'required|exists:clinics,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'record_type' => 'required|string|in:diagnosis,prescription,lab_result,imaging,notes,consultation',
            'content' => 'required|string',
            'severity' => 'nullable|string|in:low,medium,high,critical',
            'is_confidential' => 'nullable|boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
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