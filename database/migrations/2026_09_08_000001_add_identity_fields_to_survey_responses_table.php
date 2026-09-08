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
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->date('service_date')->nullable()->after('name');
            $table->string('education')->nullable()->after('service_date');
            $table->unsignedTinyInteger('age')->nullable()->after('education');
            $table->string('occupation')->nullable()->after('age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['service_date', 'education', 'age', 'occupation']);
        });
    }
};
