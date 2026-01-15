<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiAssistedFeatureStoreRequest extends FormRequest
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
            'feature_type' => 'required|in:diagnosis_suggestion,treatment_recommendation,medication_interaction,prognosis_prediction,documentation_assistance',
            'input_data' => 'required|array',
            'input_data.patient_history' => 'sometimes|string',
            'input_data.current_symptoms' => 'sometimes|string',
            'input_data.lab_results' => 'sometimes|string',
            'input_data.medications' => 'sometimes|string',
            'input_data.diagnoses' => 'sometimes|string',
            'input_data.allergies' => 'sometimes|string',
            'input_data.vital_signs' => 'sometimes|array',
            'input_data.notes' => 'sometimes|string',
            'context' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'status' => 'sometimes|in:pending,processing,completed,failed,cancelled',
            'result' => 'nullable|array',
            'result.suggestions' => 'sometimes|array',
            'result.confidence_score' => 'sometimes|numeric|min:0|max:1',
            'result.references' => 'sometimes|array',
            'result.generated_text' => 'sometimes|string',
            'result.warnings' => 'sometimes|array',
            'feedback' => 'nullable|string',
            'feedback_rating' => 'sometimes|integer|min:1|max:5',
            'processed_by' => 'sometimes|exists:users,id',
            'completed_at' => 'sometimes|date',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'feature_type.required' => 'The feature type is required.',
            'feature_type.in' => 'The feature type must be one of: diagnosis_suggestion, treatment_recommendation, medication_interaction, prognosis_prediction, documentation_assistance.',
            'input_data.required' => 'The input data is required.',
            'priority.in' => 'The priority must be one of: low, medium, high, critical.',
            'status.in' => 'The status must be one of: pending, processing, completed, failed, cancelled.',
            'result.confidence_score.min' => 'The confidence score must be at least 0.',
            'result.confidence_score.max' => 'The confidence score may not be greater than 1.',
            'feedback_rating.min' => 'The feedback rating must be at least 1.',
            'feedback_rating.max' => 'The feedback rating may not be greater than 5.',
            'processed_by.exists' => 'The selected user does not exist.',
        ];
    }
}