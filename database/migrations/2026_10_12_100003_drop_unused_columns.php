<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('lock_version');
        });

        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });

        Schema::table('sla_policies', function (Blueprint $table) {
            $table->dropColumn('first_response_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedInteger('lock_version')->default(0)->after('closed_at');
        });

        Schema::table('imports', function (Blueprint $table) {
            $table->string('batch_id', 36)->nullable()->after('failed_rows');
        });

        Schema::table('sla_policies', function (Blueprint $table) {
            $table->unsignedInteger('first_response_minutes')->nullable()->after('resolution_minutes');
        });
    }
};
