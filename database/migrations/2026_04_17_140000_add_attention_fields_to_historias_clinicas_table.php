<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->string('origen_atencion')->nullable()->after('cita_id');
            $table->string('tipo_atencion')->nullable()->after('origen_atencion');
        });

        DB::table('historias_clinicas')
            ->whereNotNull('cita_id')
            ->update([
                'origen_atencion' => 'programada',
                'tipo_atencion' => 'consulta',
            ]);

        DB::table('historias_clinicas')
            ->whereNull('cita_id')
            ->update([
                'origen_atencion' => 'manual',
            ]);
    }

    public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->dropColumn(['origen_atencion', 'tipo_atencion']);
        });
    }
};
