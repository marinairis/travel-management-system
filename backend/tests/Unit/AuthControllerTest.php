<?php

namespace Tests\Unit;

use App\Http\Controllers\API\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
  use RefreshDatabase;

  private AuthController $controller;

  protected function setUp(): void
  {
    parent::setUp();
    $this->controller = new AuthController();
  }

  public function test_register_creates_user_successfully()
  {
    $requestData = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $request = Request::create('/register', 'POST', $requestData);
    $request->setLaravelSession(session());

    JWTAuth::shouldReceive('fromUser')
      ->once()
      ->andReturn('fake-jwt-token');

    $response = $this->controller->register($request);

    $this->assertEquals(201, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertArrayHasKey('user', $responseData['data']);
    $this->assertArrayHasKey('token', $responseData['data']);
    $this->assertEquals('bearer', $responseData['data']['token_type']);

    $this->assertDatabaseHas('users', [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'is_admin' => false,
    ]);

    $user = User::where('email', 'john@example.com')->first();
    $this->assertTrue(Hash::check('password123', $user->password));
  }

  public function test_register_creates_admin_user_when_specified()
  {
    $requestData = [
      'name' => 'Admin User',
      'email' => 'admin@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
      'is_admin' => true,
    ];

    $request = Request::create('/register', 'POST', $requestData);
    $request->setLaravelSession(session());

    JWTAuth::shouldReceive('fromUser')
      ->once()
      ->andReturn('fake-jwt-token');

    $response = $this->controller->register($request);

    $this->assertEquals(201, $response->getStatusCode());

    $this->assertDatabaseHas('users', [
      'name' => 'Admin User',
      'email' => 'admin@example.com',
      'is_admin' => true,
    ]);
  }

  public function test_login_authenticates_user_successfully()
  {
    $user = User::factory()->create([
      'email' => 'john@example.com',
      'password' => Hash::make('password123'),
    ]);

    $requestData = [
      'email' => 'john@example.com',
      'password' => 'password123',
    ];

    $request = Request::create('/login', 'POST', $requestData);

    JWTAuth::shouldReceive('attempt')
      ->once()
      ->with($requestData)
      ->andReturn('fake-jwt-token');

    Auth::shouldReceive('user')
      ->once()
      ->andReturn($user);

    $response = $this->controller->login($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertArrayHasKey('user', $responseData['data']);
    $this->assertArrayHasKey('token', $responseData['data']);
    $this->assertEquals('bearer', $responseData['data']['token_type']);
  }

  public function test_login_returns_error_for_invalid_credentials()
  {
    $requestData = [
      'email' => 'john@example.com',
      'password' => 'wrongpassword',
    ];

    $request = Request::create('/login', 'POST', $requestData);

    JWTAuth::shouldReceive('attempt')
      ->once()
      ->with($requestData)
      ->andReturn(false);

    $response = $this->controller->login($request);

    $this->assertEquals(401, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
  }

  public function test_login_returns_validation_error_for_invalid_data()
  {
    $requestData = [
      'email' => 'invalid-email',
      'password' => '',
    ];

    $request = Request::create('/login', 'POST', $requestData);

    $response = $this->controller->login($request);

    $this->assertEquals(422, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertArrayHasKey('errors', $responseData);
  }

  public function test_me_returns_authenticated_user()
  {
    $user = User::factory()->create();

    $request = Request::create('/me', 'GET');
    $request->setUserResolver(function () use ($user) {
      return $user;
    });

    $response = $this->controller->me($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals($user->id, $responseData['data']['id']);
  }

  public function test_logout_invalidates_token()
  {
    $request = Request::create('/logout', 'POST');

    JWTAuth::shouldReceive('getToken')
      ->once()
      ->andReturn('fake-token');

    JWTAuth::shouldReceive('invalidate')
      ->once()
      ->with('fake-token')
      ->andReturn(true);

    $response = $this->controller->logout($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
  }

  public function test_refresh_returns_new_token()
  {
    JWTAuth::shouldReceive('getToken')
      ->twice()
      ->andReturn('fake-token');

    JWTAuth::shouldReceive('refresh')
      ->once()
      ->with('fake-token')
      ->andReturn('new-fake-token');

    $response = $this->controller->refresh();

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertEquals('new-fake-token', $responseData['data']['token']);
    $this->assertEquals('bearer', $responseData['data']['token_type']);
  }

  public function test_refresh_returns_error_on_exception()
  {
    JWTAuth::shouldReceive('getToken')
      ->once()
      ->andReturn('fake-token');

    JWTAuth::shouldReceive('refresh')
      ->once()
      ->with('fake-token')
      ->andThrow(new \Exception('Token refresh failed'));

    $response = $this->controller->refresh();

    $this->assertEquals(401, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
  }

  public function test_forgot_password_sends_reset_link()
  {
    $user = User::factory()->create(['email' => 'john@example.com']);

    $requestData = ['email' => 'john@example.com'];
    $request = Request::create('/forgot-password', 'POST', $requestData);

    Password::shouldReceive('sendResetLink')
      ->once()
      ->with($requestData)
      ->andReturn(Password::RESET_LINK_SENT);

    $response = $this->controller->forgotPassword($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
  }

  public function test_forgot_password_returns_error_for_nonexistent_email()
  {
    $requestData = ['email' => 'nonexistent@example.com'];
    $request = Request::create('/forgot-password', 'POST', $requestData);

    $response = $this->controller->forgotPassword($request);

    $this->assertEquals(422, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertArrayHasKey('errors', $responseData);
  }

  public function test_forgot_password_returns_error_on_failure()
  {
    $user = User::factory()->create(['email' => 'john@example.com']);

    $requestData = ['email' => 'john@example.com'];
    $request = Request::create('/forgot-password', 'POST', $requestData);

    Password::shouldReceive('sendResetLink')
      ->once()
      ->with($requestData)
      ->andReturn(Password::INVALID_USER);

    $response = $this->controller->forgotPassword($request);

    $this->assertEquals(500, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
  }

  public function test_reset_password_resets_password_successfully()
  {
    $requestData = [
      'token' => 'valid-token',
      'email' => 'john@example.com',
      'password' => 'newpassword123',
      'password_confirmation' => 'newpassword123',
    ];

    $request = Request::create('/reset-password', 'POST', $requestData);

    Password::shouldReceive('reset')
      ->once()
      ->with($requestData)
      ->andReturn(Password::PASSWORD_RESET);

    $response = $this->controller->resetPassword($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
  }

  public function test_reset_password_returns_error_for_invalid_token()
  {
    $requestData = [
      'token' => 'invalid-token',
      'email' => 'john@example.com',
      'password' => 'newpassword123',
      'password_confirmation' => 'newpassword123',
    ];

    $request = Request::create('/reset-password', 'POST', $requestData);

    Password::shouldReceive('reset')
      ->once()
      ->with($requestData)
      ->andReturn(Password::INVALID_TOKEN);

    $response = $this->controller->resetPassword($request);

    $this->assertEquals(400, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
  }

  public function test_reset_password_returns_validation_error_for_invalid_data()
  {
    $requestData = [
      'token' => 'valid-token',
      'email' => 'invalid-email',
      'password' => '123',
      'password_confirmation' => '456',
    ];

    $request = Request::create('/reset-password', 'POST', $requestData);

    $response = $this->controller->resetPassword($request);

    $this->assertEquals(422, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertArrayHasKey('errors', $responseData);
  }
}
