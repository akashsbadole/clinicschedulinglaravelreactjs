<?php

use App\Models\Patient;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Crypt;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function decryptValue($value)
{
    if (empty($value))
        return $value;
    try {
        return Crypt::decryptString($value);
    } catch (\Exception $e) {
        // If it's not encrypted, return as is
        return $value;
    }
}

echo "Starting decryption process..." . PHP_EOL;

// 1. Decrypt Patients
$patients = Patient::all();
echo "Processing " . $patients->count() . " patients..." . PHP_EOL;
foreach ($patients as $patient) {
    $patient->allergies = decryptValue($patient->allergies);
    $patient->medical_history = decryptValue($patient->medical_history);
    $patient->emergency_contact_name = decryptValue($patient->emergency_contact_name);
    $patient->emergency_contact_phone = decryptValue($patient->emergency_contact_phone);
    $patient->insurance_policy_number = decryptValue($patient->insurance_policy_number);
    $patient->save();
}

// 2. Decrypt Medical Records
$records = MedicalRecord::all();
echo "Processing " . $records->count() . " medical records..." . PHP_EOL;
foreach ($records as $record) {
    $record->content = decryptValue($record->content);
    $record->save();
}

echo "Decryption completed successfully!" . PHP_EOL;
