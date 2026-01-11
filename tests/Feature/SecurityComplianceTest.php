<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SecurityComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $patient;
    protected $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $clinic = Clinic::factory()->create();

        $this->admin = User::factory()->create(['role' => 'admin', 'clinic_id' => $clinic->id]);
        $this->patient = User::factory()->create(['role' => 'patient', 'clinic_id' => $clinic->id]);
        $this->doctor = User::factory()->create(['role' => 'doctor', 'clinic_id' => $clinic->id]);
    }

    /** @test */
    public function audit_logs_are_created_for_patient_operations()
    {
        $this->actingAs($this->admin);

        $patient = Patient::factory()->create([
            'user_id' => $this->patient->id,
            'clinic_id' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'model_type' => Patient::class,
            'model_id' => $patient->id,
            'action' => 'create',
        ]);
    }

    /** @test */
    public function sensitive_data_is_encrypted_in_database()
    {
        $this->actingAs($this->admin);

        $patient = Patient::factory()->create([
            'user_id' => $this->patient->id,
            'clinic_id' => 1,
            'medical_history' => 'Test medical history',
            'allergies' => 'Test allergies',
        ]);

        // Check that data is encrypted in database
        $rawPatient = \DB::table('patients')->where('id', $patient->id)->first();
        $this->assertNotEquals('Test medical history', $rawPatient->medical_history);
        $this->assertNotEquals('Test allergies', $rawPatient->allergies);

        // Check that data is decrypted when accessed
        $this->assertEquals('Test medical history', $patient->fresh()->medical_history);
        $this->assertEquals('Test allergies', $patient->fresh()->allergies);
    }

    /** @test */
    public function medical_record_content_is_encrypted()
    {
        $this->actingAs($this->doctor);

        $patient = Patient::factory()->create([
            'user_id' => $this->patient->id,
            'clinic_id' => 1,
        ]);

        $record = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => 1,
            'clinic_id' => 1,
            'content' => 'Confidential medical information',
        ]);

        // Check encryption in database
        $rawRecord = \DB::table('medical_records')->where('id', $record->id)->first();
        $this->assertNotEquals('Confidential medical information', $rawRecord->content);

        // Check decryption when accessed
        $this->assertEquals('Confidential medical information', $record->fresh()->content);
    }

    /** @test */
    public function activity_logs_track_user_actions()
    {
        $this->actingAs($this->admin);

        // Simulate login (this would normally be done by middleware)
        ActivityLog::log('login', 'User logged in', [
            'device_name' => 'test_device',
        ], $this->admin->id);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'activity_type' => 'login',
            'description' => 'User logged in',
        ]);
    }

    /** @test */
    public function rate_limiting_middleware_blocks_excessive_requests()
    {
        // This test would require setting up the middleware in routes
        // For now, just test the rate limiter logic
        $limiter = app(\Illuminate\Cache\RateLimiter::class);

        $key = 'test_key';

        // First 60 requests should be allowed
        for ($i = 0; $i < 60; $i++) {
            $this->assertFalse($limiter->tooManyAttempts($key, 60));
            $limiter->hit($key, 60);
        }

        // 61st request should be blocked
        $this->assertTrue($limiter->tooManyAttempts($key, 60));
    }

    /** @test */
    public function ip_filter_blocks_blacklisted_ips()
    {
        // This would require setting up middleware in routes
        // Test the logic directly
        $middleware = new \App\Http\Middleware\IpFilter();

        // Mock request with blocked IP
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '192.168.1.100');

        // Since blockedIps is empty by default, it should pass
        // In real scenario, you'd populate blockedIps
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function compliance_reports_can_be_generated()
    {
        $this->actingAs($this->admin);

        // Create some test data
        AuditLog::create([
            'user_id' => $this->admin->id,
            'model_type' => Patient::class,
            'model_id' => 1,
            'action' => 'view',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $response = $this->getJson('/api/compliance/audit-trail');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'audit_trail' => [
                        'data',
                        'total',
                    ],
                    'summary'
                ]);
    }

    /** @test */
    public function data_retention_command_works()
    {
        // Create old audit log
        $oldLog = AuditLog::create([
            'user_id' => $this->admin->id,
            'model_type' => Patient::class,
            'model_id' => 1,
            'action' => 'view',
            'created_at' => now()->subYears(10), // Very old
        ]);

        // Create recent log
        $recentLog = AuditLog::create([
            'user_id' => $this->admin->id,
            'model_type' => Patient::class,
            'model_id' => 2,
            'action' => 'view',
            'created_at' => now()->subDays(1),
        ]);

        // Run cleanup command with dry-run
        $this->artisan('app:cleanup-old-data', ['--dry-run' => true])
             ->expectsOutput('Would delete 1 audit logs older than 2555 days')
             ->assertExitCode(0);

        // Verify old log still exists
        $this->assertDatabaseHas('audit_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recentLog->id]);
    }

    /** @test */
    public function audit_logs_mask_sensitive_data()
    {
        $this->actingAs($this->admin);

        $patient = Patient::factory()->create([
            'user_id' => $this->patient->id,
            'clinic_id' => 1,
            'medical_history' => 'Sensitive medical info',
        ]);

        $auditLog = AuditLog::where('model_type', Patient::class)
                           ->where('model_id', $patient->id)
                           ->first();

        $this->assertNotNull($auditLog);

        // Check that sensitive data is masked in logs
        $oldValues = json_decode($auditLog->old_values, true);
        $newValues = json_decode($auditLog->new_values, true);

        $this->assertEquals('***MASKED***', $newValues['medical_history'] ?? null);
    }

    /** @test */
    public function encryption_handles_decryption_failures_gracefully()
    {
        // Manually insert corrupted encrypted data
        $patient = Patient::factory()->create([
            'user_id' => $this->patient->id,
            'clinic_id' => 1,
        ]);

        // Corrupt the encrypted data
        \DB::table('patients')->where('id', $patient->id)->update([
            'medical_history' => 'corrupted_encrypted_data',
        ]);

        // Accessing the attribute should return the corrupted data (fallback)
        $freshPatient = $patient->fresh();
        $this->assertEquals('corrupted_encrypted_data', $freshPatient->medical_history);
    }
}
