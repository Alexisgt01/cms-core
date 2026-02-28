<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('type');
            $table->string('form_id')->nullable();
            $table->string('state')->default('new');
            $table->json('payload');
            $table->json('meta')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->index('type');
            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
