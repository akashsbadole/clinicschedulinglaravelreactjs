<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactManagementTest extends TestCase
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
    public function anyone_can_create_contact()
    {
        $response = $this->post('/contacts', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'subject' => 'General Inquiry',
            'message' => 'I need information about your services.'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contact submitted successfully'
            ]);

        $this->assertDatabaseHas('contacts', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'subject' => 'General Inquiry',
            'message' => 'I need information about your services.',
            'status' => Contact::STATUS_PENDING
        ]);
    }

    /** @test */
    public function cannot_create_contact_with_invalid_data()
    {
        $response = $this->post('/contacts', [
            'name' => '', // Required
            'email' => 'invalid-email', // Invalid format
            'phone' => str_repeat('1', 21), // Too long
            'subject' => '', // Required
            'message' => '' // Required
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'subject', 'message']);
    }

    /** @test */
    public function contact_defaults_to_pending_status()
    {
        $response = $this->post('/contacts', [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'subject' => 'Appointment Inquiry',
            'message' => 'Inquiry about appointments.'
        ]);

        $response->assertStatus(200);

        $contact = Contact::where('email', 'jane.smith@example.com')->first();
        $this->assertEquals(Contact::STATUS_PENDING, $contact->status);
    }

    /** @test */
    public function admin_can_get_all_contacts()
    {
        // Create some contacts
        Contact::create([
            'name' => 'Test Contact 1',
            'email' => 'test1@example.com',
            'subject' => 'Subject 1',
            'message' => 'Message 1'
        ]);

        Contact::create([
            'name' => 'Test Contact 2',
            'email' => 'test2@example.com',
            'subject' => 'Subject 2',
            'message' => 'Message 2'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/contacts');

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
    public function non_admin_cannot_get_contacts()
    {
        $response = $this->actingAs($this->doctorUser, 'sanctum')->get('/admin/contacts');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_get_single_contact()
    {
        $contact = Contact::create([
            'name' => 'Single Contact',
            'email' => 'single@example.com',
            'subject' => 'Single Subject',
            'message' => 'Single message'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/contacts/' . $contact->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $contact->id,
                'name' => 'Single Contact',
                'email' => 'single@example.com',
                'status' => Contact::STATUS_PENDING
            ])
            ->assertJsonStructure([
                'follow_ups'
            ]);
    }

    /** @test */
    public function cannot_get_nonexistent_contact()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/contacts/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_update_contact_status()
    {
        $contact = Contact::create([
            'name' => 'Status Update Contact',
            'email' => 'status@example.com',
            'subject' => 'Update Subject',
            'message' => 'Update my status',
            'status' => Contact::STATUS_PENDING
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
            'status' => Contact::STATUS_IN_PROGRESS
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Status updated successfully'
            ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => Contact::STATUS_IN_PROGRESS
        ]);
    }

    /** @test */
    public function updating_contact_status_creates_follow_up()
    {
        $contact = Contact::create([
            'name' => 'Follow Up Contact',
            'email' => 'followup@example.com',
            'subject' => 'Follow Up Subject',
            'message' => 'Create follow up',
            'status' => Contact::STATUS_PENDING
        ]);

        $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
            'status' => Contact::STATUS_RESOLVED
        ]);

        $this->assertDatabaseHas('follow_ups', [
            'followable_id' => $contact->id,
            'followable_type' => Contact::class,
            'user_id' => $this->adminUser->id,
            'type' => FollowUp::TYPE_STATUS_CHANGE,
            'content' => 'Status changed from Pending to Resolved'
        ]);

        $contact->refresh();
        $this->assertCount(1, $contact->followUps);
    }

    /** @test */
    public function cannot_update_contact_status_to_invalid_value()
    {
        $contact = Contact::create([
            'name' => 'Invalid Status Contact',
            'email' => 'invalid@example.com',
            'subject' => 'Invalid Subject',
            'message' => 'Invalid status'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function non_admin_cannot_update_contact_status()
    {
        $contact = Contact::create([
            'name' => 'No Access Contact',
            'email' => 'noaccess@example.com',
            'subject' => 'No Access Subject',
            'message' => 'No access'
        ]);

        $response = $this->actingAs($this->doctorUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
            'status' => Contact::STATUS_RESOLVED
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_add_follow_up_to_contact()
    {
        $contact = Contact::create([
            'name' => 'Add Follow Up Contact',
            'email' => 'addfollowup@example.com',
            'subject' => 'Add Follow Up Subject',
            'message' => 'Add follow up'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/admin/contacts/' . $contact->id . '/follow-ups', [
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
            'followable_id' => $contact->id,
            'followable_type' => Contact::class,
            'user_id' => $this->adminUser->id,
            'type' => FollowUp::TYPE_NOTE,
            'content' => 'This is a test note'
        ]);
    }

    /** @test */
    public function cannot_add_follow_up_with_invalid_type()
    {
        $contact = Contact::create([
            'name' => 'Invalid Follow Up Contact',
            'email' => 'invalidfollowup@example.com',
            'subject' => 'Invalid Follow Up Subject',
            'message' => 'Invalid follow up'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/admin/contacts/' . $contact->id . '/follow-ups', [
            'type' => 'invalid_type',
            'content' => 'Invalid type content'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function cannot_add_follow_up_with_empty_content()
    {
        $contact = Contact::create([
            'name' => 'Empty Content Contact',
            'email' => 'empty@example.com',
            'subject' => 'Empty Content Subject',
            'message' => 'Empty content'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->post('/admin/contacts/' . $contact->id . '/follow-ups', [
            'type' => FollowUp::TYPE_RESPONSE,
            'content' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function non_admin_cannot_add_follow_up_to_contact()
    {
        $contact = Contact::create([
            'name' => 'No Add Follow Up Contact',
            'email' => 'noadd@example.com',
            'subject' => 'No Add Subject',
            'message' => 'No add'
        ]);

        $response = $this->actingAs($this->patientUser, 'sanctum')->post('/admin/contacts/' . $contact->id . '/follow-ups', [
            'type' => FollowUp::TYPE_NOTE,
            'content' => 'Unauthorized follow up'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function contacts_are_ordered_by_latest()
    {
        $contact1 = Contact::create([
            'name' => 'First Contact',
            'email' => 'first@example.com',
            'subject' => 'First Subject',
            'message' => 'First'
        ]);

        sleep(1); // Ensure different timestamps

        $contact2 = Contact::create([
            'name' => 'Second Contact',
            'email' => 'second@example.com',
            'subject' => 'Second Subject',
            'message' => 'Second'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/contacts');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($contact2->id, $data[0]['id']);
        $this->assertEquals($contact1->id, $data[1]['id']);
    }

    /** @test */
    public function contact_show_includes_follow_ups_with_users()
    {
        $contact = Contact::create([
            'name' => 'With Follow Ups',
            'email' => 'withfollowups@example.com',
            'subject' => 'With Follow Ups Subject',
            'message' => 'With follow ups'
        ]);

        FollowUp::create([
            'followable_id' => $contact->id,
            'followable_type' => Contact::class,
            'user_id' => $this->adminUser->id,
            'type' => FollowUp::TYPE_RESPONSE,
            'content' => 'Response content'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->get('/admin/contacts/' . $contact->id);

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
        $contact = Contact::create([
            'name' => 'No Change Contact',
            'email' => 'nochange@example.com',
            'subject' => 'No Change Subject',
            'message' => 'No change',
            'status' => Contact::STATUS_PENDING
        ]);

        $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
            'status' => Contact::STATUS_PENDING
        ]);

        $contact->refresh();
        $this->assertCount(0, $contact->followUps);
    }

    /** @test */
    public function can_create_contact_without_phone()
    {
        $response = $this->post('/contacts', [
            'name' => 'No Phone Contact',
            'email' => 'nophone@example.com',
            'subject' => 'No Phone Subject',
            'message' => 'No phone provided'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'name' => 'No Phone Contact',
            'email' => 'nophone@example.com',
            'phone' => null,
            'subject' => 'No Phone Subject',
            'message' => 'No phone provided'
        ]);
    }

    /** @test */
    public function contact_status_can_be_all_valid_values()
    {
        $contact = Contact::create([
            'name' => 'All Status Contact',
            'email' => 'allstatus@example.com',
            'subject' => 'All Status Subject',
            'message' => 'All status'
        ]);

        $statuses = [Contact::STATUS_PENDING, Contact::STATUS_IN_PROGRESS, Contact::STATUS_RESOLVED, Contact::STATUS_CLOSED];

        foreach ($statuses as $status) {
            $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
                'status' => $status
            ]);

            $this->assertDatabaseHas('contacts', [
                'id' => $contact->id,
                'status' => $status
            ]);
        }
    }

    /** @test */
    public function status_transition_creates_appropriate_follow_ups()
    {
        $contact = Contact::create([
            'name' => 'Transition Contact',
            'email' => 'transition@example.com',
            'subject' => 'Transition Subject',
            'message' => 'Status transitions'
        ]);

        // Pending to In Progress
        $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
            'status' => Contact::STATUS_IN_PROGRESS
        ]);

        $this->assertDatabaseHas('follow_ups', [
            'followable_id' => $contact->id,
            'followable_type' => Contact::class,
            'content' => 'Status changed from Pending to In Progress'
        ]);

        // In Progress to Resolved
        $this->actingAs($this->adminUser, 'sanctum')->patch('/admin/contacts/' . $contact->id . '/status', [
            'status' => Contact::STATUS_RESOLVED
        ]);

        $this->assertDatabaseHas('follow_ups', [
            'followable_id' => $contact->id,
            'followable_type' => Contact::class,
            'content' => 'Status changed from In Progress to Resolved'
        ]);

        $contact->refresh();
        $this->assertCount(2, $contact->followUps);
    }

    /** @test */
    public function multiple_follow_ups_can_be_added_to_contact()
    {
        $contact = Contact::create([
            'name' => 'Multiple Follow Ups Contact',
            'email' => 'multiple@example.com',
            'subject' => 'Multiple Subject',
            'message' => 'Multiple follow ups'
        ]);

        $this->actingAs($this->adminUser, 'sanctum')->post('/admin/contacts/' . $contact->id . '/follow-ups', [
            'type' => FollowUp::TYPE_NOTE,
            'content' => 'First note'
        ]);

        $this->actingAs($this->adminUser, 'sanctum')->post('/admin/contacts/' . $contact->id . '/follow-ups', [
            'type' => FollowUp::TYPE_RESPONSE,
            'content' => 'Response to contact'
        ]);

        $contact->refresh();
        $this->assertCount(2, $contact->followUps);

        $followUps = $contact->followUps;
        $this->assertEquals(FollowUp::TYPE_NOTE, $followUps[0]->type);
        $this->assertEquals(FollowUp::TYPE_RESPONSE, $followUps[1]->type);
    }
}