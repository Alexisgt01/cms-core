<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->string('h1', 255)->nullable()->after('name');
            $table->longText('content_seo_top')->nullable()->after('description');
            $table->longText('content_seo_bottom')->nullable()->after('content_seo_top');
            $table->string('focus_keyword', 255)->nullable()->after('meta_description');
            $table->json('secondary_keywords')->nullable()->after('focus_keyword');
            $table->boolean('indexing')->default(true)->after('meta_description');
            $table->string('canonical_url', 255)->nullable()->after('indexing');
            $table->boolean('robots_index')->nullable()->after('canonical_url');
            $table->boolean('robots_follow')->nullable()->after('robots_index');
            $table->boolean('robots_noarchive')->nullable()->after('robots_follow');
            $table->boolean('robots_nosnippet')->nullable()->after('robots_noarchive');
            $table->smallInteger('robots_max_snippet')->nullable()->after('robots_nosnippet');
            $table->string('robots_max_image_preview', 20)->nullable()->after('robots_max_snippet');
            $table->smallInteger('robots_max_video_preview')->nullable()->after('robots_max_image_preview');
            $table->string('og_type', 50)->nullable()->after('og_description');
            $table->string('og_locale', 10)->nullable()->after('og_type');
            $table->string('og_site_name', 255)->nullable()->after('og_locale');
            $table->unsignedSmallInteger('og_image_width')->nullable()->after('og_image');
            $table->unsignedSmallInteger('og_image_height')->nullable()->after('og_image_width');
            $table->string('twitter_card', 30)->nullable()->after('twitter_description');
            $table->string('twitter_site', 255)->nullable()->after('twitter_card');
            $table->string('twitter_creator', 255)->nullable()->after('twitter_site');
            $table->json('schema_types')->nullable()->after('schema_type');
        });
    }

    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropColumn([
                'h1',
                'content_seo_top',
                'content_seo_bottom',
                'focus_keyword',
                'secondary_keywords',
                'indexing',
                'canonical_url',
                'robots_index',
                'robots_follow',
                'robots_noarchive',
                'robots_nosnippet',
                'robots_max_snippet',
                'robots_max_image_preview',
                'robots_max_video_preview',
                'og_type',
                'og_locale',
                'og_site_name',
                'og_image_width',
                'og_image_height',
                'twitter_card',
                'twitter_site',
                'twitter_creator',
                'schema_types',
            ]);
        });
    }
};
