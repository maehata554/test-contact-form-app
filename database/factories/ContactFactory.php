<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->lastName(),
            'last_name' => fake()->firstName(),
            'gender' => fake()->randomElement([1, 2, 3]), // 1: 男性, 2: 女性, 3: その他
            'email' => fake()->unique()->safeEmail(),
            'telephone' => '090' . fake()->randomNumber(8, true),
            'address' => fake()->address(),
            'building' => fake()->optional()->secondaryAddress(),
            'category_id' => 1,
            'detail' => fake()->realText(100),//
        ];
    }
}
