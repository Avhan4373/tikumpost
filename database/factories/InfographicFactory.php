<?php

namespace Database\Factories;

use App\Models\Infographic;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Infographic> */
class InfographicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image' => 'infographics/'.fake()->uuid().'.jpg',
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
