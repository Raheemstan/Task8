<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Student $student;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->artisan('migrate:fresh');
        
        // Create and authenticate user
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Create test data
        $class = SchoolClass::factory()->create();
        $this->student = Student::factory()->create(['class_id' => $class->id]);
        $this->subject = Subject::factory()->create();
    }

    public function test_marks_attendance(): void
    {
        $response = $this->postJson("/api/attendance/{$this->student->id}", [
            'date' => now()->toDateString(),
            'status' => 'present',
            'subject_id' => $this->subject->id
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Attendance marked successfully']);
    }

    public function test_validates_attendance_input(): void
    {
        $response = $this->postJson("/api/attendance/{$this->student->id}", [
            'date' => 'invalid-date',
            'status' => 'invalid-status',
            'subject_id' => 999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'status', 'subject_id']);
    }

    public function test_gets_student_report(): void
    {
        $response = $this->getJson("/api/attendance/report/{$this->student->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'student',
                'class',
                'total_absences',
                'monthly_absences',
                'recent_attendance'
            ]);
    }
} 