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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();

            // Content type: digital_file, image_gallery, markdown_post, article, rpg_module
            $table->enum('type', [
                'digital_file',
                'image_gallery',
                'markdown_post',
                'article',
                'rpg_module',
            ]);

            // Content status: draft, preview, members_only, public
            $table->enum('status', [
                'draft',
                'preview',
                'members_only',
                'public',
            ])->default('draft');

            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable(); // For markdown/article content

            // Metadata
            $table->foreignId('category_id')->nullable()->constrained('content_categories')->nullOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();

            // File-related fields
            $table->string('file_path')->nullable(); // For digital files
            $table->string('file_type')->nullable(); // PDF, ZIP, etc.
            $table->unsignedBigInteger('file_size')->nullable(); // In bytes

            // Gallery-related fields
            $table->json('gallery_images')->nullable(); // Array of image paths

            // Additional metadata
            $table->json('metadata')->nullable(); // Additional flexible metadata

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Featured image
            $table->string('featured_image')->nullable();

            // Stats
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('downloads_count')->default(0);

            // Publishing
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('type');
            $table->index('status');
            $table->index('creator_id');
            $table->index('category_id');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
