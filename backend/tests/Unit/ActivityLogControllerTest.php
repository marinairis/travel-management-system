<?php

namespace Tests\Unit;

use App\Http\Controllers\API\ActivityLogController;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivityLogControllerTest extends TestCase
{
  use RefreshDatabase;

  private ActivityLogController $controller;

  protected function setUp(): void
  {
    parent::setUp();
    $this->controller = new ActivityLogController();
  }

  public function test_index_returns_logs_for_admin()
  {
    $admin = User::factory()->admin()->create();
    $activityLog1 = ActivityLog::factory()->create();
    $activityLog2 = ActivityLog::factory()->create();

    $request = Request::create('/activity-logs', 'GET');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertArrayHasKey('data', $responseData['data']);
    $this->assertCount(2, $responseData['data']['data']);
  }

  public function test_index_returns_error_for_non_admin()
  {
    $user = User::factory()->create(['is_admin' => false]);

    $request = Request::create('/activity-logs', 'GET');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->index($request);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Apenas administradores podem visualizar logs', $responseData['message']);
  }

  public function test_index_returns_error_for_guest()
  {
    $request = Request::create('/activity-logs', 'GET');

    Auth::shouldReceive('user')
      ->once()
      ->andReturn(null);

    $response = $this->controller->index($request);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Apenas administradores podem visualizar logs', $responseData['message']);
  }

  public function test_index_filters_by_user_id()
  {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $activityLog1 = ActivityLog::factory()->create(['user_id' => $user1->id]);
    $activityLog2 = ActivityLog::factory()->create(['user_id' => $user2->id]);

    $request = Request::create('/activity-logs', 'GET', ['user_id' => $user1->id]);
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(1, $responseData['data']['data']);
    $this->assertEquals($user1->id, $responseData['data']['data'][0]['user_id']);
  }

  public function test_index_filters_by_action()
  {
    $admin = User::factory()->admin()->create();

    $activityLog1 = ActivityLog::factory()->create(['action' => 'created']);
    $activityLog2 = ActivityLog::factory()->create(['action' => 'updated']);
    $activityLog3 = ActivityLog::factory()->create(['action' => 'created']);

    $request = Request::create('/activity-logs', 'GET', ['action' => 'created']);
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(2, $responseData['data']['data']);


    foreach ($responseData['data']['data'] as $log) {
      $this->assertEquals('created', $log['action']);
    }
  }

  public function test_index_filters_by_model_type()
  {
    $admin = User::factory()->admin()->create();

    $activityLog1 = ActivityLog::factory()->create(['model_type' => 'App\\Models\\User']);
    $activityLog2 = ActivityLog::factory()->create(['model_type' => 'App\\Models\\TravelRequest']);
    $activityLog3 = ActivityLog::factory()->create(['model_type' => 'App\\Models\\User']);

    $request = Request::create('/activity-logs', 'GET', ['model_type' => 'App\\Models\\User']);
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(2, $responseData['data']['data']);


    foreach ($responseData['data']['data'] as $log) {
      $this->assertEquals('App\\Models\\User', $log['model_type']);
    }
  }

  public function test_index_applies_multiple_filters()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $activityLog1 = ActivityLog::factory()->create([
      'user_id' => $user->id,
      'action' => 'created',
      'model_type' => 'App\\Models\\User'
    ]);
    $activityLog2 = ActivityLog::factory()->create([
      'user_id' => $user->id,
      'action' => 'updated',
      'model_type' => 'App\\Models\\User'
    ]);
    $activityLog3 = ActivityLog::factory()->create([
      'user_id' => $user->id,
      'action' => 'created',
      'model_type' => 'App\\Models\\TravelRequest'
    ]);

    $request = Request::create('/activity-logs', 'GET', [
      'user_id' => $user->id,
      'action' => 'created',
      'model_type' => 'App\\Models\\User'
    ]);
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(1, $responseData['data']['data']);

    $log = $responseData['data']['data'][0];
    $this->assertEquals($user->id, $log['user_id']);
    $this->assertEquals('created', $log['action']);
    $this->assertEquals('App\\Models\\User', $log['model_type']);
  }

  public function test_index_uses_custom_per_page()
  {
    $admin = User::factory()->admin()->create();


    ActivityLog::factory()->count(5)->create();

    $request = Request::create('/activity-logs', 'GET', ['per_page' => 3]);
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(3, $responseData['data']['data']);
  }

  public function test_index_uses_default_per_page()
  {
    $admin = User::factory()->admin()->create();


    ActivityLog::factory()->count(60)->create();

    $request = Request::create('/activity-logs', 'GET');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(50, $responseData['data']['data']);
  }

  public function test_index_orders_by_created_at_desc()
  {
    $admin = User::factory()->admin()->create();

    $activityLog1 = ActivityLog::factory()->create(['created_at' => now()->subDays(2)]);
    $activityLog2 = ActivityLog::factory()->create(['created_at' => now()->subDays(1)]);
    $activityLog3 = ActivityLog::factory()->create(['created_at' => now()]);

    $request = Request::create('/activity-logs', 'GET');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(3, $responseData['data']['data']);


    $logs = $responseData['data']['data'];
    $this->assertEquals($activityLog3->id, $logs[0]['id']);
    $this->assertEquals($activityLog2->id, $logs[1]['id']);
    $this->assertEquals($activityLog1->id, $logs[2]['id']);
  }

  public function test_index_includes_user_relationship()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $activityLog = ActivityLog::factory()->create(['user_id' => $user->id]);

    $request = Request::create('/activity-logs', 'GET');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(1, $responseData['data']['data']);

    $log = $responseData['data']['data'][0];
    $this->assertArrayHasKey('user', $log);
    $this->assertEquals($user->id, $log['user']['id']);
    $this->assertEquals($user->name, $log['user']['name']);
    $this->assertEquals($user->email, $log['user']['email']);
  }
}
