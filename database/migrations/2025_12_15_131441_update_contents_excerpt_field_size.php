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
        Schema::table('contents', function (Blueprint $table) {
            // Modify excerpt to explicitly support up to 65,535 characters (TEXT type)
            // This ensures it can hold 2048+ characters as required
            $table->text('excerpt')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Revert to text (though it's the same type, this is for completeness)
            $table->text('excerpt')->nullable()->change();
        });
    }
};
