<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * video_url is now editable again from the admin form (as the link to
     * the original Instagram/YouTube/other source), independent of whether
     * a video file was uploaded, so it needs to accept null instead of ''.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('video_url')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('video_url')->default('')->change();
        });
    }
};
