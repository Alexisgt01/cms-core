<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name')->after('id')->default('');
            $table->string('last_name')->after('first_name')->default('');
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['first_name', 'last_name']);
            $table->string('name')->after('id');
        });
    }
};
