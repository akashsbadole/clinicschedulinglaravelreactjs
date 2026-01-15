<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Clinic;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all clinics or create a default one if none exist
        $clinics = Clinic::all();

        if ($clinics->isEmpty()) {
            // If no clinics exist, we'll seed services without clinic_id for now
            // This will be updated when clinics are created
            $clinicId = null;
        } else {
            $clinicId = $clinics->first()->id;
        }

        $services = [
            [
                'service_code' => 'consultation',
                'name' => 'General Consultation',
                'description' => 'Standard doctor consultation',
                'base_price' => 100.00,
                'category' => 'Consultation',
                'taxable' => true,
                'insurance_eligible' => true,
            ],
            [
                'service_code' => 'follow_up',
                'name' => 'Follow-up Visit',
                'description' => 'Follow-up consultation',
                'base_price' => 75.00,
                'category' => 'Consultation',
                'taxable' => true,
                'insurance_eligible' => true,
            ],
            [
                'service_code' => 'emergency',
                'name' => 'Emergency Consultation',
                'description' => 'Urgent care consultation',
                'base_price' => 150.00,
                'category' => 'Consultation',
                'taxable' => true,
                'insurance_eligible' => true,
            ],
            [
                'service_code' => 'lab_test',
                'name' => 'Laboratory Test',
                'description' => 'Basic laboratory tests',
                'base_price' => 50.00,
                'category' => 'Laboratory',
                'taxable' => true,
                'insurance_eligible' => true,
            ],
            [
                'service_code' => 'imaging',
                'name' => 'Diagnostic Imaging',
                'description' => 'X-ray, MRI, CT scan',
                'base_price' => 200.00,
                'category' => 'Imaging',
                'taxable' => true,
                'insurance_eligible' => true,
            ],
            [
                'service_code' => 'vaccination',
                'name' => 'Vaccination',
                'description' => 'Standard vaccinations',
                'base_price' => 30.00,
                'category' => 'Vaccination',
                'taxable' => true,
                'insurance_eligible' => true,
            ],
        ];

        foreach ($clinics as $clinic) {
            foreach ($services as $serviceData) {
                Service::create(array_merge($serviceData, [
                    'clinic_id' => $clinic->id,
                    'is_active' => true,
                ]));
            }
        }

        // If no clinics exist, create a seeder that can be run after clinics are created
        if ($clinics->isEmpty()) {
            $this->command->info('No clinics found. Run this seeder after creating clinics.');
        }
    }
}
