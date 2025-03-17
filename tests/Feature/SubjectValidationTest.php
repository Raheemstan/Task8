<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubjectValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Student $student;
    private SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        $this->class = SchoolClass::factory()->create();
        $this->student = Student::factory()->create(['class_id' => $this->class->id]);
    }

 
    public function it_validates_subject_exists()
    {
        $response = $this->postJson("/api/v1/attendance/{$this->student->id}", [
            'date' => now()->toDateString(),
            'status' => 'present',
            'subject_id' => 999 // Non-existent subject
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject_id']);
    }

 
    public function it_accepts_valid_subject()
    {
        $subject = Subject::factory()->create();

        $response = $this->postJson("/api/v1/attendance/{$this->student->id}", [
            'date' => now()->toDateString(),
            'status' => 'present',
            'subject_id' => $subject->id
        ]);

        $response->assertStatus(201);
    }

 
    public function it_validates_subject_in_bulk_attendance()
    {
        $response = $this->postJson('/api/v1/attendance/bulk', [
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'date' => now()->toDateString(),
                    'status' => 'present',
                    'subject_id' => 999 // Non-existent subject
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attendances.0.subject_id']);
    }
} 