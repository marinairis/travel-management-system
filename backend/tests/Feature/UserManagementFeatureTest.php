<?php

namespace Tests\Feature;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementFeatureTest extends TestCase
{
  use RefreshDatabase;

  public function test_admin_can_view_all_users()
  {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $response = $this->actingAs($admin, 'api')
      ->getJson('/api/users');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'data' => [
          '*' => [
            'id',
            'name',
            'email',
            'is_admin',
            'travel_requests_count',
            'created_at',
            'updated_at',
          ],
        ],
      ])
      ->assertJson([
        'success' => true,
      ]);

    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
  }

  public function test_admin_can_view_specific_user()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'api')
      ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'data' => [
          'id',
          'name',
          'email',
          'is_admin',
          'travel_requests_count',
        ],
      ])
      ->assertJson([
        'success' => true,
        'data' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
        ],
      ]);
  }

  public function test_user_can_view_own_profile()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
      ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'data' => [
          'id' => $user->id,
        ],
      ]);
  }

  public function test_user_cannot_view_other_users_profile()
  {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $response = $this->actingAs($user1, 'api')
      ->getJson("/api/users/{$user2->id}");

    $response->assertStatus(403)
      ->assertJson([
        'success' => false,
        'message' => 'Você não tem permissão para visualizar este usuário',
      ]);
  }

  public function test_admin_can_update_user()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
      'name' => 'John Doe',
      'is_admin' => false,
    ]);

    $updateData = [
      'name' => 'John Updated',
      'is_admin' => true,
    ];

    $response = $this->actingAs($admin, 'api')
      ->putJson("/api/users/{$user->id}", $updateData);

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'message' => 'Usuário atualizado com sucesso',
      ]);

    $user->refresh();
    $this->assertEquals('John Updated', $user->name);
    $this->assertTrue($user->is_admin);
  }

  public function test_admin_can_delete_user()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'api')
      ->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'message' => 'Usuário excluído com sucesso',
      ]);

    $this->assertSoftDeleted('users', ['id' => $user->id]);
  }

  public function test_admin_cannot_delete_own_account()
  {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'api')
      ->deleteJson("/api/users/{$admin->id}");

    $response->assertStatus(403)
      ->assertJson([
        'success' => false,
        'message' => 'Você não pode excluir sua própria conta',
      ]);
  }

  public function test_user_filtering_by_type_admin()
  {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['is_admin' => false]);
    $user2 = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'api')
      ->getJson('/api/users?user_type=admin');

    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);

    foreach ($responseData as $user) {
      $this->assertTrue($user['is_admin']);
    }
  }

  public function test_user_filtering_by_type_basic()
  {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['is_admin' => false]);
    $user2 = User::factory()->create(['is_admin' => false]);
    $user3 = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'api')
      ->getJson('/api/users?user_type=basic');

    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);

    foreach ($responseData as $user) {
      $this->assertFalse($user['is_admin']);
    }
  }

  public function test_user_filtering_by_email()
  {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);
    $user3 = User::factory()->create(['email' => 'john.doe@example.com']);

    $response = $this->actingAs($admin, 'api')
      ->getJson('/api/users?email=john');

    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);

    foreach ($responseData as $user) {
      $this->assertStringContainsString('john', strtolower($user['email']));
    }
  }

  public function test_user_travel_requests_count_is_included()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();


    TravelRequest::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin, 'api')
      ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertEquals(3, $responseData['travel_requests_count']);
  }

  public function test_user_update_validation()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'api')
      ->putJson("/api/users/{$user->id}", [
        'name' => '',
        'is_admin' => 'invalid',
      ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['name', 'is_admin']);
  }

  public function test_cannot_update_nonexistent_user()
  {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'api')
      ->putJson('/api/users/999', [
        'name' => 'Updated Name',
      ]);

    $response->assertStatus(404)
      ->assertJson([
        'success' => false,
        'message' => 'Usuário não encontrado',
      ]);
  }

  public function test_cannot_delete_nonexistent_user()
  {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'api')
      ->deleteJson('/api/users/999');

    $response->assertStatus(404)
      ->assertJson([
        'success' => false,
        'message' => 'Usuário não encontrado',
      ]);
  }

  public function test_guest_cannot_access_users()
  {
    $response = $this->getJson('/api/users');

    $response->assertStatus(401);
  }

  public function test_regular_user_cannot_access_user_management()
  {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();


    $response = $this->actingAs($user, 'api')
      ->getJson('/api/users');
    $response->assertStatus(403);


    $response = $this->actingAs($user, 'api')
      ->putJson("/api/users/{$otherUser->id}", [
        'name' => 'Updated Name',
      ]);
    $response->assertStatus(403);


    $response = $this->actingAs($user, 'api')
      ->deleteJson("/api/users/{$otherUser->id}");
    $response->assertStatus(403);
  }

  public function test_user_soft_delete_preserves_related_data()
  {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();


    $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);


    $response = $this->actingAs($admin, 'api')
      ->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(200);


    $this->assertSoftDeleted('users', ['id' => $user->id]);


    $this->assertDatabaseHas('travel_requests', [
      'id' => $travelRequest->id,
      'user_id' => $user->id,
    ]);
  }
}
