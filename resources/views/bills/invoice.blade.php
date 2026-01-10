<!DOCTYPE html>
<html>
<head>
    <title>Medical Clinic Invoice - {{ $bill->bill_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .clinic-info {
            width: 60%;
        }
        .invoice-info {
            width: 35%;
            text-align: right;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .clinic-details {
            font-size: 12px;
            line-height: 1.4;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
            margin-top: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #6c757d;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
        .status-unpaid {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-partially-paid {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="clinic-info">
            <div class="clinic-name">{{ $clinic->name }}</div>
            <div class="clinic-details">
                {{ $clinic->address }}, {{ $clinic->city }}<br>
                {{ $clinic->state }}, {{ $clinic->postal_code }}<br>
                {{ $clinic->country }}<br>
                Phone: {{ $clinic->phone }}<br>
                Email: {{ $clinic->email }}<br>
                @if($clinic->website)
                Website: {{ $clinic->website }}<br>
                @endif
            </div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">INVOICE</div>
            <div><strong>Invoice #:</strong> {{ $bill->bill_number }}</div>
            <div><strong>Date:</strong> {{ $bill->bill_date->format('M d, Y') }}</div>
            <div><strong>Due Date:</strong> {{ $bill->due_date->format('M d, Y') }}</div>
            <div><strong>Status:</strong> 
                <span class="status-badge 
                    @if($bill->status === 'paid') status-paid
                    @elseif($bill->status === 'unpaid') status-unpaid
                    @elseif($bill->status === 'partially_paid') status-partially-paid
                    @elseif($bill->status === 'cancelled') status-cancelled
                    @endif
                ">
                    {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                </span>
            </div>
        </div>
    </div>

    <div class="section-title">BILL TO</div>
    <div class="table">
        <tr>
            <td style="width: 30%;"><strong>Patient Name:</strong></td>
            <td>{{ $patient->user->name }}</td>
        </tr>
        <tr>
            <td><strong>Patient ID:</strong></td>
            <td>{{ $patient->patient_id }}</td>
        </tr>
        <tr>
            <td><strong>Address:</strong></td>
            <td>{{ $patient->user->address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Phone:</strong></td>
            <td>{{ $patient->user->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $patient->user->email }}</td>
        </tr>
        @if($patient->insurance_provider)
        <tr>
            <td><strong>Insurance Provider:</strong></td>
            <td>{{ $patient->insurance_provider }}</td>
        </tr>
        <tr>
            <td><strong>Policy Number:</strong></td>
            <td>{{ $patient->insurance_policy_number }}</td>
        </tr>
        @endif
    </div>

    @if($doctor)
    <div class="section-title">DOCTOR INFORMATION</div>
    <div class="table">
        <tr>
            <td style="width: 30%;"><strong>Doctor Name:</strong></td>
            <td>{{ $doctor->user->name }}</td>
        </tr>
        <tr>
            <td><strong>Specialization:</strong></td>
            <td>{{ $doctor->specialization }}</td>
        </tr>
        <tr>
            <td><strong>License Number:</strong></td>
            <td>{{ $doctor->license_number }}</td>
        </tr>
    </div>
    @endif

    @if($appointment)
    <div class="section-title">APPOINTMENT INFORMATION</div>
    <div class="table">
        <tr>
            <td style="width: 30%;"><strong>Appointment #:</strong></td>
            <td>{{ $appointment->appointment_number }}</td>
        </tr>
        <tr>
            <td><strong>Date:</strong></td>
            <td>{{ $appointment->appointment_date->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Time:</strong></td>
            <td>{{ $appointment->start_time }} - {{ $appointment->end_time }}</td>
        </tr>
        <tr>
            <td><strong>Type:</strong></td>
            <td>{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>{{ ucfirst($appointment->status) }}</td>
        </tr>
    </div>
    @endif

    <div class="section-title">SERVICE DETAILS</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Description</th>
                <th style="width: 15%; text-align: right;">Quantity</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 15%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = $bill->items ?? [];
                if (!is_array($items)) {
                    $items = [];
                }
            @endphp
            @if(!empty($items))
                @foreach($items as $index => $item)
                    @if(is_array($item))
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['service_name'] ?? 'Service' }}</td>
                        <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="text-right">${{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                        <td class="text-right">${{ number_format($item['total_price'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                @endforeach
            @else
                <tr>
                    <td>1</td>
                    <td>Medical Consultation</td>
                    <td class="text-right">1</td>
                    <td class="text-right">${{ number_format($bill->amount, 2) }}</td>
                    <td class="text-right">${{ number_format($bill->amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">PAYMENT SUMMARY</div>
    <table class="table">
        <tr>
            <td style="width: 70%; text-align: right;"><strong>Subtotal:</strong></td>
            <td class="text-right">${{ number_format($bill->amount, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right;"><strong>Tax ({{ number_format(($bill->tax / $bill->amount) * 100, 1) }}%):</strong></td>
            <td class="text-right">${{ number_format($bill->tax, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right;"><strong>Discount:</strong></td>
            <td class="text-right">- ${{ number_format($bill->discount, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td style="text-align: right;"><strong>TOTAL AMOUNT DUE:</strong></td>
            <td class="text-right">${{ number_format($bill->total_amount, 2) }}</td>
        </tr>
    </table>

    @if($bill->payment_method)
    <div class="section-title">PAYMENT INFORMATION</div>
    <div class="table">
        <tr>
            <td style="width: 30%;"><strong>Payment Method:</strong></td>
            <td>{{ ucfirst(str_replace('_', ' ', $bill->payment_method)) }}</td>
        </tr>
        <tr>
            <td><strong>Payment Date:</strong></td>
            <td>{{ $bill->payment_date ? $bill->payment_date->format('M d, Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</td>
        </tr>
    </div>
    @endif

    @if($bill->description)
    <div class="section-title">NOTES</div>
    <div class="table">
        <tr>
            <td>{{ $bill->description }}</td>
        </tr>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for choosing {{ $clinic->name }}. This is a computer-generated invoice and does not require a signature.</p>
        <p>Generated on {{ $generated_at->format('M d, Y H:i:s') }} by {{ $generated_by }}</p>
    </div>
</body>
</html>