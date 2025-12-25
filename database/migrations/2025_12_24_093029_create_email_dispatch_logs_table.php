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
        Schema::create('email_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_email_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipient_user_id')->constrained('users')->onDelete('cascade');
            $table->string('data_source')->comment('users or orders');
            $table->unsignedBigInteger('source_record_id')->nullable()->comment('ID of the order/user record');
            $table->string('recipient_email');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Composite unique index for deduplication
            $table->unique(['scheduled_email_id', 'email_template_id', 'source_record_id', 'recipient_user_id'], 'dispatch_dedup_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_dispatch_logs');
    }
};
