<?php

namespace App\Exceptions;

use Exception;

class AttendanceException extends Exception
{
    public static function duplicateAttendance(int $studentId, string $date, int $subjectId): self
    {
        return new self("Attendance already exists for student {$studentId} in subject {$subjectId} on {$date}");
    }

    public static function duplicateInRequest(): self
    {
        return new self('Duplicate attendance records found in request');
    }

    public static function futureDate(): self
    {
        return new self('Cannot mark attendance for future dates');
    }
} 