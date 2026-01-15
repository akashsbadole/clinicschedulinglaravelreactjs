<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test clinic
        $clinic = Clinic::create([
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
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'clinic_id' => $clinic->id
        ]);

        // Create doctor user
        $doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@test.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'specialization' => 'Cardiology',
            'license_number' => 'LIC-12345'
        ]);

        // Create doctor record
        $doctor = Doctor::create([
            'clinic_id' => $clinic->id,
            'user_id' => $doctorUser->id,
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
        $patientUser = User::create([
            'name' => 'Patient User',
            'email' => 'patient@test.com',
            'password' => Hash::make('password'),
            'role' => 'patient',
            'clinic_id' => $clinic->id
        ]);

        // Create patient record
        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'user_id' => $patientUser->id,
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
        $staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'clinic_id' => $clinic->id
        ]);

        // Keep the original test user for backward compatibility
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed services and settings
        $this->call([
            ServiceSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
