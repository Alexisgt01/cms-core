<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('cms_media_folders')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->constrained('cms_media_folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
        });

        Schema::dropIfExists('cms_media_folders');
    }
};
