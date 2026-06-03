<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->string('tipo', 30)->default('clinico')->after('veterinario_id');
            $table->string('origen', 30)->default('atencion')->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'origen']);
        });
    }
};
