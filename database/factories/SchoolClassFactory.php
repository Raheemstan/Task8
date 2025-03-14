<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $grade = $this->faker->numberBetween(9, 12);
        $section = $this->faker->randomElement(['A', 'B', 'C']);
        
        return [
            'name' => "Grade {$grade}-{$section}",
            'grade' => (string) $grade,
            'section' => $section,
            'academic_year' => $this->faker->year(),
        ];
    }
} 