<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Article> */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(8), '.');
        $paragraphs = collect(range(1, 4))
            ->map(fn () => '<p>'.fake()->paragraph(6).'</p>')
            ->implode('');

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 99999),
            'excerpt' => fake()->paragraph(2),
            'body' => $paragraphs,
            'status' => ArticleStatus::Published,
            'is_headline' => fake()->boolean(20),
            'views' => fake()->numberBetween(0, 5000),
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ArticleStatus::Draft, 'published_at' => null]);
    }
}
