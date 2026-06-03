<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo_animal');
            $table->string('raza')->nullable();
            $table->integer('edad');

            // 🔥 NUEVA COLUMNA SEXO
            $table->enum('sexo', ['Macho', 'Hembra']);

            // Foto
            $table->string('foto')->nullable();

            $table->foreignId('cliente_id')->constrained('clientes');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};