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
        Schema::create('content_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Review content
            $table->string('title')->nullable();
            $table->text('review_text');

            // Moderation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->text('moderation_notes')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();

            // Engagement
            $table->unsignedInteger('helpful_votes')->default(0);

            // Edit tracking
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['content_id', 'user_id']);
            $table->index('status');
            $table->index(['helpful_votes', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_reviews');
    }
};
