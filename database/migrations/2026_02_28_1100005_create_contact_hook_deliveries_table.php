<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_hook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hook_endpoint_id')->constrained('contact_hook_endpoints')->cascadeOnDelete();
            $table->foreignId('contact_request_id')->constrained('contact_requests')->cascadeOnDelete();
            $table->string('event');
            $table->string('status')->default('pending');
            $table->integer('attempt')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->integer('last_http_code')->nullable();
            $table->text('last_error')->nullable();
            $table->text('request_body')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('next_retry_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_hook_deliveries');
    }
};
