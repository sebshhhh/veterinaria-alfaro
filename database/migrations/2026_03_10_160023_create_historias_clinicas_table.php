<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mascota_id')
                  ->constrained('mascotas')
                  ->onDelete('cascade');

            $table->text('diagnostico')->nullable();
            $table->text('observaciones')->nullable(); // Notas extra del veterinario
            $table->date('fecha');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};