<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
  use RefreshDatabase;

  public function test_user_can_register()
  {
    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(201)
      ->assertJsonStructure([
        'success',
        'message',
        'data' => [
          'user' => [
            'id',
            'name',
            'email',
            'is_admin',
            'created_at',
            'updated_at',
          ],
          'token',
          'token_type',
        ],
      ])
      ->assertJson([
        'success' => true,
        'data' => [
          'user' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_admin' => false,
          ],
          'token_type' => 'bearer',
        ],
      ]);

    $this->assertDatabaseHas('users', [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'is_admin' => false,
    ]);

    $user = User::where('email', 'john@example.com')->first();
    $this->assertTrue(Hash::check('password123', $user->password));
  }

  public function test_user_can_login()
  {
    $user = User::factory()->create([
      'email' => 'john@example.com',
      'password' => Hash::make('password123'),
    ]);

    $loginData = [
      'email' => 'john@example.com',
      'password' => 'password123',
    ];

    $response = $this->postJson('/api/login', $loginData);

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'message',
        'data' => [
          'user' => [
            'id',
            'name',
            'email',
            'is_admin',
          ],
          'token',
          'token_type',
        ],
      ])
      ->assertJson([
        'success' => true,
        'data' => [
          'user' => [
            'email' => 'john@example.com',
          ],
          'token_type' => 'bearer',
        ],
      ]);
  }

  public function test_user_cannot_login_with_invalid_credentials()
  {
    $user = User::factory()->create([
      'email' => 'john@example.com',
      'password' => Hash::make('password123'),
    ]);

    $loginData = [
      'email' => 'john@example.com',
      'password' => 'wrongpassword',
    ];

    $response = $this->postJson('/api/login', $loginData);

    $response->assertStatus(401)
      ->assertJson([
        'success' => false,
      ]);
  }

  public function test_authenticated_user_can_get_profile()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
      ->getJson('/api/me');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'message',
        'data' => [
          'id',
          'name',
          'email',
          'is_admin',
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

  public function test_authenticated_user_can_logout()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
      ->postJson('/api/logout');

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
      ]);
  }

  public function test_user_can_refresh_token()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
      ->postJson('/api/refresh');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'message',
        'data' => [
          'token',
          'token_type',
        ],
      ])
      ->assertJson([
        'success' => true,
        'data' => [
          'token_type' => 'bearer',
        ],
      ]);
  }

  public function test_user_can_request_password_reset()
  {
    $user = User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/forgot-password', [
      'email' => 'john@example.com',
    ]);

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
      ]);
  }

  public function test_user_cannot_request_password_reset_with_invalid_email()
  {
    $response = $this->postJson('/api/forgot-password', [
      'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
  }

  public function test_registration_validation_works()
  {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['name', 'email', 'password']);
  }

  public function test_login_validation_works()
  {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email', 'password']);
  }

  public function test_password_reset_validation_works()
  {
    $response = $this->postJson('/api/reset-password', []);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['token', 'email', 'password']);
  }

  public function test_guest_cannot_access_protected_routes()
  {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401);
  }

  public function test_user_cannot_register_with_existing_email()
  {
    User::factory()->create(['email' => 'john@example.com']);

    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
  }

  public function test_password_confirmation_validation()
  {
    $userData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'password_confirmation' => 'differentpassword',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['password']);
  }
}
