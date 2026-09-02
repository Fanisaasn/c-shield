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
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pre', 'post']);
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('level', ['Sangat Rendah', 'Rendah', 'Cukup', 'Baik', 'Sangat Baik'])->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_attempts');
    }
};
