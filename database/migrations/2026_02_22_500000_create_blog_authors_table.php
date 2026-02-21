<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_authors', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();

            // Bio / Social
            $table->longText('bio')->nullable();
            $table->string('website_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('instagram_url')->nullable();

            // Images
            $table->json('avatar')->nullable();

            // SEO
            $table->boolean('indexing')->default(true);
            $table->string('canonical_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->json('og_image')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->json('twitter_image')->nullable();
            $table->string('schema_type', 50)->nullable();
            $table->json('schema_json')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_authors');
    }
};
