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
        Schema::create('shirts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clients')->restrictOnDelete();
            $table->string('titulo');
            $table->string('club');
            $table->string('pais');
            $table->string('tipo');
            $table->string('color');
            $table->integer('precio');
            $table->integer('precio_oferta')->nullable();
            $table->text('detalles')->nullable();
            $table->string('codigo_producto')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shirts');
    }
};
