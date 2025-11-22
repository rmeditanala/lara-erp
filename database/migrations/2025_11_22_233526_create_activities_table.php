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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Polymorphic relationships for different activity subjects
            $table->morphs('subject'); // subject_type and subject_id

            // Activity details
            $table->string('type'); // created, updated, deleted, status_changed, etc.
            $table->string('title');
            $table->text('description')->nullable();

            // Additional data stored as JSON
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
