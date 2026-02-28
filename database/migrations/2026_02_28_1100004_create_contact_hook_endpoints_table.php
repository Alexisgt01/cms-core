<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_hook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('hook_key')->unique();
            $table->string('name')->nullable();
            $table->string('url');
            $table->string('secret');
            $table->boolean('enabled')->default(true);
            $table->json('events')->nullable();
            $table->integer('timeout')->default(5);
            $table->integer('retries')->default(3);
            $table->json('backoff')->nullable();
            $table->json('headers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_hook_endpoints');
    }
};
