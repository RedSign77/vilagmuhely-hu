<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_world_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('stone')->default(0);
            $table->unsignedInteger('wood')->default(0);
            $table->unsignedInteger('crystal_shards')->default(10); // Start with 10
            $table->unsignedInteger('magic_essence')->default(0);
            $table->unsignedInteger('total_structures_built')->default(0);
            $table->unsignedInteger('total_upgrades_done')->default(0);
            $table->timestamp('last_resource_claim')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_world_resources');
    }
};
