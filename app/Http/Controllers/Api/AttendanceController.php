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

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Student Attendance API",
 *     description="API for managing student attendance"
 * )
 */

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceRepository $attendanceRepository
    ) {}

    /**
     * Mark attendance for multiple students
     * 
     * @param BulkAttendanceRequest $request
     * @return JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/api/v1/attendance/bulk",
     *     summary="Mark attendance for multiple students",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="attendances",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="integer"),
     *                     @OA\Property(property="date", type="string", format="date"),
     *                     @OA\Property(property="status", type="string", enum={"present", "absent"}),
     *                     @OA\Property(property="subject", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Attendance marked successfully"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function markBulkAttendance(BulkAttendanceRequest $request): JsonResponse
    {
        try {
            $attendances = $this->attendanceService->markBulkAttendance(
                $request->validated('attendances')
            );

            return response()->json([
                'message' => 'Attendance marked successfully',
                'data' => $attendances
            ], 201);
        } catch (\Exception $e) {
            report($e);
            throw $e;
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
        } catch (\Exception $e) {
            report($e);
            throw $e;
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
        return response()->json(
            $this->attendanceRepository->getClassAttendance($class, request('date', now()->toDateString()))
        );
    }
} 