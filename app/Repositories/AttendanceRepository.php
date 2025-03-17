<?php

namespace App\Repositories;

use App\Models\SchoolClass;
use Illuminate\Support\Facades\Cache;

class AttendanceRepository
{
    public function getClassAttendance(SchoolClass $class, string $date): array
    {
        return Cache::remember(
            "class.{$class->id}.attendance.{$date}",
            now()->addHours(1),
            fn() => $this->generateClassReport($class, $date)
        );
    }

    private function generateClassReport(SchoolClass $class, string $date): array
    {
        $students = SchoolClass::with([
            'students' => function($query) use ($date) {
                $query->select('id', 'name', 'class_id')
                    ->with(['attendances' => function($query) use ($date) {
                        $query->whereDate('date', $date)
                            ->select('id', 'student_id', 'status');
                    }]);
            }
        ])
        ->findOrFail($class->id)
        ->students;

        return [
            'class' => $class->only(['id', 'name', 'grade', 'section']),
            'total_students' => $students->count(),
            'attendance_summary' => [
                'present' => $students->flatMap->attendances
                    ->where('status', 'present')
                    ->count(),
                'absent' => $students->flatMap->attendances
                    ->where('status', 'absent')
                    ->count(),
            ],
            'student_details' => $students->map(fn($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'monthly_absences' => $student->absences()
                    ->whereMonth('date', now()->month)
                    ->count(),
            ])
        ];
    }
}
