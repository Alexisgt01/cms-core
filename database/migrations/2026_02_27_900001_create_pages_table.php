<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // Core
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('key')->unique()->nullable();
            $table->json('meta')->nullable();

            // State / Publication
            $table->string('state')->default('page_draft')->index();
            $table->timestamp('published_at')->nullable();

            // Hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->unsignedInteger('position')->default(0);

            // Flags
            $table->boolean('is_home')->default(false)->index();

            // SEO — Core
            $table->string('h1', 255)->nullable();
            $table->string('focus_keyword', 255)->nullable();
            $table->json('secondary_keywords')->nullable();
            $table->boolean('indexing')->default(true);
            $table->string('canonical_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // SEO — Robots
            $table->boolean('robots_index')->nullable();
            $table->boolean('robots_follow')->nullable();
            $table->boolean('robots_noarchive')->nullable();
            $table->boolean('robots_nosnippet')->nullable();
            $table->smallInteger('robots_max_snippet')->nullable();
            $table->string('robots_max_image_preview', 20)->nullable();
            $table->smallInteger('robots_max_video_preview')->nullable();

            // SEO — Open Graph
            $table->string('og_type', 50)->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_locale', 10)->nullable();
            $table->string('og_site_name', 255)->nullable();
            $table->json('og_image')->nullable();
            $table->unsignedSmallInteger('og_image_width')->nullable();
            $table->unsignedSmallInteger('og_image_height')->nullable();

            // SEO — Twitter
            $table->string('twitter_card', 30)->nullable();
            $table->string('twitter_site', 255)->nullable();
            $table->string('twitter_creator', 255)->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->json('twitter_image')->nullable();

            // SEO — Schema / JSON-LD
            $table->json('schema_types')->nullable();
            $table->json('schema_json')->nullable();

            // SoftDeletes + timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
