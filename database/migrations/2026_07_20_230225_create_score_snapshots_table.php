<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_id')->constrained()->cascadeOnDelete();
            $table->decimal('total', 5, 2)->index();
            $table->string('algorithm_version', 20);
            $table->json('dimensions');
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->index(['developer_id', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_snapshots');
    }
};
