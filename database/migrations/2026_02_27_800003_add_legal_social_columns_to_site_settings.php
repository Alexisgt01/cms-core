<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // ── Legal (Mentions légales) ──────────────────────
            $table->string('company_name', 255)->nullable()->after('show_version_in_footer');
            $table->string('legal_form', 100)->nullable()->after('company_name');
            $table->string('share_capital', 100)->nullable()->after('legal_form');
            $table->string('company_address', 255)->nullable()->after('share_capital');
            $table->string('company_postal_code', 20)->nullable()->after('company_address');
            $table->string('company_city', 255)->nullable()->after('company_postal_code');
            $table->string('company_country', 255)->nullable()->default('France')->after('company_city');
            $table->string('siret', 20)->nullable()->after('company_country');
            $table->string('siren', 15)->nullable()->after('siret');
            $table->string('tva_number', 30)->nullable()->after('siren');
            $table->string('rcs', 100)->nullable()->after('tva_number');
            $table->string('ape_code', 10)->nullable()->after('rcs');
            $table->string('director_name', 255)->nullable()->after('ape_code');
            $table->string('director_email', 255)->nullable()->after('director_name');
            $table->string('hosting_provider_name', 255)->nullable()->after('director_email');
            $table->string('hosting_provider_address', 500)->nullable()->after('hosting_provider_name');
            $table->string('hosting_provider_phone', 30)->nullable()->after('hosting_provider_address');
            $table->string('hosting_provider_email', 255)->nullable()->after('hosting_provider_phone');
            $table->string('dpo_name', 255)->nullable()->after('hosting_provider_email');
            $table->string('dpo_email', 255)->nullable()->after('dpo_name');

            // ── Contact (additional) ─────────────────────────
            $table->string('phone', 30)->nullable()->after('dpo_email');
            $table->string('secondary_phone', 30)->nullable()->after('phone');
            $table->string('google_maps_url', 500)->nullable()->after('secondary_phone');

            // ── Social media ─────────────────────────────────
            $table->string('social_facebook', 255)->nullable()->after('google_maps_url');
            $table->string('social_x', 255)->nullable()->after('social_facebook');
            $table->string('social_instagram', 255)->nullable()->after('social_x');
            $table->string('social_linkedin', 255)->nullable()->after('social_instagram');
            $table->string('social_youtube', 255)->nullable()->after('social_linkedin');
            $table->string('social_tiktok', 255)->nullable()->after('social_youtube');
            $table->string('social_pinterest', 255)->nullable()->after('social_tiktok');
            $table->string('social_github', 255)->nullable()->after('social_pinterest');
            $table->string('social_threads', 255)->nullable()->after('social_github');
            $table->string('social_snapchat', 255)->nullable()->after('social_threads');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                // Legal
                'company_name', 'legal_form', 'share_capital',
                'company_address', 'company_postal_code', 'company_city', 'company_country',
                'siret', 'siren', 'tva_number', 'rcs', 'ape_code',
                'director_name', 'director_email',
                'hosting_provider_name', 'hosting_provider_address', 'hosting_provider_phone', 'hosting_provider_email',
                'dpo_name', 'dpo_email',
                // Contact
                'phone', 'secondary_phone', 'google_maps_url',
                // Social
                'social_facebook', 'social_x', 'social_instagram', 'social_linkedin',
                'social_youtube', 'social_tiktok', 'social_pinterest', 'social_github',
                'social_threads', 'social_snapchat',
            ]);
        });
    }
};
