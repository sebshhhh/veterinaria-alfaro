<?php

use App\Models\ConfiguracionSistema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('configuraciones_sistema')) {
            Schema::create('configuraciones_sistema', function (Blueprint $table) {
                $table->id();
                $table->string('clave')->unique();
                $table->text('valor')->nullable();
                $table->string('grupo')->default('general');
                $table->string('tipo')->default('text');
                $table->string('etiqueta');
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        ConfiguracionSistema::ensureDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_sistema');
    }
};