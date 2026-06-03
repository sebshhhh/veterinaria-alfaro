<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('seguimientos', 'cita_id')) {
            Schema::table('seguimientos', function (Blueprint $table) {
                $table->foreignId('cita_id')
                    ->nullable()
                    ->after('veterinario_id')
                    ->constrained('citas')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('seguimientos', 'hora_proximo_control')) {
            Schema::table('seguimientos', function (Blueprint $table) {
                $table->time('hora_proximo_control')->nullable()->after('fecha_proximo_control');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('seguimientos', 'cita_id')) {
            Schema::table('seguimientos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('cita_id');
            });
        }

        if (Schema::hasColumn('seguimientos', 'hora_proximo_control')) {
            Schema::table('seguimientos', function (Blueprint $table) {
                $table->dropColumn('hora_proximo_control');
            });
        }
    }
};
