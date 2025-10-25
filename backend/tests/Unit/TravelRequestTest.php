<?php

namespace Tests\Unit;

use App\Models\TravelRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestTest extends TestCase
{
  use RefreshDatabase;

  public function test_travel_request_can_be_created()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create([
      'user_id' => $user->id,
      'requester_name' => 'John Doe',
      'destination' => 'São Paulo, SP',
      'departure_date' => '2024-02-01',
      'return_date' => '2024-02-05',
      'status' => 'requested',
      'notes' => 'Business trip',
    ]);

    $this->assertInstanceOf(TravelRequest::class, $travelRequest);
    $this->assertEquals($user->id, $travelRequest->user_id);
    $this->assertEquals('John Doe', $travelRequest->requester_name);
    $this->assertEquals('São Paulo, SP', $travelRequest->destination);
    $this->assertEquals('2024-02-01', $travelRequest->departure_date->format('Y-m-d'));
    $this->assertEquals('2024-02-05', $travelRequest->return_date->format('Y-m-d'));
    $this->assertEquals('requested', $travelRequest->status);
    $this->assertEquals('Business trip', $travelRequest->notes);
  }

  public function test_travel_request_belongs_to_user()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

    $this->assertInstanceOf(User::class, $travelRequest->user);
    $this->assertEquals($user->id, $travelRequest->user->id);
  }

  public function test_travel_request_belongs_to_approved_by_user()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->approved()->create(['approved_by' => $user->id]);

    $this->assertInstanceOf(User::class, $travelRequest->approvedBy);
    $this->assertEquals($user->id, $travelRequest->approvedBy->id);
  }

  public function test_departure_date_is_casted_to_date()
  {
    $travelRequest = TravelRequest::factory()->create(['departure_date' => '2024-02-01']);

    $this->assertInstanceOf(Carbon::class, $travelRequest->departure_date);
    $this->assertEquals('2024-02-01', $travelRequest->departure_date->format('Y-m-d'));
  }

  public function test_return_date_is_casted_to_date()
  {
    $travelRequest = TravelRequest::factory()->create(['return_date' => '2024-02-05']);

    $this->assertInstanceOf(Carbon::class, $travelRequest->return_date);
    $this->assertEquals('2024-02-05', $travelRequest->return_date->format('Y-m-d'));
  }

  public function test_approved_at_is_casted_to_datetime()
  {
    $travelRequest = TravelRequest::factory()->approved()->create();

    $this->assertInstanceOf(Carbon::class, $travelRequest->approved_at);
  }

  public function test_can_be_cancelled_attribute_returns_true_for_non_approved_status()
  {
    $travelRequest = TravelRequest::factory()->requested()->create();

    $this->assertTrue($travelRequest->can_be_cancelled);
  }

  public function test_can_be_cancelled_attribute_returns_false_for_approved_status()
  {
    $travelRequest = TravelRequest::factory()->approved()->create();

    $this->assertFalse($travelRequest->can_be_cancelled);
  }

  public function test_scope_by_status_filters_by_status()
  {
    TravelRequest::factory()->requested()->create();
    TravelRequest::factory()->approved()->create();
    TravelRequest::factory()->rejected()->create();

    $requestedRequests = TravelRequest::byStatus('requested')->get();
    $approvedRequests = TravelRequest::byStatus('approved')->get();

    $this->assertCount(1, $requestedRequests);
    $this->assertCount(1, $approvedRequests);
    $this->assertEquals('requested', $requestedRequests->first()->status);
    $this->assertEquals('approved', $approvedRequests->first()->status);
  }

  public function test_scope_by_status_returns_all_when_no_status_provided()
  {
    TravelRequest::factory()->count(3)->create();

    $allRequests = TravelRequest::byStatus(null)->get();

    $this->assertCount(3, $allRequests);
  }

  public function test_scope_by_destination_filters_by_destination()
  {
    TravelRequest::factory()->create(['destination' => 'São Paulo, SP']);
    TravelRequest::factory()->create(['destination' => 'Rio de Janeiro, RJ']);
    TravelRequest::factory()->create(['destination' => 'São Paulo, SP - Centro']);

    $spRequests = TravelRequest::byDestination('São Paulo')->get();

    $this->assertCount(2, $spRequests);
    $this->assertTrue($spRequests->every(fn($request) => str_contains($request->destination, 'São Paulo')));
  }

  public function test_scope_by_destination_returns_all_when_no_destination_provided()
  {
    TravelRequest::factory()->count(3)->create();

    $allRequests = TravelRequest::byDestination(null)->get();

    $this->assertCount(3, $allRequests);
  }

  public function test_scope_by_date_range_filters_by_departure_date()
  {
    TravelRequest::factory()->create(['departure_date' => '2024-02-01']);
    TravelRequest::factory()->create(['departure_date' => '2024-02-15']);
    TravelRequest::factory()->create(['departure_date' => '2024-03-01']);

    $requests = TravelRequest::byDateRange('2024-02-01', '2024-02-28')->get();

    $this->assertCount(2, $requests);
  }

  public function test_scope_by_date_range_filters_by_return_date()
  {
    TravelRequest::factory()->create(['return_date' => '2024-02-01']);
    TravelRequest::factory()->create(['return_date' => '2024-02-15']);
    TravelRequest::factory()->create(['return_date' => '2024-03-01']);

    $requests = TravelRequest::byDateRange('2024-02-01', '2024-02-28')->get();

    $this->assertCount(2, $requests);
  }

  public function test_scope_by_date_range_filters_by_created_at()
  {
    $travelRequest1 = TravelRequest::factory()->create();
    $travelRequest1->created_at = '2024-02-01 10:00:00';
    $travelRequest1->save();

    $travelRequest2 = TravelRequest::factory()->create();
    $travelRequest2->created_at = '2024-02-15 10:00:00';
    $travelRequest2->save();

    $travelRequest3 = TravelRequest::factory()->create();
    $travelRequest3->created_at = '2024-03-01 10:00:00';
    $travelRequest3->save();

    $requests = TravelRequest::byDateRange('2024-02-01', '2024-02-28')->get();

    $this->assertCount(2, $requests);
  }

  public function test_scope_by_date_range_returns_all_when_no_dates_provided()
  {
    TravelRequest::factory()->count(3)->create();

    $allRequests = TravelRequest::byDateRange(null, null)->get();

    $this->assertCount(3, $allRequests);
  }

  public function test_travel_request_can_be_soft_deleted()
  {
    $travelRequest = TravelRequest::factory()->create();
    $travelRequestId = $travelRequest->id;

    $travelRequest->delete();

    $this->assertSoftDeleted('travel_requests', ['id' => $travelRequestId]);
    $this->assertNull(TravelRequest::find($travelRequestId));
    $this->assertNotNull(TravelRequest::withTrashed()->find($travelRequestId));
  }

  public function test_travel_request_can_be_restored()
  {
    $travelRequest = TravelRequest::factory()->create();
    $travelRequestId = $travelRequest->id;

    $travelRequest->delete();
    $travelRequest->restore();

    $this->assertNotNull(TravelRequest::find($travelRequestId));
  }
}
