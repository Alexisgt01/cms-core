<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Contact
            $table->text('opening_hours')->nullable()->after('google_maps_url');

            // Identity / Footer
            $table->string('footer_copyright', 255)->nullable()->after('time_format');
            $table->text('footer_text')->nullable()->after('footer_copyright');
            $table->unsignedSmallInteger('copyright_start_year')->nullable()->after('footer_text');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['opening_hours', 'footer_copyright', 'footer_text', 'copyright_start_year']);
        });
    }
};
