<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_clinica_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('veterinario_id')
                  ->constrained('veterinarios')
                  ->onDelete('cascade');

            $table->text('descripcion'); // Qué se hará
            $table->decimal('costo', 10, 2)->default(0); // costo del tratamiento

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // por si es tratamiento prolongado

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};