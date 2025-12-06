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
        Schema::create('content_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Rating data
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->text('critique_text')->nullable();
            $table->boolean('is_helpful')->default(false); // Marked as helpful by content creator

            $table->timestamps();

            // Prevent duplicate ratings
            $table->unique(['content_id', 'user_id']);

            $table->index('rating');
            $table->index('is_helpful');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_ratings');
    }
};
