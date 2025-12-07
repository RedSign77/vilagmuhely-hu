<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_zones', function (Blueprint $table) {
            $table->id();
            $table->string('zone_key')->unique(); // central, east, west, north, south
            $table->string('name');
            $table->string('zone_type'); // origin, creative, knowledge, etc.
            $table->integer('min_x');
            $table->integer('max_x');
            $table->integer('min_y');
            $table->integer('max_y');
            $table->unsignedInteger('unlock_at')->default(0); // Structure count to unlock
            $table->string('color')->default('#94a3b8');
            $table->boolean('is_unlocked')->default(false);
            $table->timestamps();

            $table->index(['is_unlocked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_zones');
    }
};
