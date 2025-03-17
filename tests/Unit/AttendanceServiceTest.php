<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Services\AttendanceService;
use App\Exceptions\AttendanceException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceService $service;
    private Student $student;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->artisan('migrate:fresh');
        
        $this->service = new AttendanceService();
        
        $class = SchoolClass::factory()->create();
        $this->student = Student::factory()->create(['class_id' => $class->id]);
        $this->subject = Subject::factory()->create();
    }

    public function test_marks_single_attendance(): void
    {
        $attendance = $this->service->markAttendance($this->student, [
            'date' => now()->toDateString(),
            'status' => 'present',
            'subject_id' => $this->subject->id
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'status' => 'present'
        ]);
    }

    public function test_prevents_duplicate_attendance(): void
    {
        $this->expectException(AttendanceException::class);

        $data = [
            'date' => now()->toDateString(),
            'status' => 'present',
            'subject_id' => $this->subject->id
        ];

        $this->service->markAttendance($this->student, $data);
        $this->service->markAttendance($this->student, $data);
    }
} 