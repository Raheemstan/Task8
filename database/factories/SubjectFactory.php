<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $subject = $this->faker->randomElement([
            'Mathematics' => 'MATH',
            'Physics' => 'PHY',
            'Chemistry' => 'CHEM',
            'Biology' => 'BIO',
            'English' => 'ENG',
            'History' => 'HIST',
            'Geography' => 'GEO',
        ]);

        return [
            'name' => key($subject),
            'code' => current($subject) . $this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->sentence(),
        ];
    }
} 