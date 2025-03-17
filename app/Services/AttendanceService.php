<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Collection;
use App\Jobs\ProcessAbsenceNotifications;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\AttendanceException;

class AttendanceService
{
    public function markBulkAttendance(array $attendances): Collection
    {
        // Check for duplicates in the request
        $duplicates = collect($attendances)
            ->groupBy(fn($record) => "{$record['student_id']}_{$record['subject_id']}_{$record['date']}")
            ->filter(fn($group) => $group->count() > 1);

        if ($duplicates->isNotEmpty()) {
            throw new \App\Exceptions\AttendanceException(
                'Duplicate attendance records found in request'
            );
        }

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
        try {// Assuming $data contains the new attendance record
            $attendanceData = $data;  // The new data for attendance (e.g., student_id, subject_id, date)
            
            $existingAttendance = Attendance::where('student_id', $attendanceData['student_id'])
                                             ->where('subject_id', $attendanceData['subject_id'])
                                             ->where('date', $attendanceData['date'])
                                             ->first();
            
            if ($existingAttendance) {
                // If attendance already exists, you can return a message or handle it as needed
              throw AttendanceException::duplicateAttendance(
                $data['student_id'],
                $data['date'],
                $data['subject_id']
              );
            } else {
                // If no existing attendance record, create a new one
                $attendance = Attendance::create($attendanceData);
            
            if ($attendance->status === 'absent') {
                ProcessAbsenceNotifications::dispatch($attendance->student);
            }

            $this->clearCache($attendance);
            $this->logAttendance($attendance);

            return $attendance;
                
            }
            


        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a unique constraint violation
            if ($e->errorInfo[1] === 19) { // SQLite unique constraint error code
                throw AttendanceException::duplicateAttendance(
                    $data['student_id'],
                    $data['date'],
                    $data['subject_id']
                );
            }
            throw $e;
        }
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
        // First get the student with relationships
        $student = Student::with([
            'class:id,name,grade,section',
            'attendances' => function ($query) {
                $query->with('subject:id,name')
                    ->latest('date')
                    ->take(10);
            }
        ])->findOrFail($student->id);

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
            'recent_attendance' => $student->attendances->map(fn($attendance) => [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'status' => $attendance->status,
                'subject' => $attendance->subject->only(['id', 'name'])
            ])
        ];
    }
} 