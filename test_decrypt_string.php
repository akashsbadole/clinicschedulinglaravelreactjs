<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Crypt;

$string = 'eyJpdiI6IjhsSVlFSDR1eTRYT1o1QnJleUdad2c9PSIsInZhbHVlIjoiTGJZajA3elJUNG9HWE10MFlRNmcwUT09IiwibWFjIjoiZmRmYzdhMGI2MWM5ZmE0Mzg5MThkNTg0ZDQ0NWJhMzcyMTZmZmEyMjJkZGIxNzgzNjk5OGNlMjQ2ZTlkZWRhOSIsInRhZyI6IiJ9';

try {
    $decrypted = Crypt::decryptString($string);
    echo "Decrypted: " . $decrypted . PHP_EOL;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
