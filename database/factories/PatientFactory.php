<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
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
            'user_id' => User::factory()->create(['role' => 'patient']),
            'patient_id' => 'P' . $this->faker->unique()->numberBetween(10000, 99999),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'allergies' => $this->faker->optional(0.3)->sentence(),
            'medical_history' => $this->faker->optional(0.5)->paragraph(),
            'insurance_provider' => $this->faker->optional(0.7)->company(),
            'insurance_policy_number' => $this->faker->optional(0.7)->uuid(),
            'last_visit_date' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
