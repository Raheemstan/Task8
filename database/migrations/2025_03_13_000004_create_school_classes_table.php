<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('grade');
            $table->string('section');
            $table->string('academic_year');
            $table->timestamps();

            $table->unique(['grade', 'section', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
}; 