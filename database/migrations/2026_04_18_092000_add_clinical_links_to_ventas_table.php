<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('mascota_id')
                ->nullable()
                ->after('cliente_id')
                ->constrained('mascotas')
                ->nullOnDelete();
            $table->foreignId('historia_clinica_id')
                ->nullable()
                ->after('mascota_id')
                ->constrained('historias_clinicas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('historia_clinica_id');
            $table->dropConstrainedForeignId('mascota_id');
        });
    }
};
