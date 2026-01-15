<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class BillManagementTest extends TestCase
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

        // Create a completed appointment for testing
        $this->appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000001',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'completed',
            'appointment_type' => 'consultation'
        ]);
    }

    /** @test */
    public function admin_can_create_bill()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 5.00,
            'total_amount' => 105.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
            'items' => [
                [
                    'service_name' => 'Consultation',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_price' => 100.00
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Bill created successfully'
            ]);

        $this->assertDatabaseHas('bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'amount' => 100.00,
            'status' => 'unpaid'
        ]);
    }

    /** @test */
    public function doctor_can_create_bill()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->post('/api/bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 150.00,
            'description' => 'Doctor created bill'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_create_bill()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->post('/api/bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 75.00,
            'description' => 'Staff created bill'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_create_bill()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 50.00,
            'description' => 'Patient trying to create bill'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_create_bill_with_invalid_data()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/bills', [
            'clinic_id' => 999, // Invalid clinic ID
            'patient_id' => $this->patient->id,
            'bill_date' => 'invalid-date',
            'due_date' => now()->subDays(1)->toDateString(), // Due date before bill date
            'amount' => -10.00, // Negative amount
            'status' => 'invalid-status'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'clinic_id',
                    'bill_date',
                    'amount',
                    'status'
                ]
            ]);
    }

    /** @test */
    public function cannot_create_bill_without_required_fields()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/bills', [
            // Missing required fields
            'description' => 'Missing required fields'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'clinic_id',
                    'bill_date',
                    'amount',
                    'status'
                ]
            ]);
    }

    /** @test */
    public function admin_can_get_all_bills()
    {
        // Create a bill
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 5.00,
            'total_amount' => 105.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'bill_number',
                            'bill_date',
                            'amount',
                            'status'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function doctor_can_get_bills_for_their_patients()
    {
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000002',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 120.00,
            'total_amount' => 120.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/api/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_can_get_own_bills()
    {
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000003',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 80.00,
            'total_amount' => 80.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_access_other_patients_bills()
    {
        $otherPatient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->patientUser->id, // Same user but different patient record
            'patient_id' => 'TST-PAT-0002',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '9876543211',
            'blood_type' => 'A+',
            'allergies' => 'None',
            'medical_history' => 'No issues',
            'insurance_provider' => null,
            'insurance_policy_number' => null,
            'last_visit_date' => now()
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->get('/api/patients/' . $otherPatient->id . '/bills');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000004',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 5.00,
            'total_amount' => 105.00,
            'status' => 'unpaid',
            'description' => 'Original description'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->put('/api/bills/' . $bill->id, [
            'description' => 'Updated description',
            'discount' => 10.00,
            'total_amount' => 100.00
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bill updated successfully'
            ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'description' => 'Updated description',
            'discount' => 10.00,
            'total_amount' => 100.00
        ]);
    }

    /** @test */
    public function doctor_can_update_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->doctorUser->id,
            'bill_number' => 'TST-BILL-000005',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 150.00,
            'total_amount' => 150.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->put('/api/bills/' . $bill->id, [
            'description' => 'Doctor updated bill'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function staff_can_update_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->staffUser->id,
            'bill_number' => 'TST-BILL-000006',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 75.00,
            'total_amount' => 75.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')->put('/api/bills/' . $bill->id, [
            'description' => 'Staff updated bill'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function patient_cannot_update_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000007',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 50.00,
            'total_amount' => 50.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->put('/api/bills/' . $bill->id, [
            'description' => 'Patient trying to update'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_update_bill_with_invalid_data()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000008',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->putJson('/api/bills/' . $bill->id, [
            'due_date' => now()->subDays(1)->toDateString(), // Invalid due date
            'amount' => -50.00, // Negative amount
            'status' => 'invalid-status'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'due_date',
                    'amount',
                    'status'
                ]
            ]);
    }

    /** @test */
    public function admin_can_delete_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000009',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->delete('/api/bills/' . $bill->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bill deleted successfully'
            ]);

        $this->assertSoftDeleted('bills', [
            'id' => $bill->id
        ]);
    }

    /** @test */
    public function non_admin_cannot_delete_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000010',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->delete('/api/bills/' . $bill->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function can_process_full_payment()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000011',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 5.00,
            'total_amount' => 105.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/' . $bill->id . '/pay', [
            'amount' => 105.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'transaction_reference' => 'TXN-12345',
            'notes' => 'Full payment'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Payment processed successfully'
            ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'paid',
            'payment_method' => 'cash'
        ]);
    }

    /** @test */
    public function can_process_partial_payment()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000012',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 5.00,
            'total_amount' => 105.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/' . $bill->id . '/pay', [
            'amount' => 50.00,
            'payment_method' => 'credit_card',
            'payment_date' => now()->toDateString(),
            'transaction_reference' => 'TXN-67890',
            'notes' => 'Partial payment'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'partially_paid'
        ]);
    }

    /** @test */
    public function cannot_process_payment_for_already_paid_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000013',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'paid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/' . $bill->id . '/pay', [
            'amount' => 50.00,
            'payment_method' => 'cash'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Bill is already paid'
            ]);
    }

    /** @test */
    public function cannot_process_payment_exceeding_remaining_amount()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000014',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/' . $bill->id . '/pay', [
            'amount' => 150.00, // Exceeds total
            'payment_method' => 'cash'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Payment amount exceeds remaining bill amount'
            ]);
    }

    /** @test */
    public function patient_cannot_process_payment()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000015',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/bills/' . $bill->id . '/pay', [
            'amount' => 100.00,
            'payment_method' => 'cash'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function can_create_bill_from_completed_appointment()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/from-appointment/' . $this->appointment->id);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Bill created from appointment successfully'
            ]);

        $this->assertDatabaseHas('bills', [
            'appointment_id' => $this->appointment->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'unpaid'
        ]);
    }

    /** @test */
    public function cannot_create_bill_from_incomplete_appointment()
    {
        $incompleteAppointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000002',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled', // Not completed
            'appointment_type' => 'consultation'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/from-appointment/' . $incompleteAppointment->id);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Can only create bill for completed appointments'
            ]);
    }

    /** @test */
    public function cannot_create_duplicate_bill_from_same_appointment()
    {
        // Create first bill
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000016',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/from-appointment/' . $this->appointment->id);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Bill already exists for this appointment'
            ]);
    }

    /** @test */
    public function can_cancel_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000017',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/' . $bill->id . '/cancel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bill cancelled successfully'
            ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function cannot_cancel_paid_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000018',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'paid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills/' . $bill->id . '/cancel');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot cancel a paid bill'
            ]);
    }

    /** @test */
    public function patient_cannot_cancel_bill()
    {
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000019',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/api/bills/' . $bill->id . '/cancel');

        $response->assertStatus(403);
    }

    /** @test */
    public function can_get_patient_bills()
    {
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000020',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/patients/' . $this->patient->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'bill_number',
                        'amount',
                        'status'
                    ]
                ]
            ]);
    }

    /** @test */
    public function can_get_doctor_bills()
    {
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000021',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/doctors/' . $this->doctor->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function can_get_appointment_bills()
    {
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000022',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/appointments/' . $this->appointment->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function can_get_billing_statistics()
    {
        // Create some bills with different statuses
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000023',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'paid',
            'payment_method' => 'cash'
        ]);

        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000024',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 150.00,
            'total_amount' => 150.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/bills/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    'total_bills',
                    'total_amount',
                    'paid_amount',
                    'unpaid_amount',
                    'payment_methods',
                    'status_distribution'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_bills']);
        $this->assertEquals(250.00, $data['total_amount']);
        $this->assertEquals(100.00, $data['paid_amount']);
        $this->assertEquals(150.00, $data['unpaid_amount']);
    }

    /** @test */
    public function can_get_service_catalog()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/bills/service-catalog');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'base_price'
                    ]
                ]
            ]);
    }

    /** @test */
    public function bill_with_insurance_gets_discount()
    {
        // Patient has insurance
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id, // Has insurance
            'doctor_id' => $this->doctor->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00
            // Tax and discount will be calculated automatically
        ]);

        $response->assertStatus(201);

        $bill = Bill::latest()->first();
        $this->assertEquals(20.00, $bill->discount); // 20% discount for insured patients
        $this->assertEquals(10.00, $bill->tax); // 10% tax
        $this->assertEquals(90.00, $bill->total_amount); // 100 + 10 - 20
    }

    /** @test */
    public function follow_up_appointment_gets_additional_discount()
    {
        $followUpAppointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TST-APPT-000003',
            'appointment_date' => now()->next('Monday')->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'status' => 'completed',
            'appointment_type' => 'follow-up'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $followUpAppointment->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00
        ]);

        $response->assertStatus(201);

        $bill = Bill::latest()->first();
        $this->assertEquals(30.00, $bill->discount); // 20% insurance + 10% follow-up
        $this->assertEquals(80.00, $bill->total_amount); // 100 + 10 - 30
    }

    /** @test */
    public function bill_number_is_generated_automatically()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/api/bills', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00
        ]);

        $response->assertStatus(201);

        $bill = Bill::latest()->first();
        $this->assertStringStartsWith('TES-BILL-', $bill->bill_number); // Based on clinic name "Test Clinic"
        $this->assertStringEndsWith('000001', $bill->bill_number);
    }

    /** @test */
    public function can_filter_bills_by_status()
    {
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000025',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'paid'
        ]);

        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000026',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 150.00,
            'total_amount' => 150.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/bills?status=paid');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('paid', $data[0]['status']);
    }

    /** @test */
    public function can_filter_bills_by_date_range()
    {
        $pastDate = now()->subDays(10)->toDateString();
        $futureDate = now()->addDays(10)->toDateString();

        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TST-BILL-000027',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'total_amount' => 100.00,
            'status' => 'unpaid'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/api/bills?start_date=' . $pastDate . '&end_date=' . $futureDate);

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
    }
}