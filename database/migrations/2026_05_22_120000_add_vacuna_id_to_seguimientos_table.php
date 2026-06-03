<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('seguimientos', 'vacuna_id')) {
            Schema::table('seguimientos', function (Blueprint $table) {
                $table->foreignId('vacuna_id')
                    ->nullable()
                    ->after('cita_id')
                    ->constrained('vacunas')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('seguimientos', 'vacuna_id')) {
            Schema::table('seguimientos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vacuna_id');
            });
        }
    }
};
