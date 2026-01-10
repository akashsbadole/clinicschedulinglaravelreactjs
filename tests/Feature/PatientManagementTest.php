<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PatientManagementTest extends TestCase
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

    public function test_admin_can_create_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/patients', [
                'clinic_id' => $this->clinic->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@test.com',
                'phone' => '9876543210',
                'date_of_birth' => '1980-01-01',
                'gender' => 'male',
                'address' => '123 Patient St',
                'emergency_contact_name' => 'Jane Doe',
                'emergency_contact_phone' => '9876543211',
                'blood_type' => 'A+',
                'allergies' => 'Penicillin',
                'medical_history' => 'No major issues',
                'insurance_provider' => 'Test Insurance',
                'insurance_policy_number' => 'INS123456',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'patient_id',
                    'clinic_id',
                    'user_id',
                    'user',
                    'clinic',
                ]
            ]);

        $this->assertDatabaseHas('patients', [
            'clinic_id' => $this->clinic->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@test.com',
            'role' => 'patient',
        ]);
    }

    public function test_admin_can_get_all_patients()
    {
        // Create a patient first
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => User::factory()->create([
                'role' => 'patient',
                'clinic_id' => $this->clinic->id,
            ])->id,
            'patient_id' => 'TEST-PAT-0001',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/patients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'patient_id',
                        'clinic_id',
                        'user_id',
                        'user',
                        'clinic',
                    ]
                ]
            ]);
    }

    public function test_admin_can_get_single_patient()
    {
        // Create a patient first
        $user = User::factory()->create([
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'patient_id' => 'TEST-PAT-0001',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '9876543211',
            'blood_type' => 'A+',
            'allergies' => 'Penicillin',
            'medical_history' => 'No major issues',
            'insurance_provider' => 'Test Insurance',
            'insurance_policy_number' => 'INS123456',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/patients/' . $patient->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'patient_id',
                    'clinic_id',
                    'user_id',
                    'user',
                    'clinic',
                    'appointments',
                    'bills',
                ]
            ]);
    }

    public function test_admin_can_update_patient()
    {
        // Create a patient first
        $user = User::factory()->create([
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'patient_id' => 'TEST-PAT-0001',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '9876543211',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/patients/' . $patient->id, [
                'emergency_contact_name' => 'Updated Name',
                'emergency_contact_phone' => '1112223333',
                'blood_type' => 'B+',
                'allergies' => 'Updated allergies',
                'last_visit_date' => '2023-01-01',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Patient updated successfully',
            ]);

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'emergency_contact_name' => 'Updated Name',
            'blood_type' => 'B+',
        ]);
    }

    public function test_admin_can_delete_patient()
    {
        // Create a patient first
        $user = User::factory()->create([
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'patient_id' => 'TEST-PAT-0001',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/patients/' . $patient->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Patient deleted successfully',
            ]);

        $this->assertSoftDeleted('patients', [
            'id' => $patient->id,
        ]);
    }

    public function test_validation_errors_for_patient_creation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/patients', [
                // Missing required fields
                'clinic_id' => $this->clinic->id,
                'first_name' => '',
                'last_name' => '',
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'date_of_birth',
                    'gender',
                    'address',
                ]
            ]);
    }
}