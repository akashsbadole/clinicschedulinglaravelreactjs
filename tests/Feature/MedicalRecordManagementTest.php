<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class MedicalRecordManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $adminUser;
    private $doctorUser;
    private $patientUser;
    private $clinic;
    private $patient;
    private $doctor;
    private $appointment;

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
            'is_active' => true,
        ]);

        // Create admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'clinic_id' => $this->clinic->id,
        ]);

        // Create doctor user
        $this->doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);

        // Create patient user
        $this->patientUser = User::create([
            'name' => 'Patient User',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        // Create doctor
        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->doctorUser->id,
            'doctor_id' => 'DOC-001',
            'specialization' => 'General Medicine',
            'license_number' => 'LIC-12345',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => '100.00',
            'is_accepting_new_patients' => true,
        ]);

        // Create patient
        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->patientUser->id,
            'patient_id' => 'PAT-001',
            'emergency_contact_name' => 'John Doe',
            'emergency_contact_phone' => '9876543210',
            'blood_type' => 'O+',
            'allergies' => 'None',
            'medical_history' => 'No major issues',
            'insurance_provider' => 'Test Insurance',
            'insurance_policy_number' => 'POL-12345',
        ]);

        // Create appointment
        $this->appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'APPT-001',
            'appointment_date' => now(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'status' => 'scheduled',
            'appointment_type' => 'consultation',
            'reason' => 'Routine checkup',
            'priority' => 'normal',
            'is_first_visit' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_medical_record()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Medical record created successfully',
            ]);

        $this->assertDatabaseHas('medical_records', [
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
        ]);
    }

    /** @test */
    public function doctor_can_create_medical_record()
    {
        Sanctum::actingAs($this->doctorUser);

        $response = $this->postJson('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'consultation',
            'content' => 'Test consultation notes',
            'severity' => 'low',
            'is_confidential' => false,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Medical record created successfully',
            ]);

        $this->assertDatabaseHas('medical_records', [
            'record_type' => 'consultation',
            'content' => 'Test consultation notes',
        ]);
    }

    /** @test */
    public function patient_cannot_create_medical_record()
    {
        Sanctum::actingAs($this->patientUser);

        $response = $this->postJson('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Unauthorized action.',
            ]);
    }

    /** @test */
    public function admin_can_get_all_medical_records()
    {
        // Create a medical record first
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/medical-records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function doctor_can_get_clinic_medical_records()
    {
        // Create a medical record first
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        Sanctum::actingAs($this->doctorUser);

        $response = $this->getJson('/api/medical-records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function patient_cannot_get_medical_records()
    {
        Sanctum::actingAs($this->patientUser);

        $response = $this->getJson('/api/medical-records');

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Unauthorized action.',
            ]);
    }

    /** @test */
    public function admin_can_get_single_medical_record()
    {
        // Create a medical record first
        $medicalRecord = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/medical-records/' . $medicalRecord->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $medicalRecord->id,
                    'record_type' => 'diagnosis',
                ],
            ]);
    }

    /** @test */
    public function admin_can_update_medical_record()
    {
        // Create a medical record first
        $medicalRecord = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        Sanctum::actingAs($this->adminUser);

        $response = $this->putJson('/api/medical-records/' . $medicalRecord->id, [
            'content' => 'Updated diagnosis content',
            'severity' => 'high',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Medical record updated successfully',
            ]);

        $this->assertDatabaseHas('medical_records', [
            'id' => $medicalRecord->id,
            'content' => 'Updated diagnosis content',
            'severity' => 'high',
        ]);
    }

    /** @test */
    public function admin_can_delete_medical_record()
    {
        // Create a medical record first
        $medicalRecord = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Test diagnosis content',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson('/api/medical-records/' . $medicalRecord->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Medical record deleted successfully',
            ]);

        $this->assertSoftDeleted('medical_records', [
            'id' => $medicalRecord->id,
        ]);
    }

    /** @test */
    public function admin_can_get_patient_medical_records()
    {
        // Create medical records for the patient
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Diagnosis record',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'prescription',
            'content' => 'Prescription record',
            'severity' => 'low',
            'is_confidential' => false,
        ]);

        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/medical-records/patient/' . $this->patient->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function admin_can_get_medical_records_by_type()
    {
        // Create different types of medical records
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Diagnosis record',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'prescription',
            'content' => 'Prescription record',
            'severity' => 'low',
            'is_confidential' => false,
        ]);

        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/medical-records/type/diagnosis');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function admin_can_create_consultation_note()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/medical-records/appointment/' . $this->appointment->id . '/consultation', [
            'content' => 'Consultation notes for the appointment',
            'severity' => 'medium',
            'is_confidential' => false,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Consultation note created successfully',
            ]);

        $this->assertDatabaseHas('medical_records', [
            'appointment_id' => $this->appointment->id,
            'record_type' => 'consultation',
            'content' => 'Consultation notes for the appointment',
        ]);
    }

    /** @test */
    public function doctor_can_create_prescription()
    {
        Sanctum::actingAs($this->doctorUser);

        $response = $this->postJson('/api/medical-records/patient/' . $this->patient->id . '/prescription', [
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'medications' => [
                [
                    'name' => 'Amoxicillin',
                    'dosage' => '500mg',
                    'frequency' => 'Every 8 hours',
                    'duration' => '7 days',
                ],
                [
                    'name' => 'Ibuprofen',
                    'dosage' => '200mg',
                    'frequency' => 'Every 6 hours as needed',
                    'duration' => '3 days',
                ],
            ],
            'instructions' => 'Take with food',
            'severity' => 'low',
            'is_confidential' => false,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Prescription created successfully',
            ]);

        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $this->patient->id,
            'record_type' => 'prescription',
        ]);
    }

    /** @test */
    public function admin_can_get_medical_record_statistics()
    {
        // Create different types of medical records
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Diagnosis record',
            'severity' => 'high',
            'is_confidential' => false,
        ]);

        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'prescription',
            'content' => 'Prescription record',
            'severity' => 'low',
            'is_confidential' => true,
        ]);

        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/medical-records/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'total_records',
                    'by_type',
                    'by_severity',
                    'confidential_records',
                ],
            ]);
    }
}