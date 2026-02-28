<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('site_name', 255)->nullable();
            $table->string('baseline', 255)->nullable();
            $table->json('logo_light')->nullable();
            $table->json('logo_dark')->nullable();
            $table->json('favicon')->nullable();
            $table->string('timezone', 50)->nullable();
            $table->string('date_format', 50)->nullable()->default('d/m/Y');
            $table->string('time_format', 50)->nullable()->default('H:i');

            // Contact
            $table->json('contact_email_recipients')->nullable();
            $table->string('from_email_name', 255)->nullable();
            $table->string('from_email_address', 255)->nullable();
            $table->string('reply_to_email', 255)->nullable();

            // Restricted access
            $table->boolean('restricted_access_enabled')->default(false);
            $table->string('restricted_access_password', 255)->nullable();
            $table->integer('restricted_access_cookie_ttl')->default(1440);
            $table->text('restricted_access_message')->nullable();
            $table->boolean('restricted_access_admin_bypass')->default(true);

            // SEO Global
            $table->string('default_site_title', 255)->nullable();
            $table->text('default_meta_description')->nullable();
            $table->string('title_template', 255)->nullable()->default('%title% · %site%');
            $table->json('default_og_image')->nullable();
            $table->boolean('default_robots_index')->default(true);
            $table->boolean('default_robots_follow')->default(true);
            $table->string('canonical_base_url', 255)->nullable();

            // Admin
            $table->boolean('show_version_in_footer')->default(false);

            // Application-specific settings
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
