<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DoctorManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $doctorUser;
    protected $staffUser;
    protected $patientUser;
    protected $clinic;
    protected $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create clinic
        $this->clinic = Clinic::create([
            'name' => 'Test Clinic',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'phone' => '1234567890',
            'email' => 'test@clinic.com',
            'is_active' => true
        ]);

        // Create admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'clinic_id' => $this->clinic->id
        ]);

        // Create doctor user
        $this->doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
            'specialization' => 'Cardiology',
            'license_number' => 'LIC-12345'
        ]);

        // Create doctor record
        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->doctorUser->id,
            'doctor_id' => 'TES-DOC-0001',
            'specialization' => 'Cardiology',
            'license_number' => 'LIC-12345',
            'qualifications' => 'MD, PhD',
            'years_of_experience' => 10,
            'consultation_fee' => '100.00',
            'bio' => 'Test doctor bio',
            'availability' => 'Monday-Friday',
            'is_accepting_new_patients' => true
        ]);

        // Create staff user
        $this->staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'clinic_id' => $this->clinic->id
        ]);

        // Create patient user
        $this->patientUser = User::create([
            'name' => 'Patient User',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
            'clinic_id' => $this->clinic->id
        ]);
    }

    /** @test */
    public function admin_can_create_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'phone' => '1234567890',
            'specialization' => 'Neurology',
            'license_number' => 'LIC-67890',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => '150.00',
            'bio' => 'New doctor bio',
            'availability' => 'Monday-Friday',
            'is_accepting_new_patients' => true
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Doctor created successfully'
            ]);

        $this->assertDatabaseHas('doctors', [
            'clinic_id' => $this->clinic->id,
            'specialization' => 'Neurology',
            'license_number' => 'LIC-67890'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@test.com',
            'role' => 'doctor'
        ]);
    }

    /** @test */
    public function doctor_cannot_create_doctor()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@test.com',
            'phone' => '0987654321',
            'specialization' => 'Pediatrics',
            'license_number' => 'LIC-54321',
            'qualifications' => 'MD',
            'years_of_experience' => 8,
            'consultation_fee' => '120.00'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function staff_cannot_create_doctor()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Bob',
            'last_name' => 'Wilson',
            'email' => 'bob.wilson@test.com',
            'phone' => '1122334455',
            'specialization' => 'Dermatology',
            'license_number' => 'LIC-11223',
            'qualifications' => 'MD',
            'years_of_experience' => 12,
            'consultation_fee' => '200.00'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function patient_cannot_create_doctor()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'email' => 'alice.brown@test.com',
            'phone' => '5566778899',
            'specialization' => 'Psychiatry',
            'license_number' => 'LIC-44556',
            'qualifications' => 'MD',
            'years_of_experience' => 15,
            'consultation_fee' => '180.00'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_create_doctor_with_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => 999, // Invalid clinic ID
            'first_name' => '',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'phone' => '123',
            'specialization' => '',
            'license_number' => 'LIC-12345', // Duplicate
            'qualifications' => 'MD',
            'years_of_experience' => 60, // Exceeds max
            'consultation_fee' => '15000.00' // Exceeds max
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_create_doctor_with_duplicate_email()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Duplicate',
            'last_name' => 'Email',
            'email' => 'doctor@test.com', // Duplicate email
            'phone' => '1234567890',
            'specialization' => 'Neurology',
            'license_number' => 'LIC-99999',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => '150.00'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_create_doctor_with_duplicate_license_number()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Duplicate',
            'last_name' => 'License',
            'email' => 'duplicate.license@test.com',
            'phone' => '1234567890',
            'specialization' => 'Neurology',
            'license_number' => 'LIC-12345', // Duplicate license
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => '150.00'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function admin_can_get_all_doctors()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/doctors');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'doctor_id',
                        'specialization',
                        'license_number'
                    ]
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_doctors_from_own_clinic()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/doctors');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_get_doctors_from_own_clinic()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/doctors');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_get_doctors()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/doctors');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_get_specific_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/doctors/' . $this->doctor->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->doctor->id,
                    'doctor_id' => 'TES-DOC-0001'
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_own_record()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/doctors/' . $this->doctor->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function cannot_get_doctor_from_different_clinic()
    {
        // Create another clinic and doctor
        $otherClinic = Clinic::create([
            'name' => 'Other Clinic',
            'address' => '456 Other St',
            'city' => 'Other City',
            'state' => 'Other State',
            'postal_code' => '67890',
            'country' => 'Other Country',
            'phone' => '0987654321',
            'email' => 'other@clinic.com',
            'is_active' => true
        ]);

        $otherDoctor = Doctor::create([
            'clinic_id' => $otherClinic->id,
            'user_id' => $this->doctorUser->id, // Same user but different clinic
            'doctor_id' => 'OTH-DOC-0001',
            'specialization' => 'Surgery',
            'license_number' => 'LIC-99999',
            'qualifications' => 'MD',
            'years_of_experience' => 20,
            'consultation_fee' => '300.00'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/doctors/' . $otherDoctor->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_get_nonexistent_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/doctors/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Doctor not found'
            ]);
    }

    /** @test */
    public function admin_can_update_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/doctors/' . $this->doctor->id, [
            'specialization' => 'Updated Cardiology',
            'years_of_experience' => 12,
            'consultation_fee' => '120.00',
            'bio' => 'Updated bio'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Doctor updated successfully'
            ]);

        $this->assertDatabaseHas('doctors', [
            'id' => $this->doctor->id,
            'specialization' => 'Updated Cardiology',
            'years_of_experience' => 12
        ]);
    }

    /** @test */
    public function doctor_can_update_own_record()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->put('/api/doctors/' . $this->doctor->id, [
            'bio' => 'Updated by doctor',
            'availability' => 'Tuesday-Thursday'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_cannot_update_doctor()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->put('/api/doctors/' . $this->doctor->id, [
            'specialization' => 'Updated by staff'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function patient_cannot_update_doctor()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->put('/api/doctors/' . $this->doctor->id, [
            'specialization' => 'Updated by patient'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_update_doctor_with_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/doctors/' . $this->doctor->id, [
            'years_of_experience' => 60, // Exceeds max
            'consultation_fee' => '15000.00' // Exceeds max
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_update_nonexistent_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/doctors/999', [
            'specialization' => 'Updated'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Doctor not found'
            ]);
    }

    /** @test */
    public function admin_can_delete_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/doctors/' . $this->doctor->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Doctor deleted successfully'
            ]);

        $this->assertSoftDeleted('doctors', [
            'id' => $this->doctor->id
        ]);
    }

    /** @test */
    public function doctor_cannot_delete_doctor()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->delete('/api/doctors/' . $this->doctor->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function staff_cannot_delete_doctor()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->delete('/api/doctors/' . $this->doctor->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function patient_cannot_delete_doctor()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->delete('/api/doctors/' . $this->doctor->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_delete_nonexistent_doctor()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/doctors/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Doctor not found'
            ]);
    }

    /** @test */
    public function doctor_id_is_generated_correctly()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Test',
            'last_name' => 'Doctor',
            'email' => 'test.doctor@test.com',
            'phone' => '1234567890',
            'specialization' => 'Test Specialization',
            'license_number' => 'LIC-00001',
            'qualifications' => 'MD',
            'years_of_experience' => 1,
            'consultation_fee' => '50.00'
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertStringStartsWith('TES-DOC-', $data['doctor_id']);
    }

    /** @test */
    public function doctor_creation_fails_with_clinic_not_found()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/doctors', [
            'clinic_id' => 999,
            'first_name' => 'Test',
            'last_name' => 'Doctor',
            'email' => 'test.doctor@test.com',
            'phone' => '1234567890',
            'specialization' => 'Test Specialization',
            'license_number' => 'LIC-00001',
            'qualifications' => 'MD',
            'years_of_experience' => 1,
            'consultation_fee' => '50.00'
        ]);

        $response->assertStatus(422);
    }
}