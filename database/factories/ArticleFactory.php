<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $userIds= User::pluck("id")->toArray();
        $categoryIds = Category::pluck("id")->toArray();
        return [
            'id_user' => $this->faker->randomElement($userIds),
            'id_category' => $this->faker->randomElement($categoryIds),
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'created_at' => now(),
            'updated_at'=> now(),
        ];
    }
}
