<?php

namespace Database\Factories;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelRequestFactory extends Factory
{
  protected $model = TravelRequest::class;

  public function definition(): array
  {
    $departureDate = $this->faker->dateTimeBetween('now', '+1 month');
    $returnDate = $this->faker->dateTimeBetween($departureDate, '+2 months');

    return [
      'user_id' => User::factory(),
      'requester_name' => $this->faker->name(),
      'destination' => $this->faker->city() . ', ' . $this->faker->state(),
      'departure_date' => $departureDate,
      'return_date' => $returnDate,
      'status' => $this->faker->randomElement(['requested', 'approved', 'rejected', 'cancelled']),
      'approved_by' => null,
      'approved_at' => null,
      'notes' => $this->faker->optional()->sentence(),
    ];
  }

  public function approved(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'approved',
      'approved_by' => User::factory(),
      'approved_at' => now(),
    ]);
  }

  public function rejected(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'rejected',
      'approved_by' => User::factory(),
      'approved_at' => now(),
    ]);
  }

  public function cancelled(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'cancelled',
    ]);
  }

  public function requested(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'requested',
      'approved_by' => null,
      'approved_at' => null,
    ]);
  }
}
