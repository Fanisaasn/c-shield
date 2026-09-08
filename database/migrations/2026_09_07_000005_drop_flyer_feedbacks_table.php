<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The per-flyer free-text feedback is replaced by a single, general
     * satisfaction survey (see survey_questions / survey_responses /
     * survey_answers) that is not tied to any specific flyer.
     */
    public function up(): void
    {
        Schema::dropIfExists('flyer_feedbacks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('flyer_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flyer_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }
};
