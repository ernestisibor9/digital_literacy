<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id'); // References instructor
            $table->string('title'); // Course title
            $table->text('description')->nullable(); // Course description
            $table->decimal('price', 8, 2); // Course price
            $table->enum('status', ['published', 'unpublished'])->default('unpublished'); // Course status
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};