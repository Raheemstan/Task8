<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Collection;
use App\Jobs\ProcessAbsenceNotifications;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function markBulkAttendance(array $attendances): Collection
    {
        return collect($attendances)->map(function ($record) {
            return $this->createAttendanceRecord($record);
        });
    }

    public function markAttendance(Student $student, array $data): Attendance
    {
        return $this->createAttendanceRecord([
            'student_id' => $student->id,
            ...$data
        ]);
    }

    private function createAttendanceRecord(array $data): Attendance
    {
        $attendance = Attendance::create($data);

        if ($attendance->status === 'absent') {
            ProcessAbsenceNotifications::dispatch($attendance->student);
        }

        $this->clearCache($attendance);
        $this->logAttendance($attendance);

        return $attendance;
    }

    private function clearCache(Attendance $attendance): void
    {
        Cache::forget("student.{$attendance->student_id}.attendance");
        Cache::forget("class.{$attendance->student->class_id}.attendance");
    }

    private function logAttendance(Attendance $attendance): void
    {
        Log::info('Attendance marked', [
            'student_id' => $attendance->student_id,
            'subject_id' => $attendance->subject_id,
            'status' => $attendance->status,
            'date' => $attendance->date
        ]);
    }

    public function getStudentReport(Student $student): array
    {
        return Cache::remember(
            "student.{$student->id}.attendance",
            now()->addHours(1),
            fn () => $this->generateStudentReport($student)
        );
    }

    private function generateStudentReport(Student $student): array
    {
        return [
            'student' => $student->only(['id', 'name']),
            'class' => $student->class->only(['id', 'name', 'grade', 'section']),
            'total_absences' => $student->absences()->count(),
            'monthly_absences' => $student->absences()
                ->whereMonth('date', now()->month)
                ->count(),
            'term_absences' => $student->absences()
                ->whereBetween('date', [
                    now()->startOfQuarter(),
                    now()->endOfQuarter()
                ])
                ->count(),
            'recent_attendance' => $student->attendances()
                ->with(['student:id,name', 'subject:id,name'])
                ->latest('date')
                ->take(10)
                ->get()
        ];
    }
} 