<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('og_image_fallback_width')->nullable()->after('og_description_template');
            $table->unsignedSmallInteger('og_image_fallback_height')->nullable()->after('og_image_fallback_width');
            $table->unsignedSmallInteger('twitter_image_fallback_width')->nullable()->after('twitter_description_template');
            $table->unsignedSmallInteger('twitter_image_fallback_height')->nullable()->after('twitter_image_fallback_width');
            $table->json('schema_same_as')->nullable()->after('schema_language');
            $table->string('schema_organization_url', 255)->nullable()->after('schema_publisher_logo');
        });
    }

    public function down(): void
    {
        Schema::table('blog_settings', function (Blueprint $table) {
            $table->dropColumn([
                'og_image_fallback_width',
                'og_image_fallback_height',
                'twitter_image_fallback_width',
                'twitter_image_fallback_height',
                'schema_same_as',
                'schema_organization_url',
            ]);
        });
    }
};
