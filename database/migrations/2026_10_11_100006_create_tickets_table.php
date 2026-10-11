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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->unique();
            $table->string('subject');
            $table->text('description');
            $table->string('priority', 20);
            $table->string('status', 30);
            $table->string('source', 20)->default('web');
            $table->string('external_reference', 100)->nullable()->unique();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            // SLA tracking
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('sla_paused_at')->nullable();
            $table->unsignedInteger('sla_paused_minutes')->default(0);
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('sla_warning_sent_at')->nullable();
            $table->timestamp('sla_breached_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->unsignedTinyInteger('escalation_level')->default(0);

            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->unsignedInteger('lock_version')->default(0);

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->index(['status', 'priority']);
            $table->index(['status', 'sla_due_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['created_by', 'status']);
            $table->index('created_at');
            $table->fullText(['subject', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
