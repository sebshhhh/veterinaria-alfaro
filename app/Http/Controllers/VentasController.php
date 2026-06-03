<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Producto;
use App\Models\Tratamiento;
use App\Models\Venta;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VentasController extends Controller
{
    public function __construct(private readonly SalesService $salesService)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');
        $prefillTratamientoId = (int) $request->input('tratamiento_id');

        $query = Venta::with([
            'cliente',
            'mascota',
            'historiaClinica',
            'detalles.producto',
            'detalles.tratamiento.historiaClinica.mascota',
        ]);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('cliente', function ($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('dni', 'like', '%' . $search . '%');
                })->orWhereHas('mascota', function ($mascotaQuery) use ($search) {
                    $mascotaQuery->where('nombre', 'like', '%' . $search . '%');
                })->orWhere('metodo_pago', 'like', '%' . $search . '%');
            });
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        if (!empty($fecha)) {
            $query->whereDate('fecha', $fecha);
        }

        $ventas = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(10)->withQueryString();

        $stats = [
            'total' => Venta::count(),
            'pagadas' => Venta::where('estado', 'pagado')->count(),
            'pendientes' => Venta::where('estado', 'pendiente')->count(),
            'hoy' => Venta::whereDate('fecha', now()->toDateString())->count(),
            'ingresos' => (float) Venta::where('estado', 'pagado')->sum('total'),
        ];

        $clientes = Clientes::orderBy('nombre')->get(['id', 'nombre', 'dni']);
        $mascotas = Mascotas::with('cliente:id,nombre')->orderBy('nombre')->get(['id', 'cliente_id', 'nombre', 'tipo_animal']);
        $historias = HistoriaClinica::with('mascota.cliente:id,nombre')->orderByDesc('fecha')->get(['id', 'mascota_id', 'fecha', 'diagnostico']);
        $productos = Producto::orderByDesc('es_servicio')->orderBy('nombre')->get(['id', 'nombre', 'precio', 'stock', 'es_servicio', 'categoria']);
        $tratamientos = Tratamiento::with('historiaClinica.mascota.cliente:id,nombre')
            ->orderByDesc('fecha_inicio')
            ->get(['id', 'historia_clinica_id', 'descripcion', 'costo', 'fecha_inicio', 'fecha_fin']);

        $prefillPayload = $this->buildPrefillPayload($prefillTratamientoId, $tratamientos);
        $shouldOpenCreate = $request->boolean('open_create') && ($prefillPayload['cliente_id'] || $prefillTratamientoId);

        return view('ventas.index', compact(
            'ventas',
            'stats',
            'clientes',
            'mascotas',
            'historias',
            'productos',
            'tratamientos',
            'prefillPayload',
            'shouldOpenCreate'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateVenta($request);
        $this->salesService->create($this->normalizePayload($validated));

        return redirect()->route('ventas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cobro registrado correctamente y stock sincronizado.',
        ]);
    }

    public function update(Request $request, Venta $venta)
    {
        $validated = $this->validateVenta($request, $venta);
        $this->salesService->update($venta, $this->normalizePayload($validated));

        return redirect()->route('ventas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cobro actualizado correctamente.',
        ]);
    }

    public function destroy(Venta $venta)
    {
        $this->salesService->delete($venta);

        return redirect()->route('ventas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cobro eliminado correctamente y stock restaurado si correspondía.',
        ]);
    }

    private function validateVenta(Request $request, ?Venta $venta = null): array
    {
        $input = $this->resolveSaleContext($request->all());

        $validator = Validator::make($input, [
            'cliente_id' => 'nullable|exists:clientes,id',
            'mascota_id' => 'nullable|exists:mascotas,id',
            'historia_clinica_id' => 'nullable|exists:historias_clinicas,id',
            'metodo_pago' => 'required|in:efectivo,yape,tarjeta,transferencia',
            'estado' => 'required|in:pagado,pendiente,anulado',
            'fecha' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.tipo' => 'required|in:producto,tratamiento',
            'items.*.producto_id' => 'nullable|exists:productos,id',
            'items.*.tratamiento_id' => 'nullable|exists:tratamientos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio' => 'nullable|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($input) {
            $clienteId = (int) ($input['cliente_id'] ?? 0);
            $mascotaId = (int) ($input['mascota_id'] ?? 0);
            $historiaId = (int) ($input['historia_clinica_id'] ?? 0);
            $items = collect($input['items'] ?? []);

            if ($mascotaId) {
                $mascota = Mascotas::find($mascotaId);

                if ($mascota && (int) $mascota->cliente_id !== $clienteId) {
                    $validator->errors()->add('mascota_id', 'La mascota seleccionada no pertenece al cliente elegido.');
                }
            }

            if ($historiaId) {
                $historia = HistoriaClinica::with('mascota')->find($historiaId);

                if ($historia) {
                    if ($mascotaId && (int) $historia->mascota_id !== $mascotaId) {
                        $validator->errors()->add('historia_clinica_id', 'La historia seleccionada no corresponde a la mascota elegida.');
                    }

                    if ((int) optional($historia->mascota)->cliente_id !== $clienteId) {
                        $validator->errors()->add('historia_clinica_id', 'La historia seleccionada no pertenece al cliente elegido.');
                    }
                }
            }

            if ($items->isEmpty()) {
                $validator->errors()->add('items', 'Agrega al menos un producto, servicio o tratamiento al cobro.');
            }

            $productIds = collect();
            $treatmentIds = collect();

            foreach ($items as $index => $item) {
                $type = $item['tipo'] ?? null;
                $precio = isset($item['precio']) ? (float) $item['precio'] : null;

                if ($type !== 'producto' && $type !== 'tratamiento') {
                    $validator->errors()->add("items.$index.tipo", 'Selecciona un tipo válido en cada fila.');
                    continue;
                }

                if ($type === 'producto' && blank($item['producto_id'] ?? null)) {
                    $validator->errors()->add("items.$index.producto_id", 'Selecciona el producto de la fila.');
                }

                if ($type === 'tratamiento' && blank($item['tratamiento_id'] ?? null)) {
                    $validator->errors()->add("items.$index.tratamiento_id", 'Selecciona el tratamiento de la fila.');
                }

                if ($precio !== null && $precio < 0) {
                    $validator->errors()->add("items.$index.precio", 'El precio no puede ser negativo.');
                }

                if ($type === 'producto' && filled($item['producto_id'] ?? null)) {
                    $productIds->push((int) $item['producto_id']);
                }

                if ($type === 'tratamiento' && filled($item['tratamiento_id'] ?? null)) {
                    $treatmentIds->push((int) $item['tratamiento_id']);
                }

                if ($type === 'tratamiento' && filled($item['tratamiento_id'] ?? null)) {
                    $tratamiento = Tratamiento::with('historiaClinica.mascota')->find($item['tratamiento_id']);

                    if ($tratamiento) {
                        $tratamientoClienteId = (int) optional(optional($tratamiento->historiaClinica)->mascota)->cliente_id;

                        if (!$clienteId || $tratamientoClienteId !== $clienteId) {
                            $validator->errors()->add("items.$index.tratamiento_id", 'El tratamiento seleccionado no pertenece al cliente elegido.');
                        }
                    }
                }
            }

            if ($productIds->count() !== $productIds->unique()->count()) {
                $validator->errors()->add('items', 'No repitas el mismo producto dentro del cobro.');
            }

            if ($treatmentIds->count() !== $treatmentIds->unique()->count()) {
                $validator->errors()->add('items', 'No repitas el mismo tratamiento dentro del cobro.');
            }
        });

        $validated = $validator->validateWithBag('ventaStore');
        $validated['items'] = collect($validated['items'])
            ->filter(function ($item) {
                if (($item['tipo'] ?? null) === 'producto') {
                    return filled($item['producto_id'] ?? null);
                }

                return filled($item['tratamiento_id'] ?? null);
            })
            ->map(fn ($item) => [
                'tipo' => $item['tipo'],
                'producto_id' => $item['producto_id'] ?? null,
                'tratamiento_id' => $item['tratamiento_id'] ?? null,
                'cantidad' => (int) $item['cantidad'],
                'precio' => $item['precio'] ?? null,
            ])
            ->values()
            ->all();

        if (empty($validated['items'])) {
            throw tap(ValidationException::withMessages([
                'items' => 'Agrega al menos un item válido para registrar el cobro.',
            ]), function ($exception) {
                $exception->errorBag = 'ventaStore';
            });
        }

        return $validated;
    }

    private function normalizePayload(array $validated): array
    {
        return [
            'cliente_id' => $validated['cliente_id'] ?? null,
            'mascota_id' => $validated['mascota_id'] ?? null,
            'historia_clinica_id' => $validated['historia_clinica_id'] ?? null,
            'metodo_pago' => $validated['metodo_pago'],
            'estado' => $validated['estado'],
            'fecha' => $validated['fecha'],
            'items' => $validated['items'],
        ];
    }

    private function buildPrefillPayload(int $prefillTratamientoId, $tratamientos): array
    {
        $prefill = [
            'cliente_id' => null,
            'mascota_id' => null,
            'historia_clinica_id' => null,
            'items' => [],
        ];

        if (!$prefillTratamientoId) {
            return $prefill;
        }

        $tratamiento = $tratamientos->firstWhere('id', $prefillTratamientoId);

        if (!$tratamiento) {
            return $prefill;
        }

        $historia = $tratamiento->historiaClinica;
        $mascota = optional($historia)->mascota;
        $cliente = optional($mascota)->cliente;

        $prefill['cliente_id'] = $cliente?->id;
        $prefill['mascota_id'] = $mascota?->id;
        $prefill['historia_clinica_id'] = $historia?->id;
        $prefill['items'][] = [
            'tipo' => 'tratamiento',
            'tratamiento_id' => $tratamiento->id,
            'cantidad' => 1,
            'precio' => (float) $tratamiento->costo,
        ];

        $tratamiento->loadMissing('productos');

        foreach ($tratamiento->productos as $producto) {
            $prefill['items'][] = [
                'tipo' => 'producto',
                'producto_id' => $producto->id,
                'cantidad' => (int) ($producto->pivot->cantidad ?? 1),
                'precio' => (float) $producto->precio,
            ];
        }

        return $prefill;
    }

    private function resolveSaleContext(array $input): array
    {
        $clienteId = filled($input['cliente_id'] ?? null) ? (int) $input['cliente_id'] : null;
        $mascotaId = filled($input['mascota_id'] ?? null) ? (int) $input['mascota_id'] : null;
        $historiaId = filled($input['historia_clinica_id'] ?? null) ? (int) $input['historia_clinica_id'] : null;

        if ($historiaId) {
            $historia = HistoriaClinica::with('mascota')->find($historiaId);

            if ($historia) {
                $mascotaId = $mascotaId ?: $historia->mascota_id;
                $clienteId = $clienteId ?: optional($historia->mascota)->cliente_id;
            }
        }

        if ($mascotaId && !$clienteId) {
            $clienteId = Mascotas::whereKey($mascotaId)->value('cliente_id');
        }

        foreach (($input['items'] ?? []) as $item) {
            if (($item['tipo'] ?? null) !== 'tratamiento' || blank($item['tratamiento_id'] ?? null)) {
                continue;
            }

            $tratamiento = Tratamiento::with('historiaClinica.mascota')->find($item['tratamiento_id']);

            if (!$tratamiento || !$tratamiento->historiaClinica) {
                continue;
            }

            $historiaId = $historiaId ?: $tratamiento->historia_clinica_id;
            $mascotaId = $mascotaId ?: $tratamiento->historiaClinica->mascota_id;
            $clienteId = $clienteId ?: optional($tratamiento->historiaClinica->mascota)->cliente_id;
        }

        $input['cliente_id'] = $clienteId;
        $input['mascota_id'] = $mascotaId;
        $input['historia_clinica_id'] = $historiaId;

        return $input;
    }
}
