<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = date('Y');
        
        foreach (range(9, 12) as $grade) {
            foreach (['A', 'B', 'C'] as $section) {
                SchoolClass::create([
                    'name' => "Grade {$grade}-{$section}",
                    'grade' => (string) $grade,
                    'section' => $section,
                    'academic_year' => $academicYear,
                ]);
            }
        }
    }
} 