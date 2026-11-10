<?php

namespace Database\Factories;

use App\Models\MassIntention;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MassIntention>
 */
class MassIntentionFactory extends Factory
{
    protected $model = MassIntention::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'intention_type' => fake()->randomElement([
                'Thanksgiving',
                'Healing',
                'Birthday',
                'Souls',
                'Special Prayer',
            ]),
            'intention_for' => fake()->name(),
            'additional_message' => fake()->optional()->sentence(),
            'mass_date' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'mobile' => '9171234567',
            'offering_amount' => fake()->randomFloat(2, 0, 10000),
            'status' => 'Pending',
            'payment_status' => 'Pending',
        ];
    }
}
