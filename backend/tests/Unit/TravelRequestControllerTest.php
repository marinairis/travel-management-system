<?php

namespace Tests\Unit;

use App\Http\Controllers\API\TravelRequestController;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelRequestControllerTest extends TestCase
{
  use RefreshDatabase;

  private TravelRequestController $controller;

  protected function setUp(): void
  {
    parent::setUp();
    $this->controller = new TravelRequestController();
    Notification::fake();
  }

  public function test_index_returns_user_travel_requests_for_regular_user()
  {
    $user = User::factory()->create(['is_admin' => false]);
    $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

    $request = Request::create('/travel-requests', 'GET');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->index($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertCount(1, $responseData['data']);
    $this->assertEquals($travelRequest->id, $responseData['data'][0]['id']);
  }

  public function test_index_returns_all_travel_requests_for_admin()
  {
    $admin = User::factory()->admin()->create();
    $travelRequest1 = TravelRequest::factory()->create();
    $travelRequest2 = TravelRequest::factory()->create();

    $request = Request::create('/travel-requests', 'GET');
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
    $this->assertCount(2, $responseData['data']);
  }

  public function test_store_creates_travel_request_successfully()
  {
    $user = User::factory()->create();

    $requestData = [
      'requester_name' => 'John Doe',
      'destination' => 'São Paulo, SP',
      'departure_date' => '2024-02-01',
      'return_date' => '2024-02-05',
      'notes' => 'Business trip',
    ];

    $request = Request::create('/travel-requests', 'POST', $requestData);
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('id')
      ->once()
      ->andReturn($user->id);

    $response = $this->controller->store($request);

    $this->assertEquals(201, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertEquals('Pedido de viagem criado com sucesso', $responseData['message']);
    $this->assertArrayHasKey('data', $responseData);

    $this->assertDatabaseHas('travel_requests', [
      'user_id' => $user->id,
      'requester_name' => 'John Doe',
      'destination' => 'São Paulo, SP',
      'status' => 'requested',
    ]);
  }

  public function test_show_returns_travel_request_for_owner()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'GET');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->show($travelRequest->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($travelRequest->id, $responseData['data']['id']);
  }

  public function test_show_returns_travel_request_for_admin()
  {
    $admin = User::factory()->admin()->create();
    $travelRequest = TravelRequest::factory()->create();

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'GET');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->show($travelRequest->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($travelRequest->id, $responseData['data']['id']);
  }

  public function test_show_returns_error_for_unauthorized_user()
  {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $travelRequest = TravelRequest::factory()->create(['user_id' => $otherUser->id]);

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'GET');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->show($travelRequest->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
  }

  public function test_update_updates_travel_request_successfully()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

    $requestData = [
      'requester_name' => 'Updated Name',
      'destination' => 'Rio de Janeiro, RJ',
      'departure_date' => '2024-03-01',
      'return_date' => '2024-03-05',
      'notes' => 'Updated notes',
    ];

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'PUT', $requestData);
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->update($request, $travelRequest->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertEquals('Pedido de viagem atualizado com sucesso', $responseData['message']);

    $travelRequest->refresh();
    $this->assertEquals('Updated Name', $travelRequest->requester_name);
    $this->assertEquals('Rio de Janeiro, RJ', $travelRequest->destination);
  }

  public function test_update_returns_error_for_approved_request()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->approved()->create(['user_id' => $user->id]);

    $requestData = [
      'requester_name' => 'Updated Name',
    ];

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'PUT', $requestData);
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->update($request, $travelRequest->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
  }

  public function test_update_status_updates_status_successfully()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

    $requestData = ['status' => 'approved'];
    $request = Request::create("/travel-requests/{$travelRequest->id}/status", 'PATCH', $requestData);
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->updateStatus($request, $travelRequest->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertEquals('Status atualizado com sucesso', $responseData['message']);

    $travelRequest->refresh();
    $this->assertEquals('approved', $travelRequest->status);
    $this->assertEquals($admin->id, $travelRequest->approved_by);
    $this->assertNotNull($travelRequest->approved_at);

    Notification::assertSentTo($user, TravelRequestStatusChanged::class);
  }

  public function test_update_status_returns_error_for_own_request()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

    $requestData = ['status' => 'approved'];
    $request = Request::create("/travel-requests/{$travelRequest->id}/status", 'PATCH', $requestData);
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->updateStatus($request, $travelRequest->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Você não pode alterar o status do seu próprio pedido', $responseData['message']);
  }

  public function test_destroy_deletes_travel_request_for_admin()
  {
    $admin = User::factory()->admin()->create();
    $travelRequest = TravelRequest::factory()->requested()->create();

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'DELETE');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->destroy($request, $travelRequest->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertEquals('Pedido de viagem excluído com sucesso', $responseData['message']);

    $this->assertSoftDeleted('travel_requests', ['id' => $travelRequest->id]);
  }

  public function test_destroy_returns_error_for_non_admin()
  {
    $user = User::factory()->create(['is_admin' => false]);
    $travelRequest = TravelRequest::factory()->create();

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'DELETE');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->destroy($request, $travelRequest->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Apenas administradores podem excluir pedidos', $responseData['message']);
  }

  public function test_destroy_returns_error_for_approved_request()
  {
    $admin = User::factory()->admin()->create();
    $travelRequest = TravelRequest::factory()->approved()->create();

    $request = Request::create("/travel-requests/{$travelRequest->id}", 'DELETE');
    $request->setUserResolver(function () use ($admin) {
      return $admin;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($admin);

    $response = $this->controller->destroy($request, $travelRequest->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Não é possível excluir um pedido aprovado', $responseData['message']);
  }

  public function test_cancel_cancels_travel_request_successfully()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

    $request = Request::create("/travel-requests/{$travelRequest->id}/cancel", 'PATCH');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->cancel($request, $travelRequest->id);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertEquals('Pedido cancelado com sucesso', $responseData['message']);

    $travelRequest->refresh();
    $this->assertEquals('cancelled', $travelRequest->status);
  }

  public function test_cancel_returns_error_for_approved_request()
  {
    $user = User::factory()->create();
    $travelRequest = TravelRequest::factory()->approved()->create(['user_id' => $user->id]);

    $request = Request::create("/travel-requests/{$travelRequest->id}/cancel", 'PATCH');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->cancel($request, $travelRequest->id);

    $this->assertEquals(403, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Não é possível cancelar um pedido já aprovado', $responseData['message']);
  }
}
