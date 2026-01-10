<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillStoreRequest extends FormRequest
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
            'clinic_id' => 'required|exists:clinics,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'amount' => 'required|numeric|min:0',
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
            'clinic_id.required' => 'Clinic ID is required',
            'clinic_id.exists' => 'Selected clinic does not exist',
            'patient_id.required' => 'Patient ID is required',
            'patient_id.exists' => 'Selected patient does not exist',
            'doctor_id.exists' => 'Selected doctor does not exist',
            'appointment_id.exists' => 'Selected appointment does not exist',
            'bill_date.required' => 'Bill date is required',
            'due_date.after_or_equal' => 'Due date must be on or after bill date',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be at least 0',
            'total_amount.required' => 'Total amount is required',
            'total_amount.min' => 'Total amount must be at least 0',
            'status.in' => 'Invalid bill status',
            'payment_method.in' => 'Invalid payment method',
            'insurance_claim_status.in' => 'Invalid insurance claim status',
        ];
    }
}