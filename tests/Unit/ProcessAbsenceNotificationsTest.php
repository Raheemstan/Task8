<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Attendance;
use App\Jobs\ProcessAbsenceNotifications;
use App\Notifications\AbsenceWarning;
use App\Notifications\SuspensionNotice;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessAbsenceNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $class = SchoolClass::factory()->create();
        $this->student = Student::factory()->create(['class_id' => $class->id]);
        $this->subject = Subject::factory()->create();
    }

 
    public function it_sends_warning_notification_for_five_monthly_absences()
    {
        // Create 5 absences in current month
        Attendance::factory(5)->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'status' => 'absent',
            'date' => now()
        ]);

        (new ProcessAbsenceNotifications($this->student))->handle();

        Notification::assertSentTo(
            $this->student,
            AbsenceWarning::class,
            function ($notification) {
                return $notification->absences === 5;
            }
        );
    }

 
    public function it_sends_suspension_notice_for_ten_term_absences()
    {
        // Create 10 absences in current term
        Attendance::factory(10)->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'status' => 'absent',
            'date' => now()
        ]);

        (new ProcessAbsenceNotifications($this->student))->handle();

        Notification::assertSentTo(
            $this->student,
            SuspensionNotice::class
        );
    }

 
    public function it_does_not_send_notifications_for_fewer_absences()
    {
        // Create only 3 absences
        Attendance::factory(3)->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'status' => 'absent',
            'date' => now()
        ]);

        (new ProcessAbsenceNotifications($this->student))->handle();

        Notification::assertNothingSent();
    }
} 