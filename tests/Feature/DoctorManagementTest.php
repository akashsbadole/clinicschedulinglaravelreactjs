<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DoctorManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $clinic;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a clinic
        $this->clinic = Clinic::create([
            'name' => 'Test Clinic',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'phone' => '1234567890',
            'email' => 'test@clinic.com',
            'is_active' => true,
        ]);

        // Create an admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_admin_can_create_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/doctors', [
                'clinic_id' => $this->clinic->id,
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@test.com',
                'phone' => '9876543210',
                'specialization' => 'Cardiology',
                'license_number' => 'LIC123456',
                'qualifications' => 'MD, PhD',
                'years_of_experience' => 10,
                'consultation_fee' => 150.00,
                'bio' => 'Experienced cardiologist',
                'availability' => 'Monday-Friday',
                'is_accepting_new_patients' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'doctor_id',
                    'clinic_id',
                    'user_id',
                    'user',
                    'clinic',
                    'schedules',
                ]
            ]);

        $this->assertDatabaseHas('doctors', [
            'clinic_id' => $this->clinic->id,
            'specialization' => 'Cardiology',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.smith@test.com',
            'role' => 'doctor',
        ]);
    }

    public function test_admin_can_get_all_doctors()
    {
        // Create a doctor first
        $doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => User::factory()->create([
                'role' => 'doctor',
                'clinic_id' => $this->clinic->id,
            ])->id,
            'doctor_id' => 'TEST-DOC-0001',
            'specialization' => 'Cardiology',
            'license_number' => 'LIC123456',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => 100.00,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/doctors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'doctor_id',
                        'clinic_id',
                        'user_id',
                        'user',
                        'clinic',
                        'schedules',
                    ]
                ]
            ]);
    }

    public function test_admin_can_get_single_doctor()
    {
        // Create a doctor first
        $user = User::factory()->create([
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);

        $doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'doctor_id' => 'TEST-DOC-0001',
            'specialization' => 'Cardiology',
            'license_number' => 'LIC123456',
            'qualifications' => 'MD, PhD',
            'years_of_experience' => 10,
            'consultation_fee' => 150.00,
            'bio' => 'Experienced cardiologist',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/doctors/' . $doctor->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'doctor_id',
                    'clinic_id',
                    'user_id',
                    'user',
                    'clinic',
                    'appointments',
                    'bills',
                    'schedules',
                ]
            ]);
    }

    public function test_admin_can_update_doctor()
    {
        // Create a doctor first
        $user = User::factory()->create([
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);

        $doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'doctor_id' => 'TEST-DOC-0001',
            'specialization' => 'Cardiology',
            'license_number' => 'LIC123456',
            'qualifications' => 'MD, PhD',
            'years_of_experience' => 10,
            'consultation_fee' => 150.00,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/doctors/' . $doctor->id, [
                'specialization' => 'Neurology',
                'qualifications' => 'MD, PhD, Neurology Specialist',
                'years_of_experience' => 12,
                'consultation_fee' => 200.00,
                'bio' => 'Updated bio',
                'availability' => 'Monday-Thursday',
                'is_accepting_new_patients' => false,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Doctor updated successfully',
            ]);

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'specialization' => 'Neurology',
            'consultation_fee' => 200.00,
        ]);
    }

    public function test_admin_can_delete_doctor()
    {
        // Create a doctor first
        $user = User::factory()->create([
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);

        $doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'doctor_id' => 'TEST-DOC-0001',
            'specialization' => 'Cardiology',
            'license_number' => 'LIC123456',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => 100.00,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/doctors/' . $doctor->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Doctor deleted successfully',
            ]);

        $this->assertSoftDeleted('doctors', [
            'id' => $doctor->id,
        ]);
    }

    public function test_validation_errors_for_doctor_creation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/doctors', [
                // Missing required fields
                'clinic_id' => $this->clinic->id,
                'first_name' => '',
                'last_name' => '',
                'email' => 'invalid-email',
                'specialization' => '',
                'license_number' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'specialization',
                    'license_number',
                    'qualifications',
                    'years_of_experience',
                    'consultation_fee',
                ]
            ]);
    }

    public function test_admin_can_update_doctor_schedule()
    {
        // Create a doctor first
        $user = User::factory()->create([
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);

        $doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'doctor_id' => 'TEST-DOC-0001',
            'specialization' => 'Cardiology',
            'license_number' => 'LIC123456',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => 100.00,
        ]);

        $scheduleData = [
            [
                'day_of_week' => 'Monday',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'slot_duration' => 30,
                'is_active' => true,
            ],
            [
                'day_of_week' => 'Tuesday',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'slot_duration' => 30,
                'is_active' => true,
            ],
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/doctors/' . $doctor->id . '/schedule', [
                'schedule' => $scheduleData,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Doctor schedule updated successfully',
            ]);

        $this->assertDatabaseHas('schedules', [
            'doctor_id' => $doctor->id,
            'day_of_week' => 'Monday',
        ]);
    }
}