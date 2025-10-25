<?php

namespace Tests\Unit;

use App\Http\Controllers\API\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
  use RefreshDatabase;

  private UserController $controller;

  protected function setUp(): void
  {
    parent::setUp();
    $this->controller = new UserController();
  }

  public function test_index_returns_all_users()
  {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->admin()->create();

    $request = Request::create('/users', 'GET');

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(3, $responseData['data']);
  }

  public function test_index_filters_by_user_type_admin()
  {
    $user1 = User::factory()->create(['is_admin' => false]);
    $user2 = User::factory()->admin()->create();
    $user3 = User::factory()->admin()->create();

    $request = Request::create('/users', 'GET', ['user_type' => 'admin']);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(2, $responseData['data']);

    foreach ($responseData['data'] as $user) {
      $this->assertTrue($user['is_admin']);
    }
  }

  public function test_index_filters_by_user_type_basic()
  {
    $user1 = User::factory()->create(['is_admin' => false]);
    $user2 = User::factory()->create(['is_admin' => false]);
    $user3 = User::factory()->admin()->create();

    $request = Request::create('/users', 'GET', ['user_type' => 'basic']);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(2, $responseData['data']);

    foreach ($responseData['data'] as $user) {
      $this->assertFalse($user['is_admin']);
    }
  }

  public function test_index_filters_by_email()
  {
    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);
    $user3 = User::factory()->create(['email' => 'john.doe@example.com']);

    $request = Request::create('/users', 'GET', ['email' => 'john']);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(2, $responseData['data']);

    foreach ($responseData['data'] as $user) {
      $this->assertStringContainsString('john', strtolower($user['email']));
    }
  }

  public function test_show_returns_user_for_admin()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $request = Request::create("/users/{$user->id}", 'GET');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->show($user->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($user->id, $responseData['data']['id']);
  }

  public function test_show_returns_user_for_owner()
  {
    $user = User::factory()->create();

    $request = Request::create("/users/{$user->id}", 'GET');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->show($user->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($user->id, $responseData['data']['id']);
  }

  public function test_show_returns_error_for_unauthorized_user()
  {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $request = Request::create("/users/{$user2->id}", 'GET');
    $request->setUserResolver(function () use ($user1) {
      return $user1;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user1);

    $response = $this->controller->show($user2->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Você não tem permissão para visualizar este usuário', $responseData['message']);
  }

  public function test_update_updates_user_successfully()
  {
    $user = User::factory()->create([
      'name' => 'John Doe',
      'is_admin' => false,
    ]);

    $requestData = [
      'name' => 'John Updated',
      'is_admin' => true,
    ];

    $request = Request::create("/users/{$user->id}", 'PUT', $requestData);

    $response = $this->controller->update($request, $user->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertEquals('Usuário atualizado com sucesso', $responseData['message']);
    $this->assertArrayHasKey('data', $responseData);

    $user->refresh();
    $this->assertEquals('John Updated', $user->name);
    $this->assertTrue($user->is_admin);
  }

  public function test_update_returns_error_for_nonexistent_user()
  {
    $requestData = [
      'name' => 'John Updated',
    ];

    $request = Request::create('/users/999', 'PUT', $requestData);

    $response = $this->controller->update($request, 999);

    $this->assertEquals(404, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Usuário não encontrado', $responseData['message']);
  }

  public function test_destroy_deletes_user_successfully()
  {
    $user = User::factory()->create();

    $request = Request::create("/users/{$user->id}", 'DELETE');

    $response = $this->controller->destroy($request, $user->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertEquals('Usuário excluído com sucesso', $responseData['message']);

    $this->assertSoftDeleted('users', ['id' => $user->id]);
  }

  public function test_destroy_returns_error_for_nonexistent_user()
  {
    $request = Request::create('/users/999', 'DELETE');

    $response = $this->controller->destroy($request, 999);

    $this->assertEquals(404, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Usuário não encontrado', $responseData['message']);
  }

  public function test_destroy_returns_error_for_own_account()
  {
    $user = User::factory()->create();

    $request = Request::create("/users/{$user->id}", 'DELETE');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('id')
      ->once()
      ->andReturn($user->id);

    $response = $this->controller->destroy($request, $user->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Você não pode excluir sua própria conta', $responseData['message']);
  }

  public function test_index_includes_travel_requests_count()
  {
    $user = User::factory()->create();
    TravelRequest::factory()->count(3)->create(['user_id' => $user->id]);

    $request = Request::create('/users', 'GET');

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);

    $userData = collect($responseData['data'])->firstWhere('id', $user->id);
    $this->assertNotNull($userData);
    $this->assertArrayHasKey('travel_requests_count', $userData);
    $this->assertEquals(3, $userData['travel_requests_count']);
  }

  public function test_show_includes_travel_requests_count()
  {
    $user = User::factory()->create();
    TravelRequest::factory()->count(2)->create(['user_id' => $user->id]);

    $request = Request::create("/users/{$user->id}", 'GET');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->show($user->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertArrayHasKey('travel_requests_count', $responseData['data']);
    $this->assertEquals(2, $responseData['data']['travel_requests_count']);
  }
}
