<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabOrderUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id' => 'sometimes|exists:doctors,id',
            'priority' => 'sometimes|string|in:routine,urgent,stat',
            'status' => 'sometimes|string|in:ordered,processing,completed,cancelled',
            'clinical_indication' => 'nullable|string|max:1000',
            'specimen_collection_time' => 'nullable|date',
            'results_available_time' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'priority.in' => 'The priority must be one of: routine, urgent, stat',
            'status.in' => 'The status must be one of: ordered, processing, completed, cancelled',
        ];
    }
}