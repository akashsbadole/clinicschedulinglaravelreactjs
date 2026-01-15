<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition()
    {
        return [
            'clinic_id' => Clinic::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_id' => Appointment::factory(),
            'record_type' => $this->faker->randomElement(['diagnosis', 'prescription', 'lab_result', 'imaging', 'notes', 'consultation']),
            'content' => $this->faker->paragraph(),
            'attachments' => $this->faker->optional(0.3)->passthrough([]),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'is_confidential' => $this->faker->boolean(20), // 20% chance of being confidential
        ];
    }
}