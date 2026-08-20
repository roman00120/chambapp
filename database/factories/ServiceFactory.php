<?php

namespace Database\Factories;

use App\Enums\PriceType;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Reparación e instalación profesional',
            'Mantenimiento para tu hogar',
            'Trabajo hecho a la medida',
            'Servicio confiable y puntual',
        ]);

        return [
            'professional_id' => ProfessionalProfile::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraph(),
            'price' => '650.00',
            'price_type' => PriceType::FIXED,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function startingAt(): static
    {
        return $this->state(['price_type' => PriceType::STARTING_AT]);
    }

    public function quote(): static
    {
        return $this->state(['price_type' => PriceType::QUOTE, 'price' => null]);
    }
}
