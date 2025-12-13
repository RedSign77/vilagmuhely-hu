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
        Schema::create('world_element_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_element_type_id')->constrained('world_element_types')->onDelete('cascade');
            $table->integer('position_x');
            $table->integer('position_y');
            $table->decimal('rotation', 5, 2)->default(0); // 0-360 degrees
            $table->decimal('scale', 3, 2)->default(1.00); // 0.5-2.0
            $table->string('variant')->nullable();
            $table->enum('biome', ['forest', 'meadow', 'desert', 'tundra', 'swamp'])->nullable();
            $table->boolean('is_interactable')->default(true);
            $table->unsignedInteger('interaction_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['position_x', 'position_y']);
            $table->index('world_element_type_id');
            $table->index('biome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_element_instances');
    }
};
