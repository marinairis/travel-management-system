<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_requester_sees_only_own_activity_logs(): void
    {
        $requester = User::factory()->create();
        $other = User::factory()->create();

        ActivityLog::factory()->count(2)->create(['user_id' => $requester->id]);
        ActivityLog::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($requester, 'api')
            ->getJson('/api/activity-logs');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_admin_sees_all_activity_logs(): void
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ActivityLog::factory()->count(2)->create(['user_id' => $user1->id]);
        ActivityLog::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/activity-logs');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_manager_sees_all_activity_logs(): void
    {
        $manager = User::factory()->manager()->create();
        ActivityLog::factory()->count(3)->create();

        $response = $this->actingAs($manager, 'api')
            ->getJson('/api/activity-logs');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_filter_by_action(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::factory()->create(['action' => 'created']);
        ActivityLog::factory()->create(['action' => 'updated']);
        ActivityLog::factory()->create(['action' => 'deleted']);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/activity-logs?action=created');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_filter_by_user_id(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        ActivityLog::factory()->count(2)->create(['user_id' => $user->id]);
        ActivityLog::factory()->create();

        $response = $this->actingAs($admin, 'api')
            ->getJson("/api/activity-logs?user_id={$user->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_filter_by_model_type(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::factory()->create(['model_type' => 'App\\Models\\TravelRequest']);
        ActivityLog::factory()->create(['model_type' => 'App\\Models\\User']);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/activity-logs?model_type=App\\Models\\TravelRequest');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_per_page_parameter_controls_pagination(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/activity-logs?per_page=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.total', 5);
    }

    public function test_response_includes_user_relation(): void
    {
        $user = User::factory()->create();
        ActivityLog::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/activity-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'action',
                            'user' => ['id', 'name', 'email', 'role'],
                        ],
                    ],
                ],
            ]);
    }

    public function test_guest_cannot_access_activity_logs(): void
    {
        $response = $this->getJson('/api/activity-logs');

        $response->assertStatus(401);
    }

    public function test_validation_rejects_invalid_per_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/activity-logs?per_page=0');

        $response->assertStatus(422);
    }
}
