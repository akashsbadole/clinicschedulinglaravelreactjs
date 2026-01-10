<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\AppointmentCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $patient;
    protected $doctor;
    protected $clinic;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->clinic = Clinic::factory()->create();
        $this->patient = User::factory()->create([
            'clinic_id' => $this->clinic->id,
            'role' => 'patient'
        ]);
        $this->doctor = User::factory()->create([
            'clinic_id' => $this->clinic->id,
            'role' => 'doctor'
        ]);
    }

    public function test_appointment_created_notification_is_sent()
    {
        Notification::fake();

        // Create test patient and doctor records
        $patient = Patient::factory()->create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->patient->id,
        ]);

        $doctor = Doctor::factory()->create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->doctor->id,
        ]);

        // Create an appointment
        $appointment = Appointment::factory()->create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'created_by' => $this->doctor->id,
        ]);

        // Assert that notification was sent
        Notification::assertSentTo(
            $this->patient,
            AppointmentCreated::class,
            function ($notification, $channels) use ($appointment) {
                return $notification->appointment->id === $appointment->id;
            }
        );
    }

    public function test_user_can_get_notifications()
    {
        // Create some notifications for the user
        $this->patient->notify(new AppointmentCreated(Appointment::factory()->create()));

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'data',
                            'read_at',
                            'created_at'
                        ]
                    ]
                ]
            ]);
    }

    public function test_user_can_mark_notification_as_read()
    {
        // Create a notification
        $this->patient->notify(new AppointmentCreated(Appointment::factory()->create()));
        $notification = $this->patient->notifications()->first();

        $response = $this->actingAs($this->patient, 'sanctum')
            ->putJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        // Verify notification is marked as read
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_get_notification_stats()
    {
        // Create some notifications
        $this->patient->notify(new AppointmentCreated(Appointment::factory()->create()));
        $this->patient->notify(new AppointmentCreated(Appointment::factory()->create()));

        $response = $this->actingAs($this->patient, 'sanctum')
            ->getJson('/api/notifications/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'unread',
                    'read',
                    'appointment_notifications',
                    'payment_notifications'
                ]
            ]);
    }

    public function test_admin_can_send_appointment_reminders()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/notifications/send-appointment-reminders', [
                'hours' => 24
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Appointment reminders sent successfully'
            ]);
    }

    public function test_non_admin_cannot_send_reminders()
    {
        $response = $this->actingAs($this->patient, 'sanctum')
            ->postJson('/api/notifications/send-appointment-reminders', [
                'hours' => 24
            ]);

        $response->assertStatus(403);
    }

    public function test_notification_settings_model_works()
    {
        $setting = \App\Models\NotificationSetting::create([
            'user_id' => $this->patient->id,
            'notification_type' => 'appointment_created',
            'email_enabled' => true,
            'sms_enabled' => false,
            'push_enabled' => true,
        ]);

        $this->assertTrue(\App\Models\NotificationSetting::isEnabledForUser(
            $this->patient->id,
            'appointment_created',
            'email'
        ));

        $this->assertFalse(\App\Models\NotificationSetting::isEnabledForUser(
            $this->patient->id,
            'appointment_created',
            'sms'
        ));
    }
}
