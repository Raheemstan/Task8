<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SingleAttendanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'string', 'in:present,absent'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ];
    }
} 