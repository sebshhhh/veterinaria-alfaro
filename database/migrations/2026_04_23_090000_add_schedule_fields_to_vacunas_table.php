<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            $table->string('estado_aplicacion')
                ->default('aplicada')
                ->after('nombre');
            $table->date('fecha_programada')
                ->nullable()
                ->after('estado_aplicacion');
            $table->date('fecha_aplicacion')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            $table->date('fecha_aplicacion')
                ->nullable(false)
                ->change();
            $table->dropColumn(['estado_aplicacion', 'fecha_programada']);
        });
    }
};
