<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 1, 5000),
            'payment_method' => Donation::METHOD_PARISH,
            'payment_status' => 'Pending',
            'status' => 'Pending',
        ];
    }
}
