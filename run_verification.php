<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Crypt;
use App\Models\Patient;

$output = "";

$userString = 'eyJpdiI6IjhsSVlFSDR1eTRYT1o1QnJleUdad2c9PSIsInZhbHVlIjoiTGJZajA3elJUNG9HWE10MFlRNmcwUT09IiwibWFjIjoiZmRmYzdhMGI2MWM5ZmE0Mzg5MThkNTg0ZDQ0NWJhMzcyMTZmZmEyMjJkZGIxNzgzNjk5OGNlMjQ2ZTlkZWRhOSIsInRhZyI6IiJ9';

try {
    $decrypted = Crypt::decryptString($userString);
    $output .= "User String Decrypted: " . $decrypted . "\n";
} catch (\Exception $e) {
    $output .= "User String Error: " . $e->getMessage() . "\n";
}

$patient = Patient::first();
if ($patient) {
    $output .= "Patient ID: " . $patient->id . "\n";
    $output .= "Patient Allergies (toArray): " . ($patient->toArray()['allergies'] ?? 'NULL') . "\n";
    $output .= "Patient Allergies (Direct): " . ($patient->allergies ?? 'NULL') . "\n";
} else {
    $output .= "No patient found.\n";
}

file_put_contents('verification_output.txt', $output);
echo "Verification output written to verification_output.txt\n";
