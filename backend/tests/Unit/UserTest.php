<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\TravelRequest;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
  use RefreshDatabase;

  public function test_user_can_be_created()
  {
    $user = User::factory()->create([
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => Hash::make('password'),
      'is_admin' => false,
    ]);

    $this->assertInstanceOf(User::class, $user);
    $this->assertEquals('John Doe', $user->name);
    $this->assertEquals('john@example.com', $user->email);
    $this->assertTrue(Hash::check('password', $user->password));
    $this->assertFalse($user->is_admin);
  }

  public function test_user_can_be_admin()
  {
    $user = User::factory()->admin()->create();

    $this->assertTrue($user->is_admin);
  }

  public function test_user_has_travel_requests_relationship()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

    $this->assertInstanceOf(TravelRequest::class, $user->travelRequests->first());
    $this->assertEquals($travelRequest->id, $user->travelRequests->first()->id);
  }

  public function test_user_has_approved_travel_requests_relationship()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->approved()->create(['approved_by' => $user->id]);

    $this->assertInstanceOf(TravelRequest::class, $user->approvedTravelRequests->first());
    $this->assertEquals($travelRequest->id, $user->approvedTravelRequests->first()->id);
  }

  public function test_user_has_activity_logs_relationship()
  {
    $user = User::factory()->create();
    $activityLog = ActivityLog::factory()->create(['user_id' => $user->id]);

    $this->assertInstanceOf(ActivityLog::class, $user->activityLogs->first());
    $this->assertEquals($activityLog->id, $user->activityLogs->first()->id);
  }

  public function test_user_implements_jwt_subject()
  {
    $user = User::factory()->create();

    $this->assertInstanceOf(\Tymon\JWTAuth\Contracts\JWTSubject::class, $user);
  }

  public function test_get_jwt_identifier_returns_user_id()
  {
    $user = User::factory()->create();

    $this->assertEquals($user->id, $user->getJWTIdentifier());
  }

  public function test_get_jwt_custom_claims_returns_empty_array()
  {
    $user = User::factory()->create();

    $this->assertEquals([], $user->getJWTCustomClaims());
  }

  public function test_password_is_hidden_in_serialization()
  {
    $user = User::factory()->create();

    $userArray = $user->toArray();

    $this->assertArrayNotHasKey('password', $userArray);
    $this->assertArrayNotHasKey('remember_token', $userArray);
  }

  public function test_email_verified_at_is_casted_to_datetime()
  {
    $user = User::factory()->create();

    $this->assertInstanceOf(\DateTime::class, $user->email_verified_at);
  }

  public function test_is_admin_is_casted_to_boolean()
  {
    $user = User::factory()->create(['is_admin' => 1]);

    $this->assertTrue($user->is_admin);
    $this->assertIsBool($user->is_admin);
  }

  public function test_user_can_have_multiple_travel_requests()
  {
    $user = User::factory()->create();

    TravelRequest::factory()->count(3)->create(['user_id' => $user->id]);

    $this->assertCount(3, $user->travelRequests);
  }

  public function test_user_can_have_multiple_approved_travel_requests()
  {
    $user = User::factory()->create();

    TravelRequest::factory()->count(2)->approved()->create(['approved_by' => $user->id]);

    $this->assertCount(2, $user->approvedTravelRequests);
  }

  public function test_user_can_have_multiple_activity_logs()
  {
    $user = User::factory()->create();

    ActivityLog::factory()->count(3)->create(['user_id' => $user->id]);

    $this->assertCount(3, $user->activityLogs);
  }
}
