<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class Student extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'parent_email',
        'class',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Add index for faster class-based queries
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($student) {
            Cache::forget("class.{$student->class}.attendance");
        });
        
        static::deleted(function ($student) {
            Cache::forget("student.{$student->id}.attendance");
            Cache::forget("class.{$student->class}.attendance");
        });
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Attendance::class)->where('status', 'absent');
    }
} 