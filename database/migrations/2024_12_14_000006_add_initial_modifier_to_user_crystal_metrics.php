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
        Schema::table('user_crystal_metrics', function (Blueprint $table) {
            $table->decimal('profile_completeness_modifier', 5, 2)->default(0)->after('purity_level');
            $table->boolean('initial_modifier_applied')->default(false)->after('profile_completeness_modifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_crystal_metrics', function (Blueprint $table) {
            $table->dropColumn(['profile_completeness_modifier', 'initial_modifier_applied']);
        });
    }
};
