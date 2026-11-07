<?php

namespace Database\Seeders;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::pluck('id')->toArray();
        $tagIds = Tag::pluck('id')->toArray();
        Contact::factory()->count(20)->create([
            'category_id' => function () use ($categoryIds) {
                return fake()->randomElement($categoryIds);
            }
        ])->each(function ($contact) use ($tagIds) {
            $count = rand(1, min(3, count($tagIds)));
            $randomTagIds = array_slice(fake()->shuffleArray($tagIds), 0, $count);

            $contact->tags()->attach($randomTagIds);
        });
    }
}
