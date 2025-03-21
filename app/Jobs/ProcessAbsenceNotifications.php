<?php

namespace App\Jobs;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use App\Notifications\AbsenceWarning;
use App\Notifications\SuspensionNotice;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessAbsenceNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Student $student) {}

    public function handle(): void
    {
        $monthlyAbsences = $this->student->absences()
            ->whereMonth('date', now()->month)
            ->count();

        $termAbsences = $this->student->absences()
            ->whereBetween('date', [
                now()->startOfQuarter(),
                now()->endOfQuarter()
            ])
            ->count();

        if ($monthlyAbsences === 5) {
            Log::info('Sending absence warning notification to student', ['student' => $this->student->id]);
            $this->student->notify(new AbsenceWarning(5, 'month'));
        }

        if ($termAbsences === 10) {
            Log::info('Sending suspension notice to student', ['student' => $this->student->id]);
            $this->student->notify(new SuspensionNotice());
        }
    }
} 