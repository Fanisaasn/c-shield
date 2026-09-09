<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('flyers')->whereNotNull('image')->orderBy('id')->get()->each(function ($flyer) {
            DB::table('flyer_images')->insert([
                'flyer_id' => $flyer->id,
                'image' => $flyer->image,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('flyers', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flyers', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        DB::table('flyer_images')->orderBy('flyer_id')->orderBy('sort_order')->get()
            ->groupBy('flyer_id')
            ->each(function ($images, $flyerId) {
                DB::table('flyers')->where('id', $flyerId)->update([
                    'image' => $images->first()->image,
                ]);
            });

        Schema::dropIfExists('flyer_images');
    }
};
