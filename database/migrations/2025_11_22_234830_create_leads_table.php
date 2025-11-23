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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Lead owner
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null'); // Converted to customer

            // Basic lead information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('company_name')->nullable(); // Lead's company (different from system company)
            $table->string('job_title')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();

            // Lead status and classification
            $table->string('status')->default('new'); // new, contacted, qualified, proposal, negotiation, converted, lost, recycled
            $table->string('source')->nullable(); // website, referral, cold_call, email, social, trade_show, etc.
            $table->string('industry')->nullable();
            $table->integer('employees')->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->integer('priority')->default(1); // 1-5 priority level

            // Lead scoring and qualification
            $table->integer('score')->default(0);
            $table->string('rating')->nullable(); // hot, warm, cold
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();

            // Conversion tracking
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('lost_reason')->nullable();
            $table->text('lost_details')->nullable();

            // Metadata
            $table->json('custom_fields')->nullable();
            $table->json('tags')->nullable(); // Lead tags for categorization

            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['email']);
            $table->index(['phone']);
            $table->index(['first_name']);
            $table->index(['last_name']);
            $table->index(['company_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
