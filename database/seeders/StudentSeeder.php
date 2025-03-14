<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        SchoolClass::all()->each(function ($class) {
            Student::factory()
                ->count(30)
                ->create(['class_id' => $class->id]);
        });
    }
} 