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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Unique slug identifier for triggering template from code');
            $table->string('subject')->comment('Email subject line with {{ variable }} support');
            $table->text('body')->comment('Markdown email body with {{ variable }} support');
            $table->text('description')->nullable()->comment('Admin documentation for template purpose');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
