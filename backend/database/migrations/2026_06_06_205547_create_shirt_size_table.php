<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shirt_size', function (Blueprint $table) {
            $table->foreignId('shirt_id')->constrained('shirts')->cascadeOnDelete();
            $table->foreignId('size_id')->constrained('sizes')->cascadeOnDelete();
            $table->primary(['shirt_id', 'size_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shirt_size');
    }
};
