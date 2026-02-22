<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_settings', function (Blueprint $table) {
            $table->boolean('default_h1_from_title')->default(true)->after('indexing_default');
            $table->json('default_schema_types')->nullable()->after('schema_type_default');
        });
    }

    public function down(): void
    {
        Schema::table('blog_settings', function (Blueprint $table) {
            $table->dropColumn([
                'default_h1_from_title',
                'default_schema_types',
            ]);
        });
    }
};
