<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('github_id')->unique();
            $table->string('login')->unique();
            $table->string('name')->nullable()->index();
            $table->string('avatar_url')->nullable();
            $table->string('html_url');
            $table->string('location')->nullable()->index();
            $table->text('bio')->nullable();
            $table->string('company')->nullable();
            $table->string('primary_language')->nullable()->index();
            $table->json('languages')->nullable();
            $table->unsignedInteger('followers')->default(0)->index();
            $table->unsignedInteger('public_repositories')->default(0)->index();
            $table->unsignedInteger('total_stars')->default(0)->index();
            $table->timestamp('github_created_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->string('sync_status', 20)->default('synced')->index();
            $table->text('sync_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};
