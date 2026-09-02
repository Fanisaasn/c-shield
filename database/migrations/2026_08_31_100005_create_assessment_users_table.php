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
        Schema::create('assessment_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_last_digits', 4);
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->unsignedTinyInteger('age');
            $table->string('education');
            $table->string('domicile');
            $table->string('occupation_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_users');
    }
};
