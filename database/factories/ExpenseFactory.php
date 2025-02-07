<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\MessGroup;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'mess_group_id' => MessGroup::factory(),  // Creates a random mess group
            'member_id' => Member::factory(),  // Creates a random member
            'date' => $this->faker->date(),  // Generates a random date
            'description' => $this->faker->sentence(),  // Generates a random description
            'amount' => $this->faker->randomFloat(2, 0, 1000),  // Generates a random amount between 0 and 1000
        ];
    }
}
