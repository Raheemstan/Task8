<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use App\Services\AttendanceService;
use App\Http\Controllers\Controller;
use App\Repositories\AttendanceRepository;
use App\Http\Requests\BulkAttendanceRequest;
use App\Http\Requests\SingleAttendanceRequest;
use Illuminate\Support\Facades\Log;
use App\Exceptions\AttendanceException;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceRepository $attendanceRepository
    ) {}

    public function markBulkAttendance(BulkAttendanceRequest $request): JsonResponse
    {
        try {
            $attendances = $this->attendanceService->markBulkAttendance(
                $request->validated('attendances')
            );
            Log::info('Attendance marked successfully', ['attendances' => $attendances]);

            return response()->json([
                'message' => 'Attendance marked successfully',
                'data' => $attendances
            ], 201);
        } catch (AttendanceException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mark attendance for a single student
     * 
     * @param Student $student
     * @param SingleAttendanceRequest $request
     * @return JsonResponse
     */
    public function markAttendance(Student $student, SingleAttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->markAttendance(
                $student,
                $request->validated()
            );

            return response()->json([
                'message' => 'Attendance marked successfully',
                'data' => $attendance
            ], 201);
        } catch (AttendanceException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get attendance report for a student
     * 
     * @param Student $student
     * @return JsonResponse
     */
    public function getReport(Student $student): JsonResponse
    {
        Log::info('Getting attendance report for student', ['student' => $student->id]);
        return response()->json(
            $this->attendanceService->getStudentReport($student)
        );
    }

    /**
     * Get attendance report for a class
     * 
     * @param SchoolClass $class
     * @return JsonResponse
     */
    public function getClassReport(SchoolClass $class): JsonResponse
    {
        try {
            $date = request('date', now()->toDateString());
            $report = $this->attendanceRepository->getClassAttendance($class, $date);
            
            return response()->json($report);
        } catch (\Exception $e) {
            Log::error('Failed to generate class report', [
                'class_id' => $class->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to generate report'
            ], 500);
        }
    }
} 