<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class MedicalRecordManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $doctorUser;
    protected $patientUser;
    protected $staffUser;
    protected $clinic;
    protected $doctor;
    protected $patient;
    protected $appointment;

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

        // Create appointment
        $this->appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-0001',
            'appointment_date' => now()->addDays(1)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);
    }

    /** @test */
    public function admin_can_create_medical_record()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'consultation',
            'content' => 'Patient presented with chest pain. Diagnosis: stable angina.',
            'severity' => 'medium',
            'is_confidential' => false
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Medical record created successfully'
            ]);

        $this->assertDatabaseHas('medical_records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'consultation',
            'severity' => 'medium'
        ]);
    }

    /** @test */
    public function doctor_can_create_medical_record()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'diagnosis',
            'content' => 'Hypertension diagnosis confirmed.',
            'severity' => 'high'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_cannot_create_medical_record()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->post('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'notes',
            'content' => 'Test notes'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function patient_cannot_create_medical_record()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'notes',
            'content' => 'Patient notes'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_create_medical_record_with_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/medical-records', [
            'clinic_id' => 999, // Invalid clinic ID
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'invalid_type', // Invalid record type
            'content' => ''
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_create_medical_record_for_different_clinic()
    {
        $anotherClinic = Clinic::create([
            'name' => 'Another Clinic',
            'address' => '456 Another St',
            'city' => 'Another City',
            'state' => 'Another State',
            'postal_code' => '67890',
            'country' => 'Another Country',
            'phone' => '0987654321',
            'email' => 'another@clinic.com',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/medical-records', [
            'clinic_id' => $anotherClinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'notes',
            'content' => 'Test content'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to create records for this clinic'
            ]);
    }

    /** @test */
    public function admin_can_get_all_medical_records()
    {
        // Create some medical records
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'consultation',
            'content' => 'Consultation notes',
            'severity' => 'medium'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/medical-records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'record_type',
                        'content',
                        'severity',
                        'is_confidential'
                    ]
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_medical_records_from_own_clinic()
    {
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'diagnosis',
            'content' => 'Diagnosis notes',
            'severity' => 'high'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/medical-records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_cannot_get_medical_records()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/medical-records');

        $response->assertStatus(403);
    }

    /** @test */
    public function patient_cannot_get_medical_records()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/medical-records');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_get_specific_medical_record()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'prescription',
            'content' => 'Prescription details',
            'severity' => 'medium'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/medical-records/' . $record->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $record->id,
                    'record_type' => 'prescription'
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_medical_record_from_own_clinic()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'lab_result',
            'content' => 'Lab results',
            'severity' => 'low'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/medical-records/' . $record->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function cannot_get_medical_record_from_different_clinic()
    {
        $anotherClinic = Clinic::create([
            'name' => 'Another Clinic',
            'address' => '456 Another St',
            'city' => 'Another City',
            'state' => 'Another State',
            'postal_code' => '67890',
            'country' => 'Another Country',
            'phone' => '0987654321',
            'email' => 'another@clinic.com',
            'is_active' => true
        ]);

        $record = MedicalRecord::create([
            'clinic_id' => $anotherClinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'notes',
            'content' => 'Notes from another clinic'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/medical-records/' . $record->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_medical_record()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'consultation',
            'content' => 'Original content',
            'severity' => 'medium'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/medical-records/' . $record->id, [
            'content' => 'Updated content',
            'severity' => 'high',
            'is_confidential' => true
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Medical record updated successfully'
            ]);

        $record->refresh();
        $this->assertEquals('Updated content', $record->content);
        $this->assertEquals('high', $record->severity);
        $this->assertTrue($record->is_confidential);
    }

    /** @test */
    public function doctor_can_update_medical_record_from_own_clinic()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'diagnosis',
            'content' => 'Original diagnosis',
            'severity' => 'medium'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->put('/api/medical-records/' . $record->id, [
            'content' => 'Updated diagnosis'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_cannot_update_medical_record()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'notes',
            'content' => 'Original notes'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')->put('/api/medical-records/' . $record->id, [
            'content' => 'Updated notes'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_update_medical_record_with_invalid_data()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'consultation',
            'content' => 'Original content'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/medical-records/' . $record->id, [
            'record_type' => 'invalid_type',
            'severity' => 'invalid_severity'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function admin_can_delete_medical_record()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'prescription',
            'content' => 'Prescription to delete'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/medical-records/' . $record->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Medical record deleted successfully'
            ]);

        $this->assertSoftDeleted('medical_records', [
            'id' => $record->id
        ]);
    }

    /** @test */
    public function doctor_cannot_delete_medical_record()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'notes',
            'content' => 'Notes to delete'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->delete('/api/medical-records/' . $record->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_delete_medical_record_from_different_clinic()
    {
        $anotherClinic = Clinic::create([
            'name' => 'Another Clinic',
            'address' => '456 Another St',
            'city' => 'Another City',
            'state' => 'Another State',
            'postal_code' => '67890',
            'country' => 'Another Country',
            'phone' => '0987654321',
            'email' => 'another@clinic.com',
            'is_active' => true
        ]);

        $record = MedicalRecord::create([
            'clinic_id' => $anotherClinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'consultation',
            'content' => 'Record from another clinic'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/medical-records/' . $record->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function can_get_patient_medical_records()
    {
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'consultation',
            'content' => 'Patient consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/medical-records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function cannot_get_medical_records_for_patient_from_different_clinic()
    {
        $anotherClinic = Clinic::create([
            'name' => 'Another Clinic',
            'address' => '456 Another St',
            'city' => 'Another City',
            'state' => 'Another State',
            'postal_code' => '67890',
            'country' => 'Another Country',
            'phone' => '0987654321',
            'email' => 'another@clinic.com',
            'is_active' => true
        ]);

        $anotherPatient = Patient::create([
            'clinic_id' => $anotherClinic->id,
            'user_id' => $this->patientUser->id,
            'patient_id' => 'TST-PAT-0002',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '1234567890'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/patients/' . $anotherPatient->id . '/medical-records');

        $response->assertStatus(403);
    }

    /** @test */
    public function can_get_medical_records_by_type()
    {
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'prescription',
            'content' => 'Prescription record'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/medical-records/type/prescription');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function cannot_get_medical_records_by_invalid_type()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/medical-records/type/invalid_type');

        $response->assertStatus(400);
    }

    /** @test */
    public function can_get_appointment_medical_records()
    {
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'consultation',
            'content' => 'Appointment consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/medical-records/appointment/' . $this->appointment->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function can_create_consultation_note_for_appointment()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/medical-records/appointment/' . $this->appointment->id . '/consultation', [
            'content' => 'Consultation note content',
            'severity' => 'medium',
            'is_confidential' => false
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Consultation note created successfully'
            ]);

        $record = MedicalRecord::where('appointment_id', $this->appointment->id)->first();
        $this->assertEquals('consultation', $record->record_type);
        $this->assertEquals('Consultation note content', $record->content);
    }

    /** @test */
    public function can_create_prescription_for_patient()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/medical-records/patient/' . $this->patient->id . '/prescription', [
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'medications' => [
                [
                    'name' => 'Aspirin',
                    'dosage' => '100mg',
                    'frequency' => 'Once daily',
                    'duration' => '30 days'
                ]
            ],
            'instructions' => 'Take with food',
            'severity' => 'medium'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Prescription created successfully'
            ]);

        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $this->patient->id,
            'record_type' => 'prescription'
        ]);
    }

    /** @test */
    public function cannot_create_prescription_with_invalid_medications()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/patients/' . $this->patient->id . '/prescription', [
            'doctor_id' => $this->doctor->id,
            'medications' => [
                [
                    'name' => '',
                    'dosage' => '100mg',
                    'frequency' => 'Once daily',
                    'duration' => '30 days'
                ]
            ]
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function can_get_medical_record_statistics()
    {
        // Create some records
        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'consultation',
            'content' => 'Consultation',
            'severity' => 'medium',
            'is_confidential' => true
        ]);

        MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'prescription',
            'content' => 'Prescription',
            'severity' => 'high'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/medical-records/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_records' => 2,
                    'confidential_records' => 1
                ]
            ]);
    }

    /** @test */
    public function creating_consultation_record_updates_patient_last_visit_date()
    {
        $oldVisitDate = $this->patient->last_visit_date;

        $this->actingAs($this->adminUser, 'sanctum')->post('/api/medical-records', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'record_type' => 'consultation',
            'content' => 'Consultation record',
            'severity' => 'medium'
        ]);

        $this->patient->refresh();
        $this->assertNotEquals($oldVisitDate, $this->patient->last_visit_date);
    }

    /** @test */
    public function content_is_encrypted_for_confidential_records()
    {
        $record = MedicalRecord::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'record_type' => 'consultation',
            'content' => 'Confidential information',
            'is_confidential' => true
        ]);

        // The content should be encrypted in the database
        $this->assertNotEquals('Confidential information', $record->getRawOriginal('content'));
    }
}