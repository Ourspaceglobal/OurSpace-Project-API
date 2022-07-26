<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['id' => Str::orderedUuid()->toString(), 'name' => 'Self-containment', 'slug' => 'self-containment'],
            ['id' => Str::orderedUuid()->toString(), 'name' => '2-bedroom', 'slug' => '2-bedroom'],
            ['id' => Str::orderedUuid()->toString(), 'name' => '3-bedroom', 'slug' => '3-bedroom'],
        ];

        Category::query()->upsert(
            $categories,
            ['name'],
            ['slug']
        );
    }
}
