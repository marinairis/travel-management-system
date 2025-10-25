<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
  protected $model = ActivityLog::class;

  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'action' => $this->faker->randomElement(['created', 'updated', 'deleted', 'status_changed']),
      'model_type' => $this->faker->randomElement(['App\\Models\\User', 'App\\Models\\TravelRequest']),
      'model_id' => $this->faker->numberBetween(1, 100),
      'description' => $this->faker->sentence(),
      'old_values' => $this->faker->optional()->randomElements(['field1' => 'old_value1', 'field2' => 'old_value2']),
      'new_values' => $this->faker->optional()->randomElements(['field1' => 'new_value1', 'field2' => 'new_value2']),
      'ip_address' => $this->faker->ipv4(),
      'user_agent' => $this->faker->userAgent(),
    ];
  }

  public function forUser(): static
  {
    return $this->state(fn(array $attributes) => [
      'model_type' => 'App\\Models\\User',
      'action' => $this->faker->randomElement(['created', 'updated', 'deleted']),
    ]);
  }

  public function forTravelRequest(): static
  {
    return $this->state(fn(array $attributes) => [
      'model_type' => 'App\\Models\\TravelRequest',
      'action' => $this->faker->randomElement(['created', 'updated', 'deleted', 'status_changed']),
    ]);
  }
}
