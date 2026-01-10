<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,credit_card,insurance,online,bank_transfer',
            'payment_date' => 'nullable|date',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required',
            'amount.min' => 'Payment amount must be at least 0',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
        ];
    }
}