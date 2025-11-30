<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email_verified_at');
            $table->string('mobile')->nullable()->after('avatar');
            $table->string('city')->nullable()->after('mobile');
            $table->text('address')->nullable()->after('city');
            $table->json('social_media_links')->nullable()->after('address')->comment('Array of {platform, url}');
            $table->text('about')->nullable()->after('social_media_links')->comment('Biography');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'mobile', 'city', 'address', 'social_media_links', 'about']);
        });
    }
};
