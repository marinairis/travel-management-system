<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
    }

    public function test_find_by_id_returns_user(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->findById($user->id);

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_id_returns_null_for_invalid_id(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function test_find_by_id_includes_travel_requests_count(): void
    {
        $user = User::factory()->create();
        TravelRequest::factory()->count(3)->create(['user_id' => $user->id]);

        $found = $this->repository->findById($user->id);

        $this->assertEquals(3, $found->travel_requests_count);
    }

    public function test_find_by_id_excludes_soft_deleted_by_default(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $found = $this->repository->findById($user->id);

        $this->assertNull($found);
    }

    public function test_find_by_id_includes_soft_deleted_when_requested(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $found = $this->repository->findById($user->id, withTrashed: true);

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_all_paginated_returns_all_users(): void
    {
        User::factory()->count(3)->create();

        $result = $this->repository->findAllPaginated([], 10);

        $this->assertCount(3, $result->items());
    }

    public function test_find_all_paginated_filters_admin_by_user_type(): void
    {
        User::factory()->admin()->create();
        User::factory()->count(2)->create();

        $result = $this->repository->findAllPaginated(['user_type' => 'admin'], 10);

        $this->assertCount(1, $result->items());
        $this->assertEquals('admin', $result->items()[0]->role);
    }

    public function test_find_all_paginated_filters_basic_excludes_admins(): void
    {
        User::factory()->admin()->create();
        User::factory()->count(2)->create();

        $result = $this->repository->findAllPaginated(['user_type' => 'basic'], 10);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $user) {
            $this->assertNotEquals('admin', $user->role);
        }
    }

    public function test_find_all_paginated_filters_by_email(): void
    {
        User::factory()->create(['email' => 'target@example.com']);
        User::factory()->count(2)->create();

        $result = $this->repository->findAllPaginated(['email' => 'target'], 10);

        $this->assertCount(1, $result->items());
        $this->assertEquals('target@example.com', $result->items()[0]->email);
    }

    public function test_find_all_paginated_respects_per_page(): void
    {
        User::factory()->count(5)->create();

        $result = $this->repository->findAllPaginated([], 2);

        $this->assertCount(2, $result->items());
        $this->assertEquals(5, $result->total());
    }

    public function test_basic_list_returns_id_and_name_only(): void
    {
        User::factory()->count(3)->create();

        $result = $this->repository->basicList();

        $this->assertCount(3, $result);
        foreach ($result as $user) {
            $this->assertArrayHasKey('id', $user->toArray());
            $this->assertArrayHasKey('name', $user->toArray());
            $this->assertArrayNotHasKey('email', $user->toArray());
            $this->assertArrayNotHasKey('password', $user->toArray());
        }
    }

    public function test_basic_list_is_ordered_by_name(): void
    {
        User::factory()->create(['name' => 'Zebra User']);
        User::factory()->create(['name' => 'Alpha User']);
        User::factory()->create(['name' => 'Middle User']);

        $result = $this->repository->basicList();

        $this->assertEquals('Alpha User', $result->first()->name);
        $this->assertEquals('Zebra User', $result->last()->name);
    }

    public function test_cancel_open_requests_cancels_requested_and_approved(): void
    {
        $admin = User::factory()->admin()->create();
        Auth::setUser($admin);

        $user = User::factory()->create();
        TravelRequest::factory()->requested()->create(['user_id' => $user->id]);
        TravelRequest::factory()->approved()->create(['user_id' => $user->id]);
        TravelRequest::factory()->cancelled()->create(['user_id' => $user->id]);

        $count = $this->repository->cancelOpenRequests($user, 'Account deactivated');

        $this->assertEquals(2, $count);
    }

    public function test_cancel_open_requests_sets_cancel_reason(): void
    {
        $admin = User::factory()->admin()->create();
        Auth::setUser($admin);

        $user = User::factory()->create();
        TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $this->repository->cancelOpenRequests($user, 'Test reason');

        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $user->id,
            'status' => 'cancelled',
            'cancel_reason' => 'Test reason',
        ]);
    }

    public function test_cancel_open_requests_returns_zero_when_none_open(): void
    {
        $admin = User::factory()->admin()->create();
        Auth::setUser($admin);

        $user = User::factory()->create();
        TravelRequest::factory()->cancelled()->create(['user_id' => $user->id]);

        $count = $this->repository->cancelOpenRequests($user, 'Test');

        $this->assertEquals(0, $count);
    }
}
