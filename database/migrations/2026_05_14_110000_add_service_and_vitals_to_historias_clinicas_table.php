<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->decimal('peso', 5, 2)->nullable()->after('fecha');
            $table->decimal('temperatura', 4, 1)->nullable()->after('peso');
            $table->foreignId('servicio_producto_id')
                ->nullable()
                ->after('temperatura')
                ->constrained('productos')
                ->nullOnDelete();
            $table->decimal('precio_servicio', 10, 2)->nullable()->after('servicio_producto_id');
        });
    }

    public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('servicio_producto_id');
            $table->dropColumn(['peso', 'temperatura', 'precio_servicio']);
        });
    }
};
