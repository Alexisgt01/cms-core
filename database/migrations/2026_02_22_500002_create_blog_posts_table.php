<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // Content
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('content')->nullable();
            $table->json('featured_images')->nullable();
            $table->unsignedSmallInteger('reading_time_minutes')->nullable();

            // Author
            $table->foreignId('author_id')->nullable()->constrained('blog_authors')->nullOnDelete();

            // State / Publication
            $table->string('state')->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('first_published_at')->nullable();
            $table->timestamp('updated_content_at')->nullable();

            // SEO
            $table->boolean('indexing')->default(true);
            $table->string('canonical_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('robots_index')->nullable();
            $table->boolean('robots_follow')->nullable();
            $table->boolean('robots_noarchive')->nullable();
            $table->boolean('robots_nosnippet')->nullable();
            $table->smallInteger('robots_max_snippet')->nullable();
            $table->string('robots_max_image_preview', 20)->nullable();
            $table->smallInteger('robots_max_video_preview')->nullable();

            // Open Graph
            $table->string('og_type', 50)->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->json('og_image')->nullable();

            // Twitter
            $table->string('twitter_card', 30)->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->json('twitter_image')->nullable();

            // Schema / JSON-LD
            $table->string('schema_type', 30)->nullable();
            $table->json('schema_json')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
