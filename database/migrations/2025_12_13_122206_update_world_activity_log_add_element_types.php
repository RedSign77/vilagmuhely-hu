<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Update world_activity_log to support the new element-based system:
     * - Drop foreign key constraint on structure_id (we'll reuse this column for element_id)
     * - Add new activity types: element_discovered, element_harvested
     */
    public function up(): void
    {
        Schema::table('world_activity_log', function (Blueprint $table) {
            // Drop the foreign key constraint on structure_id
            // We're reusing this column to store element_instance_id for the new system
            $table->dropForeign(['structure_id']);
        });

        // Note: activity_type column already supports VARCHAR values
        // New activity types will be used:
        // - element_discovered: When user clicks on an element for the first time
        // - element_harvested: When user claims resource bonus from an element
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_activity_log', function (Blueprint $table) {
            // Restore the foreign key constraint
            $table->foreign('structure_id')
                ->references('id')
                ->on('world_structures')
                ->onDelete('set null');
        });
    }
};
