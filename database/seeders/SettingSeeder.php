<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Clinic;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all clinics or create a default one if none exist
        $clinics = Clinic::all();

        if ($clinics->isEmpty()) {
            $this->command->info('No clinics found. Run this seeder after creating clinics.');
            return;
        }

        $settings = [
            [
                'key' => 'tax_rate',
                'value' => '10.00',
                'type' => 'number',
                'description' => 'Default tax rate percentage for billing',
            ],
            [
                'key' => 'insurance_discount_rate',
                'value' => '20.00',
                'type' => 'number',
                'description' => 'Discount rate for insured patients',
            ],
            [
                'key' => 'follow_up_discount_rate',
                'value' => '10.00',
                'type' => 'number',
                'description' => 'Additional discount rate for follow-up appointments',
            ],
        ];

        foreach ($clinics as $clinic) {
            foreach ($settings as $settingData) {
                Setting::create(array_merge($settingData, [
                    'clinic_id' => $clinic->id,
                    'is_active' => true,
                ]));
            }
        }
    }
}
