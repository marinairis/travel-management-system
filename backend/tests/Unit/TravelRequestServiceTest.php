<?php

namespace Tests\Unit;

use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use App\Services\TravelRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private TravelRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TravelRequestService::class);
        Notification::fake();
    }

    /**
     * Helper to authenticate a user
     */
    public function actingAsUser(User $user): self
    {
        Auth::login($user);
        return $this;
    }

    // ============================================
    // CREATE TRAVEL REQUEST TESTS
    // ============================================

    public function test_create_travel_request_successfully()
    {
        $user = User::factory()->create();
        $this->actingAsUser($user);

        $data = [
            'requester_name' => 'John Doe',
            'destination' => 'São Paulo, SP',
            'departure_date' => '2025-02-01',
            'return_date' => '2025-02-05',
            'notes' => 'Business trip',
            'travel_type' => 'business',
        ];

        $travelRequest = $this->service->createTravelRequest($data);

        $this->assertInstanceOf(TravelRequest::class, $travelRequest);
        $this->assertEquals($user->id, $travelRequest->user_id);
        $this->assertEquals('John Doe', $travelRequest->requester_name);
        $this->assertEquals('São Paulo, SP', $travelRequest->destination);
        $this->assertEquals('requested', $travelRequest->status);
    }

    public function test_create_travel_request_without_optional_fields()
    {
        $user = User::factory()->create();
        $this->actingAsUser($user);

        $data = [
            'requester_name' => 'Jane Doe',
            'destination' => 'Rio de Janeiro, RJ',
            'departure_date' => '2025-03-01',
            'return_date' => '2025-03-10',
        ];

        $travelRequest = $this->service->createTravelRequest($data);

        $this->assertInstanceOf(TravelRequest::class, $travelRequest);
        $this->assertNull($travelRequest->notes);
        $this->assertNull($travelRequest->travel_type);
    }

    // ============================================
    // GET TRAVEL REQUEST TESTS
    // ============================================

    public function test_get_travel_request_returns_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $found = $this->service->getTravelRequest($travelRequest->id);

        $this->assertNotNull($found);
        $this->assertEquals($travelRequest->id, $found->id);
    }

    public function test_get_travel_request_returns_null_for_invalid_id()
    {
        $found = $this->service->getTravelRequest(99999);

        $this->assertNull($found);
    }

    // ============================================
    // UPDATE TRAVEL REQUEST TESTS
    // ============================================

    public function test_update_travel_request_successfully()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $data = [
            'requester_name' => 'Updated Name',
            'destination' => 'Rio de Janeiro, RJ',
            'departure_date' => '2025-04-01',
            'return_date' => '2025-04-05',
            'notes' => 'Updated notes',
        ];

        $updated = $this->service->updateTravelRequest($travelRequest, $data);

        $this->assertEquals('Updated Name', $updated->requester_name);
        $this->assertEquals('Rio de Janeiro, RJ', $updated->destination);
    }

    // ============================================
    // STATUS UPDATE TESTS
    // ============================================

    public function test_update_status_to_approved()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $updated = $this->service->updateStatus($travelRequest, 'approved', $admin);

        $this->assertEquals('approved', $updated->status);
        $this->assertEquals($admin->id, $updated->approved_by);
        $this->assertNotNull($updated->approved_at);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    public function test_notification_sent_on_status_change()
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $this->service->updateStatus($travelRequest, 'approved', $manager);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    // ============================================
    // CANCEL REQUEST TESTS
    // ============================================

    public function test_cancel_request_successfully()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $cancelled = $this->service->cancelRequest($travelRequest, $user, 'Changed plans');

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertEquals('Changed plans', $cancelled->cancel_reason);
        $this->assertEquals($user->id, $cancelled->cancelled_by);
        $this->assertNotNull($cancelled->cancelled_at);
    }

    public function test_notification_sent_on_cancel()
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $user->id,
            'departure_date' => now()->addDays(10),
        ]);

        $this->service->cancelRequest($travelRequest, $manager, 'Budget cuts');

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    // ============================================
    // PERMISSION TESTS - CAN VIEW
    // ============================================

    public function test_approver_can_view_any_travel_request()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->service->canViewTravelRequest($travelRequest, $admin));
    }

    public function test_manager_can_view_any_travel_request()
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->service->canViewTravelRequest($travelRequest, $manager));
    }

    public function test_requester_can_view_own_travel_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->service->canViewTravelRequest($travelRequest, $user));
    }

    public function test_requester_cannot_view_other_users_travel_request()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user2->id]);

        $this->assertFalse($this->service->canViewTravelRequest($travelRequest, $user1));
    }

    // ============================================
    // PERMISSION TESTS - CAN UPDATE
    // ============================================

    public function test_owner_can_update_own_travel_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $this->assertTrue($this->service->canUpdateTravelRequest($travelRequest, $user));
    }

    public function test_admin_can_update_any_travel_request()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $this->assertTrue($this->service->canUpdateTravelRequest($travelRequest, $admin));
    }

    public function test_requester_cannot_update_other_users_travel_request()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user2->id]);

        $this->assertFalse($this->service->canUpdateTravelRequest($travelRequest, $user1));
    }

    // ============================================
    // PERMISSION TESTS - CAN MODIFY (Approved Check)
    // ============================================

    public function test_cannot_modify_approved_travel_request()
    {
        $travelRequest = TravelRequest::factory()->approved()->create();

        $this->assertFalse($this->service->canModifyTravelRequest($travelRequest));
    }

    public function test_can_modify_requested_travel_request()
    {
        $travelRequest = TravelRequest::factory()->requested()->create();

        $this->assertTrue($this->service->canModifyTravelRequest($travelRequest));
    }

    public function test_can_modify_cancelled_travel_request()
    {
        $travelRequest = TravelRequest::factory()->cancelled()->create();

        $this->assertTrue($this->service->canModifyTravelRequest($travelRequest));
    }

    // ============================================
    // PERMISSION TESTS - CAN UPDATE STATUS
    // ============================================

    public function test_manager_can_update_status_of_others_request()
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $this->assertTrue($this->service->canUpdateStatus($travelRequest, $manager));
    }

    public function test_admin_can_update_status_of_others_request()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $this->assertTrue($this->service->canUpdateStatus($travelRequest, $admin));
    }

    public function test_user_cannot_update_own_request_status()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $this->assertFalse($this->service->canUpdateStatus($travelRequest, $user));
    }

    public function test_manager_cannot_update_own_request_status()
    {
        $manager = User::factory()->manager()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $manager->id]);

        $this->assertFalse($this->service->canUpdateStatus($travelRequest, $manager));
    }

    public function test_requester_cannot_update_status()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user2->id]);

        $this->assertFalse($this->service->canUpdateStatus($travelRequest, $user1));
    }

    // ============================================
    // CAN BE CANCELLED TESTS
    // ============================================

    public function test_can_be_cancelled_when_requested()
    {
        $travelRequest = TravelRequest::factory()->requested()->create([
            'departure_date' => now()->addDays(5),
        ]);

        $this->assertTrue($this->service->canBeCancelled($travelRequest));
    }

    public function test_can_be_cancelled_when_approved_with_future_departure()
    {
        $travelRequest = TravelRequest::factory()->approved()->create([
            'departure_date' => now()->addDays(5),
        ]);

        $this->assertTrue($this->service->canBeCancelled($travelRequest));
    }

    public function test_cannot_be_cancelled_when_already_cancelled()
    {
        $travelRequest = TravelRequest::factory()->cancelled()->create();

        $this->assertFalse($this->service->canBeCancelled($travelRequest));
    }

    public function test_cannot_be_cancelled_when_expired()
    {
        $travelRequest = TravelRequest::factory()->create([
            'status' => 'expired',
            'departure_date' => now()->subDays(5),
        ]);

        $this->assertFalse($this->service->canBeCancelled($travelRequest));
    }

    public function test_cannot_cancel_approved_request_with_past_departure()
    {
        $travelRequest = TravelRequest::factory()->approved()->create([
            'departure_date' => now()->subDays(1),
        ]);

        $this->assertFalse($this->service->canBeCancelled($travelRequest));
    }

    // ============================================
    // GET ALL TRAVEL REQUESTS (Role-based) TESTS
    // ============================================

    public function test_requester_only_sees_own_requests()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        TravelRequest::factory()->count(3)->create(['user_id' => $user->id]);
        TravelRequest::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $this->actingAsUser($user);

        $request = Request::create('/api/travel-requests', 'GET');
        $result = $this->service->getAllTravelRequests($request);

        $this->assertEquals(3, $result->total());
    }

    public function test_admin_sees_all_requests()
    {
        $admin = User::factory()->admin()->create();

        TravelRequest::factory()->count(3)->create();
        TravelRequest::factory()->count(2)->create();

        $this->actingAsUser($admin);

        $request = Request::create('/api/travel-requests', 'GET');
        $result = $this->service->getAllTravelRequests($request);

        $this->assertEquals(5, $result->total());
    }

    public function test_manager_sees_all_requests()
    {
        $manager = User::factory()->manager()->create();

        TravelRequest::factory()->count(4)->create();

        $this->actingAsUser($manager);

        $request = Request::create('/api/travel-requests', 'GET');
        $result = $this->service->getAllTravelRequests($request);

        $this->assertEquals(4, $result->total());
    }
}