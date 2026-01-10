<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+1 month');
        $endTime = $this->faker->dateTimeBetween($startTime, $startTime->format('Y-m-d H:i:s') . ' +2 hours');

        return [
            'clinic_id' => Clinic::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'created_by' => User::factory(),
            'appointment_number' => 'APPT-' . $this->faker->unique()->numberBetween(100000, 999999),
            'appointment_date' => $startTime->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'cancelled', 'no-show']),
            'appointment_type' => $this->faker->randomElement(['consultation', 'follow-up', 'emergency']),
            'reason' => $this->faker->optional(0.7)->sentence(),
            'notes' => $this->faker->optional(0.5)->paragraph(),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'emergency']),
            'is_first_visit' => $this->faker->boolean(30),
        ];
    }
}
