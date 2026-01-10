<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $doctorUser;
    protected $patientUser;
    protected $staffUser;
    protected $clinic;
    protected $doctor;
    protected $patient;
    protected $schedule;

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

        // Create doctor schedule
        $this->schedule = Schedule::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'slot_duration' => 30,
            'is_active' => true
        ]);
    }

    /** @test */
    public function admin_can_create_appointment()
    {
        // Create a schedule for Monday first
        Schedule::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
            'slot_duration' => 30,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:00',
            'appointment_type' => 'consultation',
            'reason' => 'Routine checkup',
            'notes' => 'First visit',
            'priority' => 'normal',
            'is_first_visit' => true
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Appointment created successfully'
            ]);

        $this->assertDatabaseHas('appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'scheduled'
        ]);
    }

    /** @test */
    public function doctor_can_create_appointment()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '11:00',
            'appointment_type' => 'consultation',
            'reason' => 'Follow-up',
            'priority' => 'normal'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_can_create_appointment()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '12:00',
            'appointment_type' => 'consultation',
            'reason' => 'Checkup'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_create_appointment()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '13:00',
            'appointment_type' => 'consultation'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function unauthorized_user_cannot_create_appointment()
    {
        // Create a user with no role that should be unauthorized
        $user = User::create([
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@test.com',
            'password' => bcrypt('password'),
            'role' => 'guest', // Invalid role
            'clinic_id' => $this->clinic->id
        ]);

        $response = $this->actingAs($user, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '14:00',
            'appointment_type' => 'consultation'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_create_appointment_with_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => 999, // Invalid clinic ID
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '15:00',
            'appointment_type' => 'consultation'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_create_appointment_outside_doctor_schedule()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(), // Monday
            'start_time' => '18:00', // After schedule ends at 17:00
            'appointment_type' => 'consultation'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Requested time is outside doctor\'s available schedule'
            ]);
    }

    /** @test */
    public function cannot_create_appointment_on_day_doctor_not_available()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Sunday')->toDateString(), // Sunday - no schedule
            'start_time' => '10:00',
            'appointment_type' => 'consultation'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Doctor is not available on this day'
            ]);
    }

    /** @test */
    public function cannot_create_duplicate_appointment()
    {
        // Create first appointment
        $appointment1 = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000001',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        // Try to create overlapping appointment
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:15', // Overlaps with first appointment
            'appointment_type' => 'consultation'
        ]);

        $response->assertStatus(409) // Conflict
            ->assertJson([
                'success' => false,
                'message' => 'Doctor already has an appointment scheduled during this time'
            ]);
    }

    /** @test */
    public function cannot_create_appointment_for_patient_with_conflict()
    {
        // Create first appointment
        $appointment1 = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000002',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        // Try to create overlapping appointment for same patient with different doctor
        $anotherDoctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->doctorUser->id,
            'doctor_id' => 'TST-DOC-0002',
            'specialization' => 'Neurology',
            'license_number' => 'LIC-67890',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => '150.00',
            'bio' => 'Another test doctor',
            'availability' => 'Monday-Friday',
            'is_accepting_new_patients' => true
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/appointments', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $anotherDoctor->id,
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '11:15', // Overlaps with first appointment
            'appointment_type' => 'consultation'
        ]);

        $response->assertStatus(409) // Conflict
            ->assertJson([
                'success' => false,
                'message' => 'Patient already has an appointment scheduled during this time'
            ]);
    }

    /** @test */
    public function admin_can_get_all_appointments()
    {
        // Create some appointments
        Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000003',
            'appointment_date' => now()->addDays(1)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'appointment_number',
                            'appointment_date',
                            'start_time',
                            'end_time',
                            'status',
                            'appointment_type'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_own_appointments()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_can_get_own_appointments()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function can_get_available_time_slots()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/appointments/doctor/' . $this->doctor->id . '/slots?date=' . now()->next('Monday')->toDateString());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    'doctor_id',
                    'date',
                    'day_of_week',
                    'schedule',
                    'available_slots',
                    'slot_duration'
                ]
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['available_slots']);
    }

    /** @test */
    public function cannot_get_slots_for_invalid_date()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/appointments/doctor/' . $this->doctor->id . '/slots?date=invalid-date');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function cannot_get_slots_for_past_date()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/appointments/doctor/' . $this->doctor->id . '/slots?date=' . now()->subDays(1)->toDateString());

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    /** @test */
    public function admin_can_update_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000004',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/appointments/' . $appointment->id, [
            'notes' => 'Updated notes',
            'priority' => 'high'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Appointment updated successfully'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'notes' => 'Updated notes',
            'priority' => 'high'
        ]);
    }

    /** @test */
    public function doctor_can_update_own_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->doctorUser->id,
            'appointment_number' => 'TST-APPT-000005',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->put('/api/appointments/' . $appointment->id, [
            'notes' => 'Doctor updated notes'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_update_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->staffUser->id,
            'appointment_number' => 'TST-APPT-000006',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')->put('/api/appointments/' . $appointment->id, [
            'notes' => 'Staff updated notes'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_update_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000007',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '13:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->put('/api/appointments/' . $appointment->id, [
            'notes' => 'Patient trying to update'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_cancel_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000008',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/appointments/' . $appointment->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Appointment cancelled successfully'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function patient_can_cancel_own_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->patientUser->id,
            'appointment_number' => 'TST-APPT-000009',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '15:00:00',
            'end_time' => '15:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->delete('/api/appointments/' . $appointment->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function cannot_cancel_already_cancelled_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000010',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '16:30:00',
            'status' => 'cancelled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/appointments/' . $appointment->id);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Appointment cannot be cancelled as it is already cancelled'
            ]);
    }

    /** @test */
    public function doctor_can_mark_appointment_as_completed()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000011',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/complete');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Appointment marked as completed successfully'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function admin_can_mark_appointment_as_completed()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000012',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/complete');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function patient_cannot_mark_appointment_as_completed()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000013',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/complete');

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_complete_already_completed_appointment()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000014',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '13:30:00',
            'status' => 'completed',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/complete');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only scheduled appointments can be marked as completed'
            ]);
    }

    /** @test */
    public function staff_can_mark_appointment_as_no_show()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000015',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '14:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/no-show');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Appointment marked as no-show successfully'
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'no-show'
        ]);
    }

    /** @test */
    public function doctor_can_mark_appointment_as_no_show()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000016',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '15:00:00',
            'end_time' => '15:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/no-show');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'no-show'
        ]);
    }

    /** @test */
    public function patient_cannot_mark_appointment_as_no_show()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000017',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '16:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/no-show');

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_mark_completed_appointment_as_no_show()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000018',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'completed',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')->post('/api/appointments/' . $appointment->id . '/no-show');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only scheduled appointments can be marked as no-show'
            ]);
    }

    /** @test */
    public function admin_can_get_doctor_appointments()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000019',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/doctors/' . $this->doctor->id . '/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'appointment_number',
                        'appointment_date',
                        'start_time',
                        'end_time',
                        'status'
                    ]
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_own_appointments_via_doctor_endpoint()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->doctorUser->id,
            'appointment_number' => 'TST-APPT-000020',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/doctors/' . $this->doctor->id . '/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_get_doctor_appointments()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->staffUser->id,
            'appointment_number' => 'TST-APPT-000021',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'status' => 'scheduled',
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')->get('/api/doctors/' . $this->doctor->id . '/appointments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_get_doctor_appointments()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/doctors/' . $this->doctor->id . '/appointments');

        $response->assertStatus(403);
    }
}