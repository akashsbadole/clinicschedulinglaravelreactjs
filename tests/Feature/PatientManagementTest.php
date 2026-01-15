<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $doctorUser;
    protected $patientUser;
    protected $staffUser;
    protected $clinic;
    protected $doctor;
    protected $patient;

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
            'doctor_id' => 'TST-DOC-0001',
            'specialization' => 'Cardiology',
            'license_number' => 'LIC-12345',
            'qualifications' => 'MD, PhD',
            'years_of_experience' => 10,
            'consultation_fee' => '100.00',
            'bio' => 'Test doctor bio',
            'availability' => 'Monday-Friday',
            'is_accepting_new_patients' => true
        ]);

        // Create patient user
        $this->patientUser = User::create([
            'name' => 'Patient User',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
            'clinic_id' => $this->clinic->id
        ]);

        // Create patient record
        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->patientUser->id,
            'patient_id' => 'TST-PAT-0001',
            'emergency_contact_name' => 'John Doe',
            'emergency_contact_phone' => '9876543210',
            'blood_type' => 'O+',
            'allergies' => 'None',
            'medical_history' => 'No major issues',
            'insurance_provider' => 'Test Insurance',
            'insurance_policy_number' => 'POL-12345',
            'last_visit_date' => now()
        ]);

        // Create staff user
        $this->staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'clinic_id' => $this->clinic->id
        ]);
    }

    /** @test */
    public function admin_can_create_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'phone' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => '123 Test St, Test City',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '0987654321',
            'blood_type' => 'A+',
            'allergies' => 'Penicillin',
            'medical_history' => 'No significant history',
            'insurance_provider' => 'Test Insurance Co',
            'insurance_policy_number' => 'POL-67890'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Patient created successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@test.com',
            'role' => 'patient',
            'clinic_id' => $this->clinic->id
        ]);

        $patient = Patient::where('clinic_id', $this->clinic->id)->first();
        $this->assertEquals('Jane Doe', $patient->emergency_contact_name);
        $this->assertEquals('A+', $patient->blood_type);
    }

    /** @test */
    public function doctor_can_create_patient()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.smith@test.com',
            'phone' => '1112223333',
            'date_of_birth' => '1985-05-15',
            'gender' => 'female',
            'address' => '456 Test Ave, Test City'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_create_patient()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email' => 'bob.johnson@test.com',
            'phone' => '4445556666',
            'date_of_birth' => '1975-10-20',
            'gender' => 'male',
            'address' => '789 Test Blvd, Test City'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_create_patient()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie.brown@test.com',
            'phone' => '7778889999',
            'date_of_birth' => '1995-12-25',
            'gender' => 'male',
            'address' => '101 Test Rd, Test City'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_create_patient_with_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => 999, // Invalid clinic ID
            'first_name' => '', // Required field empty
            'email' => 'invalid-email', // Invalid email format
            'date_of_birth' => '2030-01-01', // Future date
            'gender' => 'invalid', // Invalid gender
            'blood_type' => 'Z+', // Invalid blood type
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_create_patient_with_duplicate_email()
    {
        // First create a patient
        $this->actingAs($this->adminUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'duplicate@test.com',
            'phone' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => '123 Test St'
        ]);

        // Try to create another with same email
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'duplicate@test.com', // Duplicate email
            'phone' => '0987654321',
            'date_of_birth' => '1992-02-02',
            'gender' => 'female',
            'address' => '456 Test Ave'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_create_patient_for_different_clinic_without_admin()
    {
        // Create another clinic
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

        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $otherClinic->id, // Different clinic
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'email' => 'test@other.com',
            'phone' => '1111111111',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => '123 Other St'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to create patients for this clinic'
            ]);
    }

    /** @test */
    public function patient_id_is_generated_automatically()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/patients', [
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Auto',
            'last_name' => 'Generated',
            'email' => 'auto@test.com',
            'phone' => '1231231234',
            'date_of_birth' => '1980-03-03',
            'gender' => 'male',
            'address' => 'Auto St'
        ]);

        $response->assertStatus(201);

        $patient = Patient::where('clinic_id', $this->clinic->id)
            ->where('patient_id', 'LIKE', 'TES-PAT-%')
            ->first();

        $this->assertNotNull($patient);
        $this->assertStringStartsWith('TES-PAT-', $patient->patient_id);
    }

    /** @test */
    public function admin_can_get_all_patients()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'patient_id',
                        'user' => [
                            'name',
                            'email'
                        ],
                        'clinic' => [
                            'name'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_clinic_patients()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_get_clinic_patients()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/patients');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_get_patients_list()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/patients');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_get_specific_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients/' . $this->patient->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->patient->id,
                    'patient_id' => $this->patient->patient_id
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'clinic',
                    'appointments',
                    'bills'
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_clinic_patient()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $this->patient->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_get_clinic_patient()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/patients/' . $this->patient->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function cannot_get_patient_from_different_clinic()
    {
        // Create another clinic and patient
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

        $otherPatient = Patient::create([
            'clinic_id' => $otherClinic->id,
            'user_id' => $this->patientUser->id, // Reuse user for simplicity
            'patient_id' => 'OTH-PAT-0001',
            'emergency_contact_name' => 'Other Contact',
            'emergency_contact_phone' => '1111111111'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $otherPatient->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ]);
    }

    /** @test */
    public function cannot_get_nonexistent_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Patient not found'
            ]);
    }

    /** @test */
    public function doctor_can_access_medical_records_in_patient_show()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $this->patient->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'medicalRecords'
                ]
            ]);
    }

    /** @test */
    public function staff_cannot_access_medical_records_in_patient_show()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/patients/' . $this->patient->id);

        $response->assertStatus(200)
            ->assertJsonMissing([
                'data' => [
                    'medicalRecords' => []
                ]
            ]);
    }

    /** @test */
    public function admin_can_update_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/patients/' . $this->patient->id, [
            'emergency_contact_name' => 'Updated Contact',
            'blood_type' => 'B+',
            'allergies' => 'Updated allergies',
            'insurance_provider' => 'Updated Insurance'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Patient updated successfully'
            ]);

        $this->patient->refresh();
        $this->assertEquals('Updated Contact', $this->patient->emergency_contact_name);
        $this->assertEquals('B+', $this->patient->blood_type);
    }

    /** @test */
    public function doctor_can_update_clinic_patient()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->put('/api/patients/' . $this->patient->id, [
            'medical_history' => 'Updated medical history'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_update_clinic_patient()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->put('/api/patients/' . $this->patient->id, [
            'allergies' => 'Staff updated allergies'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_update_patient()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->put('/api/patients/' . $this->patient->id, [
            'allergies' => 'Patient trying to update'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_update_patient_from_different_clinic()
    {
        // Create another clinic and patient
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

        $otherPatient = Patient::create([
            'clinic_id' => $otherClinic->id,
            'user_id' => $this->patientUser->id,
            'patient_id' => 'OTH-PAT-0001',
            'emergency_contact_name' => 'Other Contact'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->put('/api/patients/' . $otherPatient->id, [
            'allergies' => 'Trying to update other clinic patient'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to update this patient'
            ]);
    }

    /** @test */
    public function cannot_update_patient_with_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/patients/' . $this->patient->id, [
            'blood_type' => 'Invalid',
            'last_visit_date' => '2030-01-01', // Future date
            'allergies' => str_repeat('A', 1001) // Too long
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function admin_can_delete_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/patients/' . $this->patient->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Patient deleted successfully'
            ]);

        $this->assertSoftDeleted('patients', [
            'id' => $this->patient->id
        ]);
    }

    /** @test */
    public function doctor_cannot_delete_patient()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->delete('/api/patients/' . $this->patient->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only admins can delete patients'
            ]);
    }

    /** @test */
    public function staff_cannot_delete_patient()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->delete('/api/patients/' . $this->patient->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function patient_cannot_delete_patient()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->delete('/api/patients/' . $this->patient->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_delete_nonexistent_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/patients/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Patient not found'
            ]);
    }

    /** @test */
    public function can_get_patient_appointments()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => []
            ]);
    }

    /** @test */
    public function doctor_can_get_patient_appointments()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_get_patient_appointments()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_get_patient_appointments_endpoint()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/appointments');

        $response->assertStatus(403);
    }

    /** @test */
    public function can_get_patient_bills()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => []
            ]);
    }

    /** @test */
    public function doctor_can_get_patient_bills()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_get_patient_bills()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_get_patient_bills_endpoint()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/bills');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_get_patient_medical_records()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/medical-records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => []
            ]);
    }

    /** @test */
    public function doctor_can_get_patient_medical_records()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/medical-records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_cannot_get_patient_medical_records()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/medical-records');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ]);
    }

    /** @test */
    public function patient_cannot_get_patient_medical_records()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/medical-records');

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_access_medical_records_for_patient_from_different_clinic()
    {
        // Create another clinic and patient
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

        $otherPatient = Patient::create([
            'clinic_id' => $otherClinic->id,
            'user_id' => $this->patientUser->id,
            'patient_id' => 'OTH-PAT-0001',
            'emergency_contact_name' => 'Other Contact'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $otherPatient->id . '/medical-records');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ]);
    }
}