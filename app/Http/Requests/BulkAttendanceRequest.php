<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'attendances' => ['required', 'array'],
            'attendances.*.student_id' => ['required', 'exists:students,id'],
            'attendances.*.date' => ['required', 'date', 'before_or_equal:today'],
            'attendances.*.status' => ['required', 'in:present,absent'],
            'attendances.*.subject_id' => ['required', 'exists:subjects,id'],
        ];
    }
} 