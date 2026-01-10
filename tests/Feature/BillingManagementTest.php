<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BillingManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $doctorUser;
    protected $staffUser;
    protected $patientUser;
    protected $clinic;
    protected $patient;
    protected $doctor;
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

        // Create staff user
        $this->staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
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

        // Create patient
        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->patientUser->id,
            'patient_id' => 'TEST-PAT-0001',
            'emergency_contact_name' => 'John Doe',
            'emergency_contact_phone' => '9876543210',
            'blood_type' => 'O+',
            'allergies' => 'None',
            'medical_history' => 'No major issues',
            'insurance_provider' => 'Test Insurance',
            'insurance_policy_number' => 'INS123456',
        ]);

        // Create doctor
        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->doctorUser->id,
            'doctor_id' => 'TEST-DOC-0001',
            'specialization' => 'General Medicine',
            'license_number' => 'LIC12345',
            'qualifications' => 'MD',
            'years_of_experience' => 5,
            'consultation_fee' => 100.00,
            'bio' => 'Test doctor bio',
            'is_accepting_new_patients' => true,
        ]);

        // Create appointment
        $this->appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TEST-APPT-0001',
            'appointment_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
            'appointment_type' => 'consultation',
            'reason' => 'Routine checkup',
            'priority' => 'normal',
            'is_first_visit' => false,
        ]);
    }

    /** @test */
    public function test_admin_can_create_bill()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->post('/api/bills', [
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'appointment_id' => $this->appointment->id,
                'bill_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'amount' => 100.00,
                'description' => 'Test bill description',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Bill created successfully',
            ]);

        $this->assertDatabaseHas('bills', [
            'patient_id' => $this->patient->id,
            'status' => 'unpaid',
        ]);
    }

    /** @test */
    public function test_doctor_can_create_bill()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')
            ->post('/api/bills', [
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'appointment_id' => $this->appointment->id,
                'bill_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'amount' => 100.00,
                'description' => 'Test bill description',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Bill created successfully',
            ]);
    }

    /** @test */
    public function test_patient_cannot_create_bill()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')
            ->post('/api/bills', [
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'appointment_id' => $this->appointment->id,
                'bill_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'amount' => 100.00,
                'description' => 'Test bill description',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to create bills',
            ]);
    }

    /** @test */
    public function test_admin_can_get_all_bills()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data.data');
    }

    /** @test */
    public function test_admin_can_get_single_bill()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/bills/' . $bill->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $bill->id,
                    'bill_number' => 'TEST-BILL-0001',
                    'status' => 'unpaid',
                ],
            ]);
    }

    /** @test */
    public function test_admin_can_update_bill()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->put('/api/bills/' . $bill->id, [
                'status' => 'paid',
                'payment_method' => 'cash',
                'payment_date' => now()->toDateString(),
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bill updated successfully',
            ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'paid',
            'payment_method' => 'cash',
        ]);
    }

    /** @test */
    public function test_admin_can_delete_bill()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->delete('/api/bills/' . $bill->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bill deleted successfully',
            ]);

        $this->assertSoftDeleted('bills', [
            'id' => $bill->id,
        ]);
    }

    /** @test */
    public function test_staff_can_process_payment()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->post('/api/bills/' . $bill->id . '/pay', [
                'amount' => 110.00,
                'payment_method' => 'credit_card',
                'transaction_reference' => 'TRANS123456',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Payment processed successfully',
            ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'paid',
            'payment_method' => 'credit_card',
        ]);
    }

    /** @test */
    public function test_admin_can_cancel_bill()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->post('/api/bills/' . $bill->id . '/cancel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Bill cancelled successfully',
            ]);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function test_admin_can_create_bill_from_appointment()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->post('/api/bills/from-appointment/' . $this->appointment->id);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Bill created from appointment successfully',
            ]);

        $this->assertDatabaseHas('bills', [
            'appointment_id' => $this->appointment->id,
            'status' => 'unpaid',
        ]);
    }

    /** @test */
    public function test_patient_can_get_their_own_bills()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')
            ->get('/api/patients/' . $this->patient->id . '/bills');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function test_patient_cannot_access_other_patients_bills()
    {
        // Create another patient
        $otherPatientUser = User::create([
            'name' => 'Other Patient',
            'email' => 'otherpatient@test.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
            'clinic_id' => $this->clinic->id,
        ]);

        $otherPatient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $otherPatientUser->id,
            'patient_id' => 'TEST-PAT-0002',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '9876543211',
            'blood_type' => 'A+',
            'allergies' => 'Penicillin',
            'medical_history' => 'Allergies',
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')
            ->get('/api/patients/' . $otherPatient->id . '/bills');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to access this patient',
            ]);
    }

    /** @test */
    public function test_admin_can_get_billing_statistics()
    {
        // Create some bills
        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'paid',
            'payment_method' => 'cash',
            'description' => 'Paid bill',
        ]);

        Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0002',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 200.00,
            'tax' => 20.00,
            'discount' => 0.00,
            'total_amount' => 220.00,
            'status' => 'unpaid',
            'description' => 'Unpaid bill',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/bills/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_bills' => 2,
                    'total_amount' => 330.00,
                    'paid_amount' => 110.00,
                    'unpaid_amount' => 220.00,
                ],
            ]);
    }

    /** @test */
    public function test_admin_can_get_service_catalog()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/bills/service-catalog');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(6, 'data'); // Should have 6 services
    }

    /** @test */
    public function test_bill_validation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->post('/api/bills', [
                // Missing required fields
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                // Missing amount, bill_date, due_date
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'bill_date', 'due_date']);
    }

    /** @test */
    public function test_payment_validation()
    {
        // Create a bill first
        $bill = Bill::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'created_by' => $this->adminUser->id,
            'bill_number' => 'TEST-BILL-0001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'amount' => 100.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
            'description' => 'Test bill',
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->post('/api/bills/' . $bill->id . '/pay', [
                // Missing required fields
                // Missing amount and payment_method
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payment_method']);
    }
}