<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnquiryManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $doctorUser;
    protected $patientUser;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Create doctor user
        $this->doctorUser = User::create([
            'name' => 'Doctor User',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor'
        ]);

        // Create patient user
        $this->patientUser = User::create([
            'name' => 'Patient User',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'role' => 'patient'
        ]);

        // Create staff user
        $this->staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff'
        ]);
    }

    /** @test */
    public function anyone_can_create_enquiry()
    {
        $response = $this->post('/enquiries', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'message' => 'I need information about your services.'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Enquiry submitted successfully'
            ]);

        $this->assertDatabaseHas('enquiries', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'message' => 'I need information about your services.',
            'status' => Enquiry::STATUS_PENDING
        ]);
    }

    /** @test */
    public function cannot_create_enquiry_with_invalid_data()
    {
        $response = $this->post('/enquiries', [
            'name' => '', // Required
            'email' => 'invalid-email', // Invalid format
            'phone' => str_repeat('1', 21), // Too long
            'message' => '' // Required
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'message']);
    }

    /** @test */
    public function enquiry_defaults_to_pending_status()
    {
        $response = $this->post('/enquiries', [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'message' => 'Inquiry about appointments.'
        ]);

        $response->assertStatus(200);

        $enquiry = Enquiry::where('email', 'jane.smith@example.com')->first();
        $this->assertEquals(Enquiry::STATUS_PENDING, $enquiry->status);
    }

    /** @test */
    public function admin_can_get_all_enquiries()
    {
        // Create some enquiries
        Enquiry::create([
            'name' => 'Test Enquiry 1',
            'email' => 'test1@example.com',
            'message' => 'Message 1'
        ]);

        Enquiry::create([
            'name' => 'Test Enquiry 2',
            'email' => 'test2@example.com',
            'message' => 'Message 2'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/enquiries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'follow_ups'
                ]
            ]);

        $this->assertCount(2, $response->json());
    }

    /** @test */
    public function non_admin_cannot_get_enquiries()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/admin/enquiries');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_get_single_enquiry()
    {
        $enquiry = Enquiry::create([
            'name' => 'Single Enquiry',
            'email' => 'single@example.com',
            'message' => 'Single message'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/enquiries/' . $enquiry->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $enquiry->id,
                'name' => 'Single Enquiry',
                'email' => 'single@example.com',
                'status' => Enquiry::STATUS_PENDING
            ])
            ->assertJsonStructure([
                'follow_ups'
            ]);
    }

    /** @test */
    public function cannot_get_nonexistent_enquiry()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/enquiries/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_update_enquiry_status()
    {
        $enquiry = Enquiry::create([
            'name' => 'Status Update Enquiry',
            'email' => 'status@example.com',
            'message' => 'Update my status',
            'status' => Enquiry::STATUS_PENDING
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/enquiries/' . $enquiry->id . '/status', [
            'status' => Enquiry::STATUS_IN_PROGRESS
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Status updated successfully'
            ]);

        $this->assertDatabaseHas('enquiries', [
            'id' => $enquiry->id,
            'status' => Enquiry::STATUS_IN_PROGRESS
        ]);
    }

    /** @test */
    public function updating_status_creates_follow_up()
    {
        $enquiry = Enquiry::create([
            'name' => 'Follow Up Enquiry',
            'email' => 'followup@example.com',
            'message' => 'Create follow up',
            'status' => Enquiry::STATUS_PENDING
        ]);

        $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/enquiries/' . $enquiry->id . '/status', [
            'status' => Enquiry::STATUS_RESOLVED
        ]);

        $this->assertDatabaseHas('follow_ups', [
            'followable_id' => $enquiry->id,
            'followable_type' => Enquiry::class,
            'user_id' => $this->adminUser->id,
            'type' => FollowUp::TYPE_STATUS_CHANGE,
            'content' => 'Status changed from Pending to Resolved'
        ]);

        $enquiry->refresh();
        $this->assertCount(1, $enquiry->followUps);
    }

    /** @test */
    public function cannot_update_status_to_invalid_value()
    {
        $enquiry = Enquiry::create([
            'name' => 'Invalid Status Enquiry',
            'email' => 'invalid@example.com',
            'message' => 'Invalid status'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/enquiries/' . $enquiry->id . '/status', [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function non_admin_cannot_update_enquiry_status()
    {
        $enquiry = Enquiry::create([
            'name' => 'No Access Enquiry',
            'email' => 'noaccess@example.com',
            'message' => 'No access'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->patch('/admin/enquiries/' . $enquiry->id . '/status', [
            'status' => Enquiry::STATUS_RESOLVED
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_add_follow_up()
    {
        $enquiry = Enquiry::create([
            'name' => 'Add Follow Up Enquiry',
            'email' => 'addfollowup@example.com',
            'message' => 'Add follow up'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/admin/enquiries/' . $enquiry->id . '/follow-ups', [
            'type' => FollowUp::TYPE_NOTE,
            'content' => 'This is a test note'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Follow-up added successfully',
                'follow_up' => [
                    'type' => FollowUp::TYPE_NOTE,
                    'content' => 'This is a test note',
                    'user' => [
                        'id' => $this->adminUser->id,
                        'name' => $this->adminUser->name
                    ]
                ]
            ]);

        $this->assertDatabaseHas('follow_ups', [
            'followable_id' => $enquiry->id,
            'followable_type' => Enquiry::class,
            'user_id' => $this->adminUser->id,
            'type' => FollowUp::TYPE_NOTE,
            'content' => 'This is a test note'
        ]);
    }

    /** @test */
    public function cannot_add_follow_up_with_invalid_type()
    {
        $enquiry = Enquiry::create([
            'name' => 'Invalid Follow Up Enquiry',
            'email' => 'invalidfollowup@example.com',
            'message' => 'Invalid follow up'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/admin/enquiries/' . $enquiry->id . '/follow-ups', [
            'type' => 'invalid_type',
            'content' => 'Invalid type content'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function cannot_add_follow_up_with_empty_content()
    {
        $enquiry = Enquiry::create([
            'name' => 'Empty Content Enquiry',
            'email' => 'empty@example.com',
            'message' => 'Empty content'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/admin/enquiries/' . $enquiry->id . '/follow-ups', [
            'type' => FollowUp::TYPE_RESPONSE,
            'content' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function non_admin_cannot_add_follow_up()
    {
        $enquiry = Enquiry::create([
            'name' => 'No Add Follow Up Enquiry',
            'email' => 'noadd@example.com',
            'message' => 'No add'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/admin/enquiries/' . $enquiry->id . '/follow-ups', [
            'type' => FollowUp::TYPE_NOTE,
            'content' => 'Unauthorized follow up'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function enquiries_are_ordered_by_latest()
    {
        $enquiry1 = Enquiry::create([
            'name' => 'First Enquiry',
            'email' => 'first@example.com',
            'message' => 'First'
        ]);

        sleep(1); // Ensure different timestamps

        $enquiry2 = Enquiry::create([
            'name' => 'Second Enquiry',
            'email' => 'second@example.com',
            'message' => 'Second'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/enquiries');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($enquiry2->id, $data[0]['id']);
        $this->assertEquals($enquiry1->id, $data[1]['id']);
    }

    /** @test */
    public function enquiry_show_includes_follow_ups_with_users()
    {
        $enquiry = Enquiry::create([
            'name' => 'With Follow Ups',
            'email' => 'withfollowups@example.com',
            'message' => 'With follow ups'
        ]);

        FollowUp::create([
            'followable_id' => $enquiry->id,
            'followable_type' => Enquiry::class,
            'user_id' => $this->adminUser->id,
            'type' => FollowUp::TYPE_RESPONSE,
            'content' => 'Response content'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/enquiries/' . $enquiry->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'follow_ups' => [
                    '*' => [
                        'id',
                        'type',
                        'content',
                        'user' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            ]);

        $this->assertCount(1, $response->json('follow_ups'));
    }

    /** @test */
    public function status_change_follow_up_not_created_when_status_unchanged()
    {
        $enquiry = Enquiry::create([
            'name' => 'No Change Enquiry',
            'email' => 'nochange@example.com',
            'message' => 'No change',
            'status' => Enquiry::STATUS_PENDING
        ]);

        $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/enquiries/' . $enquiry->id . '/status', [
            'status' => Enquiry::STATUS_PENDING
        ]);

        $enquiry->refresh();
        $this->assertCount(0, $enquiry->followUps);
    }

    /** @test */
    public function can_create_enquiry_without_phone()
    {
        $response = $this->post('/enquiries', [
            'name' => 'No Phone Enquiry',
            'email' => 'nophone@example.com',
            'message' => 'No phone provided'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('enquiries', [
            'name' => 'No Phone Enquiry',
            'email' => 'nophone@example.com',
            'phone' => null,
            'message' => 'No phone provided'
        ]);
    }

    /** @test */
    public function enquiry_status_can_be_all_valid_values()
    {
        $enquiry = Enquiry::create([
            'name' => 'All Status Enquiry',
            'email' => 'allstatus@example.com',
            'message' => 'All status'
        ]);

        $statuses = [Enquiry::STATUS_PENDING, Enquiry::STATUS_IN_PROGRESS, Enquiry::STATUS_RESOLVED, Enquiry::STATUS_CLOSED];

        foreach ($statuses as $status) {
            $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/enquiries/' . $enquiry->id . '/status', [
                'status' => $status
            ]);

            $this->assertDatabaseHas('enquiries', [
                'id' => $enquiry->id,
                'status' => $status
            ]);
        }
    }
}