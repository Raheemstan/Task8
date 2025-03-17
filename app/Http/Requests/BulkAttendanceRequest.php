<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'attendances' => ['required', 'array'],
            'attendances.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'attendances.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'attendances.*.date' => ['required', 'date', 'before_or_equal:today'],
            'attendances.*.status' => ['required', 'string', 'in:present,absent'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $attendances = collect($this->attendances);
            
            // Check for duplicates in the request
            $duplicates = $attendances
                ->groupBy(fn($record) => "{$record['student_id']}_{$record['subject_id']}_{$record['date']}")
                ->filter(fn($group) => $group->count() > 1);

            if ($duplicates->isNotEmpty()) {
                $validator->errors()->add('attendances', 'Duplicate attendance records found');
            }
        });
    }
} 