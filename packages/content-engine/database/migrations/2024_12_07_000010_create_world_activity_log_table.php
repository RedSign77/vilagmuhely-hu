<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type'); // structure_placed, structure_upgraded, resource_earned
            $table->foreignId('structure_id')->nullable()->constrained('world_structures')->onDelete('set null');
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_activity_log');
    }
};
