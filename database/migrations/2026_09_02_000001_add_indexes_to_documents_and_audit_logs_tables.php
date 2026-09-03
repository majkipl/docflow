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
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['state', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('document_audit_logs', function (Blueprint $table) {
            $table->index(['document_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_audit_logs', function (Blueprint $table) {
            $table->dropIndex(['document_id', 'created_at']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['state', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
