<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_settings', function (Blueprint $table) {
            $table->boolean('sitemap_enabled')->default(true)->after('schema_custom_json');
            $table->string('sitemap_base_url', 255)->nullable()->after('sitemap_enabled');
            $table->unsignedSmallInteger('sitemap_max_urls')->default(5000)->after('sitemap_base_url');
            $table->unsignedTinyInteger('sitemap_crawl_depth')->default(10)->after('sitemap_max_urls');
            $table->unsignedTinyInteger('sitemap_concurrency')->default(10)->after('sitemap_crawl_depth');
            $table->json('sitemap_exclude_patterns')->nullable()->after('sitemap_concurrency');
            $table->string('sitemap_default_change_freq', 20)->default('weekly')->after('sitemap_exclude_patterns');
            $table->string('sitemap_default_priority', 5)->default('0.5')->after('sitemap_default_change_freq');
        });
    }

    public function down(): void
    {
        Schema::table('blog_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sitemap_enabled',
                'sitemap_base_url',
                'sitemap_max_urls',
                'sitemap_crawl_depth',
                'sitemap_concurrency',
                'sitemap_exclude_patterns',
                'sitemap_default_change_freq',
                'sitemap_default_priority',
            ]);
        });
    }
};
