<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tratamientos', function (Blueprint $table) {
            $table->date('proximo_control')->nullable()->after('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('tratamientos', function (Blueprint $table) {
            $table->dropColumn('proximo_control');
        });
    }
};
