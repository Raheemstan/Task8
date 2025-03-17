<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $subjects = [
            'Mathematics' => 'MATH',
            'Physics' => 'PHY',
            'Chemistry' => 'CHEM',
            'Biology' => 'BIO',
            'English' => 'ENG',
            'History' => 'HIST',
            'Geography' => 'GEO',
        ];

        // Get a random key-value pair
        $name = $this->faker->randomElement(array_keys($subjects));
        $code = $subjects[$name];

        return [
            'name' => $name,
            'code' => $code . $this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->sentence(),
        ];
    }
} 