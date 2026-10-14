<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow several ordered study materials, including interactive videos
     * (file_type "interactive"), and remember a video's length so the
     * server can check it was watched in full.
     */
    public function up(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('id');
            $table->unsignedInteger('duration_seconds')->nullable()->after('file_type');
            $table->string('file_type', 20)->nullable()->change(); // pdf, ppt, video, interactive
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'duration_seconds']);
            $table->string('file_type', 10)->nullable()->change();
        });
    }
};
