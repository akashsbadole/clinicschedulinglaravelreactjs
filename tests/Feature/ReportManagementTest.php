<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $doctorUser;
    protected $staffUser;
    protected $clinic;
    protected $patient;
    protected $doctor;
    protected $appointment;
    protected $bill;

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

        // Create appointments
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

        // Create no-show appointment
        Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TEST-APPT-0002',
            'appointment_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'status' => 'no-show',
            'appointment_type' => 'consultation',
            'reason' => 'Follow-up',
            'priority' => 'normal',
            'is_first_visit' => false,
        ]);

        // Create cancelled appointment
        Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->adminUser->id,
            'appointment_number' => 'TEST-APPT-0003',
            'appointment_date' => now()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
            'status' => 'cancelled',
            'appointment_type' => 'consultation',
            'reason' => 'Patient cancelled',
            'priority' => 'normal',
            'is_first_visit' => false,
        ]);

        // Create bills
        $this->bill = Bill::create([
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
            'payment_date' => now()->toDateString(),
            'description' => 'Consultation fee',
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
            'status' => 'paid',
            'payment_method' => 'credit_card',
            'payment_date' => now()->toDateString(),
            'description' => 'Follow-up consultation',
        ]);
    }

    /** @test */
    public function test_admin_can_get_dashboard_statistics()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/reports/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_patients',
                'total_doctors',
                'total_appointments_this_month',
                'total_revenue_this_month',
                'pending_bills',
                'today_appointments',
            ]);
    }

    /** @test */
    public function test_doctor_can_get_dashboard_statistics()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')
            ->get('/api/reports/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_staff_can_get_dashboard_statistics()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->get('/api/reports/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_patient_cannot_access_reports()
    {
        $response = $this->actingAs($this->patientUser, 'sanctum')
            ->get('/api/reports/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_get_financial_report()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/reports/financial');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'revenue',
                'doctor_earnings' => [
                    '*' => [
                        'doctor_name',
                        'earnings',
                    ],
                ],
                'payment_methods' => [
                    '*' => [
                        'payment_method',
                        'count',
                        'total',
                    ],
                ],
            ]);
    }

    /** @test */
    public function test_admin_can_get_operational_report()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/reports/operational');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'no_show_rate',
                'total_appointments',
                'no_shows',
                'doctor_utilization' => [
                    '*' => [
                        'doctor_name',
                        'appointments',
                    ],
                ],
                'cancellations',
            ]);
    }

    /** @test */
    public function test_admin_can_export_financial_report()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/reports/export/financial');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="financial_report_' . now()->format('Y-m-d') . '.csv"');
    }

    /** @test */
    public function test_admin_can_export_operational_report()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/reports/export/operational');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="operational_report_' . now()->format('Y-m-d') . '.csv"');
    }

    /** @test */
    public function test_reports_filter_by_date_range()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->get('/api/reports/financial?start_date=' . now()->startOfMonth()->toDateString() . '&end_date=' . now()->endOfMonth()->toDateString());

        $response->assertStatus(200);
    }
}