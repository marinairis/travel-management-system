<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvitationFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createInvitation(array $overrides = []): Invitation
    {
        return Invitation::create(array_merge([
            'email'      => 'invited@example.com',
            'role'       => 'requester',
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ], $overrides));
    }

    public function test_show_returns_invitation_data_for_valid_token(): void
    {
        $invitation = $this->createInvitation();

        $response = $this->getJson("/api/invitations/{$invitation->token}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'email' => 'invited@example.com',
                    'role'  => 'requester',
                ],
            ]);
    }

    public function test_show_returns_404_for_invalid_token(): void
    {
        $response = $this->getJson('/api/invitations/invalid-token-xyz');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_show_returns_404_for_expired_invitation(): void
    {
        $invitation = $this->createInvitation(['expires_at' => now()->subDay()]);

        $response = $this->getJson("/api/invitations/{$invitation->token}");

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_show_returns_404_for_already_accepted_invitation(): void
    {
        $invitation = $this->createInvitation(['accepted_at' => now()]);

        $response = $this->getJson("/api/invitations/{$invitation->token}");

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_accept_creates_user_and_marks_invitation_as_used(): void
    {
        $invitation = $this->createInvitation(['email' => 'newuser@example.com', 'role' => 'manager']);

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'name'                  => 'New User',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token', 'token_type'],
            ])
            ->assertJson([
                'success' => true,
                'data'    => ['token_type' => 'bearer'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role'  => 'manager',
        ]);

        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_accept_returns_error_for_invalid_token(): void
    {
        $response = $this->postJson('/api/invitations/invalid-token/accept', [
            'name'                  => 'New User',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_accept_returns_error_for_expired_invitation(): void
    {
        $invitation = $this->createInvitation(['expires_at' => now()->subDay()]);

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'name'                  => 'New User',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_accept_validates_required_fields(): void
    {
        $invitation = $this->createInvitation();

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'password']);
    }

    public function test_admin_can_invite_user(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/users/invite', [
                'email' => 'newinvite@example.com',
                'role'  => 'requester',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('invitations', ['email' => 'newinvite@example.com']);
    }

    public function test_non_admin_cannot_invite_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/users/invite', [
                'email' => 'newinvite@example.com',
                'role'  => 'requester',
            ]);

        $response->assertStatus(403);
    }

    public function test_invite_validates_duplicate_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/users/invite', [
                'email' => 'existing@example.com',
                'role'  => 'requester',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_guest_cannot_invite_user(): void
    {
        $response = $this->postJson('/api/users/invite', [
            'email' => 'newinvite@example.com',
            'role'  => 'requester',
        ]);

        $response->assertStatus(401);
    }
}
