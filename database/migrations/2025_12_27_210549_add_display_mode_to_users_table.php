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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('display_mode', ['anonymous', 'public'])->default('anonymous')->after('username');
            $table->timestamp('display_mode_changed_at')->nullable()->after('display_mode');
            $table->index('display_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['display_mode']);
            $table->dropColumn(['display_mode', 'display_mode_changed_at']);
        });
    }
};
