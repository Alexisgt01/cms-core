<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('h1', 255)->nullable()->after('title');
            $table->string('subtitle', 255)->nullable()->after('h1');
            $table->text('seo_excerpt')->nullable()->after('excerpt');
            $table->longText('content_seo_top')->nullable()->after('content');
            $table->longText('content_seo_bottom')->nullable()->after('content_seo_top');
            $table->string('focus_keyword', 255)->nullable()->after('meta_description');
            $table->json('secondary_keywords')->nullable()->after('focus_keyword');
            $table->json('faq_blocks')->nullable()->after('content_seo_bottom');
            $table->boolean('table_of_contents')->default(false)->after('faq_blocks');
            $table->string('og_locale', 10)->nullable()->after('og_description');
            $table->string('og_site_name', 255)->nullable()->after('og_locale');
            $table->unsignedSmallInteger('og_image_width')->nullable()->after('og_image');
            $table->unsignedSmallInteger('og_image_height')->nullable()->after('og_image_width');
            $table->string('twitter_site', 255)->nullable()->after('twitter_description');
            $table->string('twitter_creator', 255)->nullable()->after('twitter_site');
            $table->json('schema_types')->nullable()->after('schema_type');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'h1',
                'subtitle',
                'seo_excerpt',
                'content_seo_top',
                'content_seo_bottom',
                'focus_keyword',
                'secondary_keywords',
                'faq_blocks',
                'table_of_contents',
                'og_locale',
                'og_site_name',
                'og_image_width',
                'og_image_height',
                'twitter_site',
                'twitter_creator',
                'schema_types',
            ]);
        });
    }
};
