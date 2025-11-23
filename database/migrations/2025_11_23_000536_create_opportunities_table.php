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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();

            // Company and User relationships
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Opportunity owner
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null'); // Associated lead

            // Basic opportunity information
            $table->string('title'); // Opportunity title/deal name
            $table->text('description')->nullable(); // Detailed description
            $table->string('account_name')->nullable(); // Customer/account name

            // Pipeline and stage management
            $table->string('pipeline')->default('sales'); // Pipeline type (sales, customer-success, etc.)
            $table->string('stage'); // Current stage (prospecting, qualification, proposal, negotiation, closed-won, closed-lost)
            $table->integer('stage_order')->default(1); // Order within stage for sorting

            // Value and revenue information
            $table->decimal('amount', 15, 2)->nullable(); // Deal amount
            $table->string('currency', 3)->default('USD');
            $table->decimal('probability', 5, 2)->default(0); // Win probability (0-100%)
            $table->decimal('weighted_amount', 15, 2)->nullable(); // amount * probability
            $table->integer('expected_close_days')->nullable(); // Days to expected close

            // Deal status and classification
            $table->string('status')->default('active'); // active, won, lost, paused
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('type')->nullable(); // new-business, existing-business, renewal, expansion
            $table->string('source')->nullable(); // Deal source (inbound, outbound, referral, etc.)

            // Contact and account information
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('decision_maker')->nullable(); // Decision maker name
            $table->text('decision_makers')->nullable(); // Multiple decision makers (JSON)

            // Competition information
            $table->string('competitors')->nullable(); // Main competitors
            $table->text('competitive_advantages')->nullable(); // Our advantages
            $table->text('competitive_weaknesses')->nullable(); // Their advantages

            // Timeline and next steps
            $table->date('expected_close_date')->nullable();
            $table->text('next_steps')->nullable(); // Action items
            $table->date('follow_up_date')->nullable();

            // Team and collaboration
            $table->json('team_members')->nullable(); // Team members involved (JSON with user_id and role)
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('won_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('lost_by')->nullable()->constrained('users')->onDelete('set null');

            // Outcome information
            $table->text('won_reason')->nullable(); // Why we won
            $table->text('lost_reason')->nullable(); // Why we lost
            $table->decimal('actual_amount', 15, 2)->nullable(); // Final deal amount when won
            $table->date('closed_date')->nullable(); // When the deal was closed
            $table->integer('sales_cycle_days')->nullable(); // Days from creation to close

            // Additional details
            $table->json('custom_fields')->nullable(); // Custom opportunity fields
            $table->json('tags')->nullable(); // Opportunity tags
            $table->longText('notes')->nullable(); // Internal notes

            // Metadata
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'pipeline']);
            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'pipeline']);
            $table->index(['user_id', 'status']);
            $table->index(['stage', 'pipeline']);
            $table->index(['priority']);
            $table->index(['expected_close_date']);
            $table->index(['probability']);
            $table->index(['amount']);
            $table->index(['status', 'stage']);
            $table->index(['contact_email']);
            $table->index(['account_name']);
            $table->index(['title']);
            $table->index(['pipeline', 'stage_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
