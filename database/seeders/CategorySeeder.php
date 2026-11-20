<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Tech & work', 'slug' => 'tech-work', 'sort_order' => 10],
            ['name' => 'Home & everyday', 'slug' => 'home-everyday', 'sort_order' => 20],
            ['name' => 'Digital goods', 'slug' => 'digital-goods', 'sort_order' => 30],
            ['name' => 'Services', 'slug' => 'services', 'sort_order' => 40],
        ] as $category) {
            Category::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'is_active' => true],
            );
        }
    }
}
