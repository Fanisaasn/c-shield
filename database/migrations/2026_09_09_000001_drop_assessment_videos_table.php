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
        Schema::dropIfExists('assessment_videos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('assessment_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_path')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }
};
