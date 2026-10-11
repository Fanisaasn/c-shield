<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A question can now have several correct options, so a respondent's
 * answer to one question can pick several options. The chosen options
 * move from assessment_answers.assessment_option_id into a pivot table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Read the existing choices first: dropping the column rebuilds the
        // table on SQLite, which would cascade-delete pivot rows made earlier.
        $existing = DB::table('assessment_answers')
            ->whereNotNull('assessment_option_id')
            ->get(['id as assessment_answer_id', 'assessment_option_id'])
            ->map(fn ($row) => (array) $row);

        Schema::table('assessment_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assessment_option_id');
        });

        Schema::create('assessment_answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_option_id')->constrained()->cascadeOnDelete();

            $table->unique(['assessment_answer_id', 'assessment_option_id'], 'assessment_answer_options_unique');
        });

        foreach ($existing->chunk(500) as $chunk) {
            DB::table('assessment_answer_options')->insert($chunk->values()->all());
        }
    }

    /**
     * Reverse the migrations. Only the first chosen option of each answer
     * can be restored.
     */
    public function down(): void
    {
        $firstChoices = DB::table('assessment_answer_options')
            ->selectRaw('assessment_answer_id, MIN(assessment_option_id) as option_id')
            ->groupBy('assessment_answer_id')
            ->get();

        Schema::dropIfExists('assessment_answer_options');

        Schema::table('assessment_answers', function (Blueprint $table) {
            $table->foreignId('assessment_option_id')->nullable()->after('assessment_question_id')
                ->constrained()->cascadeOnDelete();
        });

        $firstChoices->each(fn ($row) => DB::table('assessment_answers')
            ->where('id', $row->assessment_answer_id)
            ->update(['assessment_option_id' => $row->option_id]));
    }
};
