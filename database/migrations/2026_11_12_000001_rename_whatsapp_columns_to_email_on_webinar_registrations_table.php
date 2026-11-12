<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengiriman link webinar beralih dari WhatsApp ke email,
     * sehingga kolom pelacak status ikut diganti namanya.
     */
    public function up(): void
    {
        Schema::table('webinar_registrations', function (Blueprint $table) {
            $table->renameColumn('whatsapp_status', 'email_status');
            $table->renameColumn('whatsapp_sent_at', 'email_sent_at');
            $table->renameColumn('whatsapp_error', 'email_error');
        });
    }

    public function down(): void
    {
        Schema::table('webinar_registrations', function (Blueprint $table) {
            $table->renameColumn('email_status', 'whatsapp_status');
            $table->renameColumn('email_sent_at', 'whatsapp_sent_at');
            $table->renameColumn('email_error', 'whatsapp_error');
        });
    }
};
