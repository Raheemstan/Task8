<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SingleAttendanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:present,absent'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ];
    }
} 