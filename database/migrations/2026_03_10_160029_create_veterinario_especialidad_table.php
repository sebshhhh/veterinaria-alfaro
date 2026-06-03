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
        Schema::create('veterinario_especialidad', function (Blueprint $table) {

            $table->foreignId('veterinario_id')->constrained('veterinarios');
            $table->foreignId('especialidad_id')->constrained('especialidades');

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinario_especialidad');
    }
};
