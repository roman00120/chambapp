<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Pisos', 'Vidriería', 'Tapicería', 'Impermeabilización', 'Mudanzas']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Servicios profesionales de '.$name.'.',
            'icon' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
