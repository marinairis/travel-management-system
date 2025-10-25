<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
  use RefreshDatabase;

  public function test_activity_log_can_be_created()
  {
    $user = User::factory()->create();
    $activityLog = ActivityLog::factory()->create([
      'user_id' => $user->id,
      'action' => 'created',
      'model_type' => 'App\\Models\\User',
      'model_id' => 1,
      'description' => 'User created',
      'old_values' => null,
      'new_values' => ['name' => 'John Doe', 'email' => 'john@example.com'],
      'ip_address' => '192.168.1.1',
      'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]);

    $this->assertInstanceOf(ActivityLog::class, $activityLog);
    $this->assertEquals($user->id, $activityLog->user_id);
    $this->assertEquals('created', $activityLog->action);
    $this->assertEquals('App\\Models\\User', $activityLog->model_type);
    $this->assertEquals(1, $activityLog->model_id);
    $this->assertEquals('User created', $activityLog->description);
    $this->assertNull($activityLog->old_values);
    $this->assertEquals(['name' => 'John Doe', 'email' => 'john@example.com'], $activityLog->new_values);
    $this->assertEquals('192.168.1.1', $activityLog->ip_address);
    $this->assertStringContainsString('Mozilla', $activityLog->user_agent);
  }

  public function test_activity_log_belongs_to_user()
  {
    $user = User::factory()->create();
    $activityLog = ActivityLog::factory()->create(['user_id' => $user->id]);

    $this->assertInstanceOf(User::class, $activityLog->user);
    $this->assertEquals($user->id, $activityLog->user->id);
  }

  public function test_activity_log_has_morph_to_relationship()
  {
    $user = User::factory()->create();
    $activityLog = ActivityLog::factory()->create([
      'user_id' => $user->id,
      'model_type' => 'App\\Models\\User',
      'model_id' => $user->id,
    ]);

    $this->assertInstanceOf(User::class, $activityLog->model);
    $this->assertEquals($user->id, $activityLog->model->id);
  }

  public function test_activity_log_can_morph_to_travel_request()
  {
    $travelRequest = TravelRequest::factory()->create();
    $activityLog = ActivityLog::factory()->create([
      'model_type' => 'App\\Models\\TravelRequest',
      'model_id' => $travelRequest->id,
    ]);

    $this->assertInstanceOf(TravelRequest::class, $activityLog->model);
    $this->assertEquals($travelRequest->id, $activityLog->model->id);
  }

  public function test_old_values_is_casted_to_array()
  {
    $activityLog = ActivityLog::factory()->create([
      'old_values' => ['name' => 'Old Name', 'email' => 'old@example.com'],
    ]);

    $this->assertIsArray($activityLog->old_values);
    $this->assertEquals(['name' => 'Old Name', 'email' => 'old@example.com'], $activityLog->old_values);
  }

  public function test_new_values_is_casted_to_array()
  {
    $activityLog = ActivityLog::factory()->create([
      'new_values' => ['name' => 'New Name', 'email' => 'new@example.com'],
    ]);

    $this->assertIsArray($activityLog->new_values);
    $this->assertEquals(['name' => 'New Name', 'email' => 'new@example.com'], $activityLog->new_values);
  }

  public function test_activity_log_can_have_null_old_values()
  {
    $activityLog = ActivityLog::factory()->create(['old_values' => null]);

    $this->assertNull($activityLog->old_values);
  }

  public function test_activity_log_can_have_null_new_values()
  {
    $activityLog = ActivityLog::factory()->create(['new_values' => null]);

    $this->assertNull($activityLog->new_values);
  }

  public function test_activity_log_can_have_complex_array_values()
  {
    $complexOldValues = [
      'user' => ['name' => 'Old Name', 'email' => 'old@example.com'],
      'metadata' => ['ip' => '192.168.1.1', 'timestamp' => '2024-01-01 10:00:00'],
    ];

    $complexNewValues = [
      'user' => ['name' => 'New Name', 'email' => 'new@example.com'],
      'metadata' => ['ip' => '192.168.1.2', 'timestamp' => '2024-01-01 11:00:00'],
    ];

    $activityLog = ActivityLog::factory()->create([
      'old_values' => $complexOldValues,
      'new_values' => $complexNewValues,
    ]);

    $this->assertEquals($complexOldValues, $activityLog->old_values);
    $this->assertEquals($complexNewValues, $activityLog->new_values);
  }

  public function test_activity_log_can_be_created_for_different_actions()
  {
    $actions = ['created', 'updated', 'deleted', 'status_changed'];

    foreach ($actions as $action) {
      $activityLog = ActivityLog::factory()->create(['action' => $action]);
      $this->assertEquals($action, $activityLog->action);
    }
  }

  public function test_activity_log_can_be_created_for_different_model_types()
  {
    $modelTypes = ['App\\Models\\User', 'App\\Models\\TravelRequest'];

    foreach ($modelTypes as $modelType) {
      $activityLog = ActivityLog::factory()->create(['model_type' => $modelType]);
      $this->assertEquals($modelType, $activityLog->model_type);
    }
  }

  public function test_activity_log_can_have_long_description()
  {
    $longDescription = 'This is a very long description that contains multiple sentences and should be stored properly in the database without any issues. It can contain special characters like @#$%^&*() and numbers 123456789.';

    $activityLog = ActivityLog::factory()->create(['description' => $longDescription]);

    $this->assertEquals($longDescription, $activityLog->description);
  }

  public function test_activity_log_can_have_different_ip_addresses()
  {
    $ipAddresses = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '127.0.0.1'];

    foreach ($ipAddresses as $ip) {
      $activityLog = ActivityLog::factory()->create(['ip_address' => $ip]);
      $this->assertEquals($ip, $activityLog->ip_address);
    }
  }

  public function test_activity_log_can_have_different_user_agents()
  {
    $userAgents = [
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
      'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
    ];

    foreach ($userAgents as $userAgent) {
      $activityLog = ActivityLog::factory()->create(['user_agent' => $userAgent]);
      $this->assertEquals($userAgent, $activityLog->user_agent);
    }
  }
}
