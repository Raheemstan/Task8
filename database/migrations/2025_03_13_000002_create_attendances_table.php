<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent']);
            $table->string('subject');
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['student_id', 'date', 'status']);
            $table->index(['date', 'status']); // For class-wide queries
            $table->index(['student_id', 'status']); // For absence counting
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
}; 