<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'user_id' => User::factory()->create(['role' => 'doctor']),
            'doctor_id' => 'D' . $this->faker->unique()->numberBetween(10000, 99999),
            'specialization' => $this->faker->randomElement([
                'General Medicine', 'Cardiology', 'Dermatology', 'Neurology',
                'Orthopedics', 'Pediatrics', 'Psychiatry', 'Radiology'
            ]),
            'license_number' => 'LIC' . $this->faker->unique()->numberBetween(100000, 999999),
            'qualifications' => $this->faker->randomElement(['MD', 'DO', 'MBBS']) . ', ' . $this->faker->randomElement(['Board Certified', 'Fellowship Trained']),
            'years_of_experience' => $this->faker->numberBetween(1, 40),
            'consultation_fee' => $this->faker->numberBetween(50, 500),
            'bio' => $this->faker->optional(0.8)->paragraph(),
            'availability' => 'Monday-Friday',
            'is_accepting_new_patients' => $this->faker->boolean(80),
        ];
    }
}
