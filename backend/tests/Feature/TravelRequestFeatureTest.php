<?php

namespace Tests\Feature;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_travel_request()
    {
        $user = User::factory()->create();

        $travelRequestData = [
            'requester_name' => 'John Doe',
            'destination' => 'São Paulo, SP',
            'departure_date' => now()->addDays(30)->format('Y-m-d'),
            'return_date' => now()->addDays(35)->format('Y-m-d'),
            'notes' => 'Business trip',
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/travel-requests', $travelRequestData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'requester_name',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                    'notes',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Pedido de viagem criado com sucesso',
                'data' => [
                    'user_id' => $user->id,
                    'requester_name' => 'John Doe',
                    'destination' => 'São Paulo, SP',
                    'status' => 'requested',
                ],
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $user->id,
            'requester_name' => 'John Doe',
            'destination' => 'São Paulo, SP',
            'status' => 'requested',
        ]);
    }

    public function test_user_can_view_own_travel_requests()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'requester_name',
                        'destination',
                        'departure_date',
                        'return_date',
                        'status',
                        'notes',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'id' => $travelRequest->id,
                        'user_id' => $user->id,
                    ],
                ],
            ]);
    }

    public function test_admin_can_view_all_travel_requests()
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $travelRequest1 = TravelRequest::factory()->create(['user_id' => $user1->id]);
        $travelRequest2 = TravelRequest::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'requester_name',
                        'destination',
                        'departure_date',
                        'return_date',
                        'status',
                        'notes',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);

        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);
    }

    public function test_manager_can_view_all_travel_requests()
    {
        $manager = User::factory()->manager()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $user1->id]);
        TravelRequest::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($manager, 'api')
            ->getJson('/api/travel-requests');

        $response->assertStatus(200);

        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);
    }

    public function test_user_can_view_specific_travel_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'requester_name',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                    'notes',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $travelRequest->id,
                    'user_id' => $user->id,
                ],
            ]);
    }

    public function test_user_cannot_view_other_users_travel_request()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'api')
            ->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_user_can_update_own_travel_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $updateData = [
            'requester_name' => 'Updated Name',
            'destination' => 'Rio de Janeiro, RJ',
            'departure_date' => now()->addDays(30)->format('Y-m-d'),
            'return_date' => now()->addDays(35)->format('Y-m-d'),
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/travel-requests/{$travelRequest->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pedido de viagem atualizado com sucesso',
            ]);

        $travelRequest->refresh();
        $this->assertEquals('Updated Name', $travelRequest->requester_name);
        $this->assertEquals('Rio de Janeiro, RJ', $travelRequest->destination);
    }

    public function test_user_cannot_update_approved_travel_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->approved()->create(['user_id' => $user->id]);

        $updateData = [
            'requester_name' => 'Updated Name',
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/travel-requests/{$travelRequest->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_can_approve_travel_request()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
            ]);

        $travelRequest->refresh();
        $this->assertEquals('approved', $travelRequest->status);
        $this->assertEquals($admin->id, $travelRequest->approved_by);
        $this->assertNotNull($travelRequest->approved_at);
    }

    public function test_manager_can_approve_travel_request()
    {
        $manager = User::factory()->manager()->create();
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $response = $this->actingAs($manager, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
            ]);
    }

    public function test_requester_cannot_change_travel_request_status()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_manager_cannot_approve_own_travel_request()
    {
        $manager = User::factory()->manager()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $manager->id]);

        $response = $this->actingAs($manager, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Você não pode alterar o status do seu próprio pedido',
            ]);
    }

    public function test_user_can_cancel_own_travel_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pedido cancelado com sucesso',
            ]);

        $travelRequest->refresh();
        $this->assertEquals('cancelled', $travelRequest->status);
    }

    public function test_user_can_cancel_approved_request_with_future_departure()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $user->id,
            'departure_date' => now()->addDays(5),
            'return_date' => now()->addDays(10),
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pedido cancelado com sucesso',
            ]);

        $travelRequest->refresh();
        $this->assertEquals('cancelled', $travelRequest->status);
    }

    public function test_user_cannot_cancel_approved_request_with_past_departure()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $user->id,
            'departure_date' => now()->subDays(3),
            'return_date' => now()->addDays(1),
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Não é possível cancelar um pedido aprovado cuja data de partida já passou',
            ]);
    }

    public function test_user_cannot_cancel_already_cancelled_request()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->cancelled()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Este pedido já está cancelado',
            ]);
    }

    public function test_travel_request_filtering_by_status()
    {
        $user = User::factory()->create();
        TravelRequest::factory()->requested()->create(['user_id' => $user->id]);
        TravelRequest::factory()->approved()->create(['user_id' => $user->id]);
        TravelRequest::factory()->rejected()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/travel-requests?status=requested');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertEquals('requested', $responseData[0]['status']);
    }

    public function test_travel_request_filtering_by_destination()
    {
        $user = User::factory()->create();
        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'destination' => 'São Paulo, SP',
        ]);
        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Rio de Janeiro, RJ',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/travel-requests?destination=São Paulo');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData);
        $this->assertStringContainsString('São Paulo', $responseData[0]['destination']);
    }

    public function test_travel_request_validation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/travel-requests', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requester_name', 'destination', 'departure_date', 'return_date']);
    }

    public function test_guest_cannot_access_travel_requests()
    {
        $response = $this->getJson('/api/travel-requests');

        $response->assertStatus(401);
    }
}
