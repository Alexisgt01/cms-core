<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_settings', function (Blueprint $table) {
            $table->id();

            // Identity / Display
            $table->boolean('enabled')->default(false);
            $table->string('blog_name')->default('Blog');
            $table->text('blog_description')->nullable();
            $table->foreignId('default_author_id')->nullable()->constrained('blog_authors')->nullOnDelete();
            $table->unsignedSmallInteger('posts_per_page')->default(12);
            $table->boolean('show_author_on_post')->default(true);
            $table->boolean('show_reading_time')->default(true);
            $table->boolean('enable_comments')->default(false);
            $table->boolean('rss_enabled')->default(true);
            $table->string('rss_title')->nullable();
            $table->text('rss_description')->nullable();

            // Images
            $table->unsignedTinyInteger('featured_images_max')->default(1);
            $table->boolean('featured_image_required')->default(false);
            $table->json('og_image_fallback')->nullable();
            $table->json('twitter_image_fallback')->nullable();
            $table->unsignedSmallInteger('default_image_width')->default(1200);
            $table->unsignedSmallInteger('default_image_height')->default(0);

            // SEO / Indexation defaults
            $table->boolean('indexing_default')->default(true);
            $table->string('default_canonical_mode', 20)->default('auto');
            $table->string('default_meta_title_template')->nullable();
            $table->text('default_meta_description_template')->nullable();
            $table->boolean('default_robots_index')->default(true);
            $table->boolean('default_robots_follow')->default(true);
            $table->boolean('default_robots_noarchive')->default(false);
            $table->boolean('default_robots_nosnippet')->default(false);
            $table->smallInteger('default_robots_max_snippet')->nullable();
            $table->string('default_robots_max_image_preview', 20)->default('large');
            $table->smallInteger('default_robots_max_video_preview')->nullable();

            // Open Graph defaults
            $table->string('og_site_name')->nullable();
            $table->string('og_type_default', 50)->default('article');
            $table->string('og_locale', 10)->nullable();
            $table->string('og_title_template')->nullable();
            $table->text('og_description_template')->nullable();

            // Twitter defaults
            $table->string('twitter_card_default', 30)->default('summary_large_image');
            $table->string('twitter_site')->nullable();
            $table->string('twitter_creator')->nullable();
            $table->string('twitter_title_template')->nullable();
            $table->text('twitter_description_template')->nullable();

            // Schema / JSON-LD defaults
            $table->boolean('schema_enabled')->default(true);
            $table->string('schema_type_default', 30)->default('BlogPosting');
            $table->string('schema_publisher_name')->nullable();
            $table->json('schema_publisher_logo')->nullable();
            $table->string('schema_language', 10)->nullable();
            $table->json('schema_custom_json')->nullable();

            $table->timestamps();
        });

        DB::table('blog_settings')->insert(['created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_settings');
    }
};
