<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Plomería', 'icon' => 'droplet'],
            ['name' => 'Electricidad', 'icon' => 'bolt'],
            ['name' => 'Carpintería', 'icon' => 'hammer'],
            ['name' => 'Pintura', 'icon' => 'brush'],
            ['name' => 'Jardinería', 'icon' => 'leaf'],
            ['name' => 'Limpieza', 'icon' => 'sparkles'],
            ['name' => 'Albañilería', 'icon' => 'building'],
            ['name' => 'Mecánica', 'icon' => 'car'],
            ['name' => 'Herrería', 'icon' => 'wrench'],
            ['name' => 'Cerrajería', 'icon' => 'key'],
            ['name' => 'Climatización', 'icon' => 'wind'],
            ['name' => 'Reparaciones generales', 'icon' => 'tools'],
        ];

        foreach ($categories as $sortOrder => $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => 'Servicios de '.$category['name'].' para tu hogar o negocio.',
                    'icon' => $category['icon'],
                    'is_active' => true,
                    'sort_order' => $sortOrder + 1,
                ],
            );
        }
    }
}
