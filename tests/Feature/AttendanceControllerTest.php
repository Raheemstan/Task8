<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Attendance;
use App\Jobs\ProcessAbsenceNotifications;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private SchoolClass $class;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Prevent jobs from being processed during tests
        Queue::fake();
        
        // Prevent cache from interfering with tests
        Cache::flush();

        // Create test data
        $this->class = SchoolClass::factory()->create();
        $this->subject = Subject::factory()->create();
    }

    public function it_can_mark_bulk_attendance()
    {
        $students = Student::factory(3)->create(['class_id' => $this->class->id]);
        
        $attendanceData = $students->map(fn ($student) => [
            'student_id' => $student->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
            'subject_id' => $this->subject->id
        ])->toArray();

        $response = $this->postJson('/api/v1/attendance/bulk', [
            'attendances' => $attendanceData
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'student_id',
                        'subject_id',
                        'date',
                        'status'
                    ]
                ]
            ]);

        // Assert attendance was recorded
        $this->assertDatabaseCount('attendances', 3);
        
        // Assert jobs were dispatched
        Queue::assertPushed(ProcessAbsenceNotifications::class, 3);
    }

    public function it_can_mark_single_student_attendance()
    {
        $student = Student::factory()->create(['class_id' => $this->class->id]);

        $response = $this->postJson("/api/v1/attendance/{$student->id}", [
            'date' => now()->toDateString(),
            'status' => 'absent',
            'subject_id' => $this->subject->id
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'student_id',
                    'subject_id',
                    'date',
                    'status'
                ]
            ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'status' => 'absent'
        ]);

        Queue::assertPushed(ProcessAbsenceNotifications::class);
    }

    public function it_can_get_student_attendance_report()
    {
        $student = Student::factory()->create();
        Attendance::factory(5)->create([
            'student_id' => $student->id,
            'status' => 'absent'
        ]);

        $response = $this->getJson("/api/v1/attendance/report/{$student->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'student' => ['id', 'name', 'class'],
                'total_absences',
                'monthly_absences',
                'term_absences',
                'recent_attendance'
            ]);

        $this->assertEquals(5, $response->json('total_absences'));
    }

    public function it_can_get_class_attendance_report()
    {
        $class = '10A';
        $students = Student::factory(5)->create(['class' => $class]);
        
        // Create some attendance records
        foreach ($students as $student) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => now(),
                'status' => 'present'
            ]);
            
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => now(),
                'status' => 'absent'
            ]);
        }

        $response = $this->getJson("/api/v1/attendance/class/{$class}");

        $response->assertOk()
            ->assertJsonStructure([
                'class',
                'total_students',
                'attendance_summary' => [
                    'present',
                    'absent'
                ],
                'student_details' => [
                    '*' => [
                        'id',
                        'name',
                        'monthly_absences'
                    ]
                ]
            ]);

        $this->assertEquals(5, $response->json('total_students'));
        $this->assertEquals(5, $response->json('attendance_summary.present'));
        $this->assertEquals(5, $response->json('attendance_summary.absent'));
    }


    public function it_validates_bulk_attendance_input()
    {
        $response = $this->postJson('/api/v1/attendance/bulk', [
            'attendances' => [
                [
                    'student_id' => 999, // Non-existent student
                    'date' => 'invalid-date',
                    'status' => 'invalid-status',
                    'subject_id' => 0
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'attendances.0.student_id',
                'attendances.0.date',
                'attendances.0.status',
                'attendances.0.subject_id'
            ]);
    }


    public function it_validates_single_attendance_input()
    {
        $student = Student::factory()->create();

        $response = $this->postJson("/api/v1/attendance/{$student->id}", [
            'date' => 'invalid-date',
            'status' => 'invalid-status',
            'subject_id' => 0
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'date',
                'status',
                'subject_id'
            ]);
    }


    public function it_enforces_authentication()
    {
        // Clear authenticated user
        auth()->guard('sanctum')->logout();

        $response = $this->getJson('/api/v1/attendance/report/1');
        
        $response->assertStatus(401);
    }


    public function it_handles_rate_limiting()
    {
        // Make 61 requests (exceeding the 60 per minute limit)
        for ($i = 0; $i < 61; $i++) {
            $this->getJson('/api/v1/attendance/report/1');
        }

        $response = $this->getJson('/api/v1/attendance/report/1');
        
        $response->assertStatus(429); // Too Many Requests
    }


    public function it_caches_attendance_reports()
    {
        $student = Student::factory()->create();
        
        // First request should miss cache
        $this->getJson("/api/v1/attendance/report/{$student->id}");
        
        // Verify cache exists
        $this->assertTrue(Cache::has("student.{$student->id}.attendance"));
        
        // Second request should hit cache
        $response = $this->getJson("/api/v1/attendance/report/{$student->id}");
        $response->assertOk();
    }
} 