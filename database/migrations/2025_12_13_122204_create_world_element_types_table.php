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
        Schema::create('world_element_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('category', ['vegetation', 'water', 'terrain', 'structure', 'decoration']);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('max_width')->default(128);
            $table->unsignedInteger('max_height')->default(128);
            $table->decimal('density_weight', 5, 2)->default(1.00);
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->json('resource_bonus')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('rarity');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_element_types');
    }
};
