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
        Schema::create('crystal_activity_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Activity type: content_published, content_viewed, content_downloaded, content_rated, achievement_unlocked
            $table->string('activity_type', 50);

            // Flexible metadata for additional context
            $table->json('metadata')->nullable(); // content_id, rating_id, etc.

            // Processing tracking
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'processed_at']);
            $table->index('activity_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crystal_activity_queue');
    }
};
