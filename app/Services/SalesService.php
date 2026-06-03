<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Tratamiento;
use App\Models\Venta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function create(array $payload): Venta
    {
        return DB::transaction(fn () => $this->persist(null, $payload));
    }

    public function update(Venta $venta, array $payload): Venta
    {
        return DB::transaction(fn () => $this->persist($venta, $payload));
    }

    public function delete(Venta $venta): void
    {
        DB::transaction(function () use ($venta) {
            $venta->load('detalles.producto');

            if ($venta->estado === 'pagado') {
                $this->restoreStock($venta->detalles);
            }

            $venta->detalles()->delete();
            $venta->delete();
        });
    }

    private function persist(?Venta $venta, array $payload): Venta
    {
        if ($venta) {
            $venta->load('detalles.producto');

            if ($venta->estado === 'pagado') {
                $this->restoreStock($venta->detalles);
            }
        }

        $items = $this->materializeItems($payload['items']);

        if (($payload['estado'] ?? 'pagado') === 'pagado') {
            $this->assertStockAvailability($items);
        }

        $venta = tap($venta ?? new Venta())->fill([
            'cliente_id' => $payload['cliente_id'],
            'mascota_id' => $payload['mascota_id'] ?? null,
            'historia_clinica_id' => $payload['historia_clinica_id'] ?? null,
            'metodo_pago' => $payload['metodo_pago'],
            'estado' => $payload['estado'],
            'fecha' => $payload['fecha'],
            'total' => $items->sum('subtotal'),
        ]);

        $venta->save();
        $venta->detalles()->delete();

        foreach ($items as $item) {
            DetalleVenta::create([
                'venta_id' => $venta->id,
                'producto_id' => $item['producto_id'],
                'tratamiento_id' => $item['tratamiento_id'],
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        if ($venta->estado === 'pagado') {
            $this->discountStock($items);
        }

        $venta->update(['total' => $items->sum('subtotal')]);

        return $venta->fresh(['cliente', 'mascota', 'historiaClinica', 'detalles.producto', 'detalles.tratamiento.historiaClinica.mascota']);
    }

    private function materializeItems(array $items): Collection
    {
        return collect($items)->map(function ($item) {
            $type = $item['tipo'];
            $quantity = max(1, (int) ($item['cantidad'] ?? 1));

            if ($type === 'producto') {
                $producto = Producto::query()->findOrFail($item['producto_id']);
                $price = (float) $producto->precio;

                return [
                    'tipo' => 'producto',
                    'producto_id' => $producto->id,
                    'tratamiento_id' => null,
                    'cantidad' => $quantity,
                    'precio' => $price,
                    'subtotal' => round($price * $quantity, 2),
                    'producto_model' => $producto,
                ];
            }

            $tratamiento = Tratamiento::with('historiaClinica.mascota.cliente')->findOrFail($item['tratamiento_id']);
            $price = isset($item['precio']) && $item['precio'] !== ''
                ? (float) $item['precio']
                : (float) $tratamiento->costo;

            return [
                'tipo' => 'tratamiento',
                'producto_id' => null,
                'tratamiento_id' => $tratamiento->id,
                'cantidad' => $quantity,
                'precio' => $price,
                'subtotal' => round($price * $quantity, 2),
                'producto_model' => null,
            ];
        })->values();
    }

    private function assertStockAvailability(Collection $items): void
    {
        $required = $items
            ->filter(fn ($item) => $item['tipo'] === 'producto' && $item['producto_model'] && !$item['producto_model']->es_servicio)
            ->groupBy('producto_id')
            ->map(fn ($group) => $group->sum('cantidad'));

        foreach ($required as $productId => $needed) {
            $producto = Producto::query()->lockForUpdate()->find($productId);

            if (!$producto) {
                throw ValidationException::withMessages([
                    'items' => 'Uno de los productos seleccionados ya no existe en el inventario.',
                ]);
            }

            if ($producto->stock < $needed) {
                throw ValidationException::withMessages([
                    'items' => 'No hay stock suficiente para ' . $producto->nombre . '. Disponible: ' . $producto->stock . '. Solicitado: ' . $needed . '.',
                ]);
            }
        }
    }

    private function discountStock(Collection $items): void
    {
        foreach ($items as $item) {
            if ($item['tipo'] !== 'producto') {
                continue;
            }

            $producto = Producto::query()->lockForUpdate()->find($item['producto_id']);

            if (!$producto || $producto->es_servicio) {
                continue;
            }

            $producto->decrement('stock', $item['cantidad']);
        }
    }

    private function restoreStock(Collection $detalles): void
    {
        foreach ($detalles as $detalle) {
            $producto = $detalle->producto;

            if (!$producto || $producto->es_servicio) {
                continue;
            }

            Producto::query()->lockForUpdate()->whereKey($producto->id)->increment('stock', $detalle->cantidad);
        }
    }
}
