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
            ['name' => 'Electricidad', 'icon' => 'bolt'],
            ['name' => 'Carpintería', 'icon' => 'hammer'],
            ['name' => 'Construcción', 'icon' => 'building'],
            ['name' => 'Limpieza', 'icon' => 'sparkles'],
            ['name' => 'Mecánica', 'icon' => 'car-front'],
            ['name' => 'Plomería', 'icon' => 'droplet'],
            ['name' => 'Pintura', 'icon' => 'brush'],
            ['name' => 'Aires acondicionados', 'icon' => 'wind'],
            ['name' => 'Cerrajería', 'icon' => 'key'],
            ['name' => 'Reparación electrodomésticos', 'icon' => 'plug'],
            ['name' => 'Enfermería', 'icon' => 'heart-pulse'],
            ['name' => 'Mudanzas', 'icon' => 'truck'],
            ['name' => 'Jardinería', 'icon' => 'flower1'],
            ['name' => 'Autolavado y detallado automotriz', 'icon' => 'car-front-fill'],
            ['name' => 'Informática (mantenimiento PC)', 'icon' => 'pc-display'],
            ['name' => 'Impermeabilización', 'icon' => 'umbrella'],
            ['name' => 'Demolición', 'icon' => 'bricks'],
            ['name' => 'Cosmética y estética', 'icon' => 'person-hearts'],
            ['name' => 'Costura', 'icon' => 'scissors'],
            ['name' => 'Herrería', 'icon' => 'wrench-adjustable'],
            ['name' => 'Vidriería', 'icon' => 'window'],
            ['name' => 'Mariachis / grupo musical', 'icon' => 'music-note-beamed'],
            ['name' => 'Banquetes', 'icon' => 'cake2'],
            ['name' => 'Animación y conducción', 'icon' => 'mic'],
            ['name' => 'Restauración de inmuebles', 'icon' => 'house-gear'],
            ['name' => 'Restauración de bienes', 'icon' => 'tools'],
            ['name' => 'Tatuaje y perforación', 'icon' => 'pen'],
            ['name' => 'Cocina', 'icon' => 'egg-fried'],
            ['name' => 'Lavandería', 'icon' => 'basket'],
            ['name' => 'Entrenamiento personal', 'icon' => 'activity'],
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
