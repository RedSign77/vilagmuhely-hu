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
        Schema::create('world_map_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('map_width')->default(200);
            $table->unsignedInteger('map_height')->default(200);
            $table->unsignedInteger('tile_size')->default(64); // pixels per map unit
            $table->string('default_biome')->default('meadow');
            $table->string('generation_seed')->nullable();
            $table->timestamp('last_regenerated_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_map_config');
    }
};
