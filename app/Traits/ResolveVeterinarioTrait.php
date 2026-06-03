<?php

namespace App\Traits;

use App\Models\Veterinarios;

trait ResolveVeterinarioTrait
{
    /**
     * Resuelve el ID del veterinario responsable.
     *
     * Prioridad:
     *   1. El ID recibido si existe en la tabla.
     *   2. El primer veterinario registrado en el sistema.
     *   3. Crea un registro provisional a partir del usuario autenticado.
     */
    private function resolveVeterinarioId($requestedId): int
    {
        if (!empty($requestedId) && Veterinarios::whereKey($requestedId)->exists()) {
            return (int) $requestedId;
        }

        $veterinario = Veterinarios::query()->orderBy('id')->first();

        if ($veterinario) {
            return $veterinario->id;
        }

        $user = auth()->user();

        $veterinario = Veterinarios::firstOrCreate(
            ['user_id' => $user->id],
            [
                'licencia' => $user->dni ?: 'PENDIENTE-' . $user->id,
                'telefono' => '',
            ]
        );

        return $veterinario->id;
    }
}
