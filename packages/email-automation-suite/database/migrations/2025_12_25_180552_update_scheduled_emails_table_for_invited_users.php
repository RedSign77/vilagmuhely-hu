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
        Schema::table('scheduled_emails', function (Blueprint $table) {
            // Change data_source enum to replace 'orders' with 'invited_users'
            $table->enum('data_source', ['users', 'invited_users'])->default('users')->change();

            // Add invitation_statuses column
            $table->json('invitation_statuses')->nullable()->after('recipient_users')->comment('Array of invitation statuses to filter');

            // Drop order-specific columns
            $table->dropColumn(['order_statuses', 'lookback_hours']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_emails', function (Blueprint $table) {
            // Revert data_source enum to original
            $table->enum('data_source', ['users', 'orders'])->default('users')->change();

            // Drop invitation_statuses column
            $table->dropColumn('invitation_statuses');

            // Re-add order-specific columns
            $table->json('order_statuses')->nullable()->comment('Array of order statuses to filter');
            $table->integer('lookback_hours')->nullable()->comment('How many hours to look back for order updates');
        });
    }
};
