<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillUpdateRequest extends FormRequest
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
            'bill_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'amount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:unpaid,paid,partially_paid,cancelled',
            'payment_method' => 'nullable|string|in:cash,credit_card,insurance,online,bank_transfer',
            'payment_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.service_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'nullable|numeric|min:0',
            'insurance_claim_status' => 'nullable|string|in:pending,approved,rejected',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'due_date.after_or_equal' => 'Due date must be on or after bill date',
            'amount.min' => 'Amount must be at least 0',
            'total_amount.min' => 'Total amount must be at least 0',
            'status.in' => 'Invalid bill status',
            'payment_method.in' => 'Invalid payment method',
            'insurance_claim_status.in' => 'Invalid insurance claim status',
        ];
    }
}