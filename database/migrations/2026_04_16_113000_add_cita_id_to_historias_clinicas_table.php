<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->foreignId('cita_id')
                ->nullable()
                ->after('mascota_id')
                ->constrained('citas')
                ->nullOnDelete();

            $table->unique('cita_id');
        });
    }

    public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->dropUnique(['cita_id']);
            $table->dropConstrainedForeignId('cita_id');
        });
    }
};
