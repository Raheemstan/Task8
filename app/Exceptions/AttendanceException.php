<?php

namespace App\Exceptions;

use Exception;

class AttendanceException extends Exception
{
    public static function duplicateAttendance(int $studentId, string $date, int $subjectId): self
    {
        return new self("Attendance already marked for student {$studentId} on {$date} for subject {$subjectId}");
    }

    public static function futureDate(): self
    {
        return new self("Cannot mark attendance for future dates");
    }
} 