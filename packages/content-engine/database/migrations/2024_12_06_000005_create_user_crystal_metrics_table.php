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
        Schema::create('user_crystal_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Content metrics
            $table->unsignedInteger('total_content_count')->default(0);
            $table->decimal('diversity_index', 5, 3)->default(0); // 0.000 - 1.000

            // Interaction metrics
            $table->decimal('interaction_score', 10, 2)->default(0);
            $table->decimal('engagement_score', 10, 2)->default(0);

            // Crystal dimensions
            $table->unsignedTinyInteger('facet_count')->default(4); // Min 4, max ~50
            $table->decimal('glow_intensity', 3, 2)->default(0); // 0.00 - 1.00
            $table->decimal('purity_level', 3, 2)->default(0.5); // 0.00 - 1.00

            // Visual properties
            $table->json('dominant_colors')->nullable(); // Array of hex colors
            $table->json('cached_geometry')->nullable(); // Complete 3D data for frontend

            // Tracking
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->index('last_calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_crystal_metrics');
    }
};
