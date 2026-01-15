<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'document_type' => 'sometimes|string|max:100',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_confidential' => 'nullable|boolean',
            'tags' => 'nullable|string|max:255',
        ];
    }
}