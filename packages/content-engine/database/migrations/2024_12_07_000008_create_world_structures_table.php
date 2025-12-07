<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('structure_type'); // cottage, workshop, gallery, library, academy, tower, monument, garden
            $table->string('category_slug')->nullable(); // links to content category
            $table->integer('grid_x');
            $table->integer('grid_y');
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('health')->default(100);
            $table->enum('decay_state', ['active', 'fading', 'ruined'])->default('active');
            $table->json('metadata')->nullable(); // custom colors, decorations
            $table->timestamp('placed_at');
            $table->timestamp('last_owner_activity')->nullable();
            $table->timestamp('decay_started_at')->nullable();
            $table->timestamps();

            $table->unique(['grid_x', 'grid_y']); // One structure per cell
            $table->index(['user_id']);
            $table->index(['structure_type']);
            $table->index(['decay_state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_structures');
    }
};
