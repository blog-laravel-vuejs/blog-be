<?php

namespace Database\Seeders;

use App\Models\Category;
use GuzzleHttp\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // //
        $categories = [
            [
                'name' => 'Laravel',
                'description_category' => 'Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in most web projects.',
            ],
            [
                'name' => 'VueJs',
                'description_category' => 'Vue.js is a progressive framework for building user interfaces. Unlike other monolithic frameworks, Vue is designed from the ground up to be incrementally adoptable. The core library is focused on the view layer only, and is easy to pick up and integrate with other libraries or existing projects.',
            ],
            [
                'name' => 'ReactJs',
                'description_category' => 'React is a JavaScript library for building user interfaces. Learn what React is all about on our homepage or in the tutorial.',
            ],
            [
                'name' => 'NodeJs',
                'description_category' => 'Node.js® is a JavaScript runtime built on Chrome’s V8 JavaScript engine. Node.js uses an event-driven, non-blocking I/O model that makes it lightweight and efficient.',
            ],
            [
                'name' => 'PHP',
                'description_category' => 'PHP is a popular general-purpose scripting language that is especially suited to web development.',
            ],
        ];
        foreach ($categories as $index => $category) {
            try {
                 Category::create([
                            'name' => $category['name'],
                            'description_category' => $category['description_category'],
                            'thumbnail' => null,
                            'search_number' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            catch (\Exception $e) {
                // return $e->getMessage();
            }
        }
    }
}
