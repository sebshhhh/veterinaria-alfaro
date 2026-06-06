<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE ventas DROP FOREIGN KEY ventas_cliente_id_foreign');
        DB::statement('ALTER TABLE ventas MODIFY cliente_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE ventas ADD CONSTRAINT ventas_cliente_id_foreign FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        $fallbackClienteId = DB::table('clientes')->orderBy('id')->value('id');

        if ($fallbackClienteId) {
            DB::table('ventas')->whereNull('cliente_id')->update(['cliente_id' => $fallbackClienteId]);
        }

        DB::statement('ALTER TABLE ventas DROP FOREIGN KEY ventas_cliente_id_foreign');
        DB::statement('ALTER TABLE ventas MODIFY cliente_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE ventas ADD CONSTRAINT ventas_cliente_id_foreign FOREIGN KEY (cliente_id) REFERENCES clientes(id)');
    }
};
