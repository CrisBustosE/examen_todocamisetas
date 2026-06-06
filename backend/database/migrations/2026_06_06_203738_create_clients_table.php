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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial');
            $table->string('rut')->unique();
            $table->string('direccion');
            $table->enum('categoria', ['Regular', 'Preferencial']);
            $table->string('contacto_nombre');
            $table->string('contacto_correo');
            $table->integer('porcentaje_oferta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
