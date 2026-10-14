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
        Schema::table('webinar_registrations', function (Blueprint $table) {
            $table->string('referral_source')->nullable()->after('motivation');
            $table->string('whatsapp_status', 20)->default('pending')->after('referral_source');
            $table->dateTime('whatsapp_sent_at')->nullable()->after('whatsapp_status');
            $table->text('whatsapp_error')->nullable()->after('whatsapp_sent_at');
        });

        Schema::table('webinars', function (Blueprint $table) {
            $table->string('meeting_link')->nullable()->after('registration_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webinar_registrations', function (Blueprint $table) {
            $table->dropColumn(['referral_source', 'whatsapp_status', 'whatsapp_sent_at', 'whatsapp_error']);
        });

        Schema::table('webinars', function (Blueprint $table) {
            $table->dropColumn('meeting_link');
        });
    }
};
