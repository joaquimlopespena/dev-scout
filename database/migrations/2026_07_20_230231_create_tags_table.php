<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['organization_id', 'name']);
        });
        Schema::create('developer_tag', function (Blueprint $table) {
            $table->foreignId('developer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['developer_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_tag');
        Schema::dropIfExists('tags');
    }
};
