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
        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Descriptive name for the scheduled email');
            $table->foreignId('email_template_id')->constrained()->onDelete('cascade')->comment('Email template to use');
            $table->string('cron_expression')->comment('5-part cron expression for scheduling');
            $table->boolean('is_enabled')->default(true)->comment('Global enable/disable toggle');

            // Data source and filtering
            $table->enum('data_source', ['users', 'orders'])->default('users')->comment('Source of recipient data');
            $table->enum('recipient_type', ['all', 'roles', 'individual'])->default('all')->comment('How recipients are selected');
            $table->json('recipient_roles')->nullable()->comment('Array of role IDs when recipient_type=roles');
            $table->json('recipient_users')->nullable()->comment('Array of user IDs when recipient_type=individual');

            // Order-specific filters
            $table->json('order_statuses')->nullable()->comment('Array of order statuses to filter');
            $table->integer('lookback_hours')->nullable()->comment('How many hours to look back for order updates');

            // Variable mapping
            $table->json('variable_mapping')->nullable()->comment('Maps data source fields to template variables');

            // Execution tracking
            $table->timestamp('last_run_at')->nullable()->comment('Last execution timestamp');
            $table->timestamp('next_run_at')->nullable()->comment('Next scheduled execution');
            $table->integer('total_sent')->default(0)->comment('Total emails sent');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');
    }
};
