<?php

use App\Models\Patient;
use Illuminate\Support\Facades\Crypt;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking first patient decryption..." . PHP_EOL;

$patient = Patient::first();

if (!$patient) {
    echo "No patient found in database." . PHP_EOL;
    exit;
}

echo "ID: " . $patient->id . PHP_EOL;
echo "Allergies (Direct): " . ($patient->allergies ?? 'NULL') . PHP_EOL;
echo "Allergies (toArray): " . ($patient->toArray()['allergies'] ?? 'NULL') . PHP_EOL;

if (isset($patient->getAttributes()['allergies'])) {
    echo "Raw Allergies in DB: " . substr($patient->getAttributes()['allergies'], 0, 50) . "..." . PHP_EOL;
}
