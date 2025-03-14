<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH101', 'description' => 'Basic Mathematics'],
            ['name' => 'Physics', 'code' => 'PHY101', 'description' => 'General Physics'],
            ['name' => 'Chemistry', 'code' => 'CHEM101', 'description' => 'General Chemistry'],
            ['name' => 'Biology', 'code' => 'BIO101', 'description' => 'General Biology'],
            ['name' => 'English', 'code' => 'ENG101', 'description' => 'English Language'],
            ['name' => 'History', 'code' => 'HIST101', 'description' => 'World History'],
            ['name' => 'Geography', 'code' => 'GEO101', 'description' => 'Physical Geography'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
} 