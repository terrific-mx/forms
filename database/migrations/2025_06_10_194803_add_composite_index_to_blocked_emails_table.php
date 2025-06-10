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
        Schema::table('blocked_emails', function (Blueprint $table) {
            // Add composite index for optimal performance on our whereIn + form_id query
            // This index will be used when checking: WHERE form_id = ? AND email IN (...)
            $table->index(['form_id', 'email'], 'blocked_emails_form_email_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocked_emails', function (Blueprint $table) {
            $table->dropIndex('blocked_emails_form_email_index');
        });
    }
};
