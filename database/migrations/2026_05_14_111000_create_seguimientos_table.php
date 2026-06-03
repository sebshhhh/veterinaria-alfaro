<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('historia_clinica_id')->nullable()->constrained('historias_clinicas')->nullOnDelete();
            $table->foreignId('veterinario_id')->nullable()->constrained('veterinarios')->nullOnDelete();
            $table->string('titulo');
            $table->string('estado')->default('activo');
            $table->text('motivo')->nullable();
            $table->text('notas')->nullable();
            $table->text('evolucion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_proximo_control')->nullable();
            $table->unsignedSmallInteger('dias_retorno')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};
