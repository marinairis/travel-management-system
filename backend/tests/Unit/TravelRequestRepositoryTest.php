<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\TravelRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TravelRequestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TravelRequestRepository::class);
    }

    public function test_find_by_id_returns_travel_request(): void
    {
        $travelRequest = TravelRequest::factory()->create();

        $found = $this->repository->findById($travelRequest->id);

        $this->assertNotNull($found);
        $this->assertEquals($travelRequest->id, $found->id);
    }

    public function test_find_by_id_returns_null_for_invalid_id(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function test_find_by_id_eager_loads_user_relationship(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $found = $this->repository->findById($travelRequest->id);

        $this->assertTrue($found->relationLoaded('user'));
        $this->assertEquals($user->id, $found->user->id);
    }

    public function test_find_all_paginated_requester_sees_own_requests_only(): void
    {
        $requester = User::factory()->create();
        $other = User::factory()->create();
        TravelRequest::factory()->count(2)->create(['user_id' => $requester->id]);
        TravelRequest::factory()->create(['user_id' => $other->id]);

        $result = $this->repository->findAllPaginated($requester, [], 10);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $item) {
            $this->assertEquals($requester->id, $item->user_id);
        }
    }

    public function test_find_all_paginated_admin_sees_all_requests(): void
    {
        $admin = User::factory()->admin()->create();
        TravelRequest::factory()->count(2)->create(['user_id' => User::factory()->create()->id]);
        TravelRequest::factory()->create(['user_id' => User::factory()->create()->id]);

        $result = $this->repository->findAllPaginated($admin, [], 10);

        $this->assertCount(3, $result->items());
    }

    public function test_find_all_paginated_manager_sees_all_requests(): void
    {
        $manager = User::factory()->manager()->create();
        TravelRequest::factory()->count(3)->create();

        $result = $this->repository->findAllPaginated($manager, [], 10);

        $this->assertCount(3, $result->items());
    }

    public function test_find_all_paginated_filters_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        TravelRequest::factory()->requested()->create();
        TravelRequest::factory()->approved()->create();
        TravelRequest::factory()->cancelled()->create();

        $result = $this->repository->findAllPaginated($admin, ['status' => 'requested'], 10);

        $this->assertCount(1, $result->items());
        $this->assertEquals('requested', $result->items()[0]->status);
    }

    public function test_find_all_paginated_filters_by_destination(): void
    {
        $admin = User::factory()->admin()->create();
        TravelRequest::factory()->create(['destination' => 'São Paulo, SP']);
        TravelRequest::factory()->create(['destination' => 'Rio de Janeiro, RJ']);

        $result = $this->repository->findAllPaginated($admin, ['destination' => 'São Paulo'], 10);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('São Paulo', $result->items()[0]->destination);
    }

    public function test_find_all_paginated_respects_per_page(): void
    {
        $admin = User::factory()->admin()->create();
        TravelRequest::factory()->count(5)->create();

        $result = $this->repository->findAllPaginated($admin, [], 2);

        $this->assertCount(2, $result->items());
        $this->assertEquals(5, $result->total());
    }

    public function test_create_stores_travel_request(): void
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'requester_name' => 'John Doe',
            'destination' => 'São Paulo, SP',
            'departure_date' => now()->addDays(10),
            'return_date' => now()->addDays(15),
            'status' => 'requested',
        ];

        $travelRequest = $this->repository->create($data);

        $this->assertNotNull($travelRequest->id);
        $this->assertEquals('John Doe', $travelRequest->requester_name);
        $this->assertDatabaseHas('travel_requests', ['id' => $travelRequest->id]);
    }

    public function test_save_persists_status_change(): void
    {
        $travelRequest = TravelRequest::factory()->requested()->create();
        $travelRequest->status = 'approved';

        $saved = $this->repository->save($travelRequest);

        $this->assertEquals('approved', $saved->status);
        $this->assertDatabaseHas('travel_requests', [
            'id' => $saved->id,
            'status' => 'approved',
        ]);
    }

    public function test_save_returns_fresh_instance_with_relationships(): void
    {
        $travelRequest = TravelRequest::factory()->create();

        $saved = $this->repository->save($travelRequest);

        $this->assertTrue($saved->relationLoaded('user'));
    }
}
