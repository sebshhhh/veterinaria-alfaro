<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Producto;
use App\Models\Tratamiento;
use App\Models\Veterinarios;
use App\Services\ClinicalAttentionService;
use App\Traits\ResolveVeterinarioTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TratamientosController extends Controller
{
    use ResolveVeterinarioTrait;

    public function __construct(private readonly ClinicalAttentionService $clinicalAttentionService)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');
        $requestedMascotaId = (int) $request->input('mascota_id');
        $requestedHistoriaId = (int) $request->input('historia_clinica_id');
        $today = now()->toDateString();
        $upcomingLimit = now()->addDays(3)->toDateString();

        $query = Tratamiento::with(['historiaClinica.mascota.cliente', 'veterinario.user', 'productos']);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('descripcion', 'like', '%' . $search . '%')
                    ->orWhereHas('historiaClinica', function ($historiaQuery) use ($search) {
                        $historiaQuery->where('diagnostico', 'like', '%' . $search . '%')
                            ->orWhere('observaciones', 'like', '%' . $search . '%')
                            ->orWhereHas('mascota', function ($mascotaQuery) use ($search) {
                                $mascotaQuery->where('nombre', 'like', '%' . $search . '%')
                                    ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                                        $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                                            ->orWhere('dni', 'like', '%' . $search . '%');
                                    });
                            });
                    })
                    ->orWhereHas('veterinario.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($estado === 'activos') {
            $this->applyActiveScope($query, $today);
        }

        if ($estado === 'por_vencer') {
            $this->applyActiveScope($query, $today)
                ->whereNotNull('fecha_fin')
                ->whereDate('fecha_fin', '>=', $today)
                ->whereDate('fecha_fin', '<=', $upcomingLimit);
        }

        if ($estado === 'programados') {
            $query->whereDate('fecha_inicio', '>', $today);
        }

        if ($estado === 'finalizados') {
            $query->whereNotNull('fecha_fin')
                ->whereDate('fecha_fin', '<', $today);
        }

        if (!empty($fecha)) {
            $query->whereDate('fecha_inicio', $fecha);
        }

        if ($requestedHistoriaId) {
            $query->where('historia_clinica_id', $requestedHistoriaId);
        }

        if ($requestedMascotaId) {
            $query->whereHas('historiaClinica', function ($historiaQuery) use ($requestedMascotaId) {
                $historiaQuery->where('mascota_id', $requestedMascotaId);
            });
        }

        $tratamientos = $query
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $stats = [
            'total' => Tratamiento::count(),
            'activos' => $this->applyActiveScope(Tratamiento::query(), $today)->count(),
            'por_vencer' => $this->applyActiveScope(Tratamiento::query(), $today)
                ->whereNotNull('fecha_fin')
                ->whereDate('fecha_fin', '>=', $today)
                ->whereDate('fecha_fin', '<=', $upcomingLimit)
                ->count(),
            'programados' => Tratamiento::whereDate('fecha_inicio', '>', $today)->count(),
            'finalizados' => Tratamiento::whereNotNull('fecha_fin')->whereDate('fecha_fin', '<', $today)->count(),
            'mascotas' => DB::table('tratamientos')
                ->join('historias_clinicas', 'tratamientos.historia_clinica_id', '=', 'historias_clinicas.id')
                ->distinct()
                ->count('historias_clinicas.mascota_id'),
        ];

        $historiaCatalogo = HistoriaClinica::with('mascota.cliente:id,nombre')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get(['id', 'mascota_id', 'fecha', 'diagnostico', 'observaciones']);

        $veterinarios = Veterinarios::with('user:id,name')
            ->orderBy('id')
            ->get();

        $productos = Producto::query()
            ->orderByDesc('es_servicio')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio', 'stock', 'es_servicio']);

        $prefillHistoriaId = $historiaCatalogo->contains('id', $requestedHistoriaId)
            ? $requestedHistoriaId
            : null;

        $selectedHistoria = $prefillHistoriaId
            ? $historiaCatalogo->firstWhere('id', $prefillHistoriaId)
            : null;

        $selectedMascota = $selectedHistoria?->mascota;

        if (!$selectedMascota && $requestedMascotaId) {
            $selectedMascota = Mascotas::with('cliente:id,nombre')
                ->find($requestedMascotaId, ['id', 'cliente_id', 'nombre', 'tipo_animal', 'foto']);
        }

        $shouldOpenCreate = $request->boolean('open_create') && $prefillHistoriaId;

        return view('tratamientos.index', compact(
            'tratamientos',
            'stats',
            'historiaCatalogo',
            'veterinarios',
            'prefillHistoriaId',
            'selectedHistoria',
            'selectedMascota',
            'shouldOpenCreate',
            'productos'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTratamiento($request);
        $validated['veterinario_id'] = $this->resolveVeterinarioId($request->input('veterinario_id'));
        $productoPayload = $validated['productos'] ?? [];

        unset($validated['productos']);

        $tratamiento = DB::transaction(function () use ($validated, $productoPayload) {
            $tratamiento = Tratamiento::create($this->preparePersistedData($validated));
            $this->syncProductos($tratamiento, $productoPayload);
            $this->clinicalAttentionService->syncTherapeuticFollowUpFromTreatment($tratamiento);

            return $tratamiento;
        });

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => $tratamiento->proximo_control
                ? 'Tratamiento registrado y seguimiento de control sincronizado con la agenda.'
                : 'Tratamiento registrado correctamente.',
        ]);
    }

    public function update(Request $request, Tratamiento $tratamiento)
    {
        $validated = $this->validateTratamiento($request, $tratamiento);
        $validated['veterinario_id'] = $this->resolveVeterinarioId($request->input('veterinario_id') ?: $tratamiento->veterinario_id);
        $productoPayload = $validated['productos'] ?? [];

        unset($validated['productos']);

        DB::transaction(function () use ($tratamiento, $validated, $productoPayload) {
            $tratamiento->update($this->preparePersistedData($validated));
            $this->syncProductos($tratamiento, $productoPayload);
            $this->clinicalAttentionService->syncTherapeuticFollowUpFromTreatment($tratamiento->refresh());
        });

        return redirect()->route('tratamientos.index')->with('toast', [
            'type' => 'success',
            'message' => $validated['proximo_control']
                ? 'Tratamiento actualizado y seguimiento de control sincronizado.'
                : 'Tratamiento actualizado correctamente.',
        ]);
    }

    public function destroy(Tratamiento $tratamiento)
    {
        if ($tratamiento->detalleVentas()->exists()) {
            return redirect()->route('tratamientos.index')->with('toast', [
                'type' => 'error',
                'message' => 'No puedes eliminar este tratamiento porque ya esta vinculado a ventas.',
            ]);
        }

        DB::transaction(function () use ($tratamiento) {
            $historia = $tratamiento->historiaClinica;
            $veterinarioId = $tratamiento->veterinario_id;

            $tratamiento->productos()->detach();
            $tratamiento->delete();

            if ($historia) {
                $this->clinicalAttentionService->resyncTherapeuticFollowUpForHistory($historia, $veterinarioId);
            }
        });

        return redirect()->route('tratamientos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Tratamiento eliminado correctamente.',
        ]);
    }

    private function validateTratamiento(Request $request, ?Tratamiento $tratamiento = null): array
    {
        $validator = Validator::make($request->all(), [
            'historia_clinica_id' => 'required|exists:historias_clinicas,id',
            'veterinario_id' => 'nullable|exists:veterinarios,id',
            'descripcion' => 'required|string',
            'costo' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'proximo_control' => 'nullable|date|after_or_equal:fecha_inicio',
            'productos' => 'nullable|array',
            'productos.*.producto_id' => 'nullable|exists:productos,id',
            'productos.*.cantidad' => 'nullable|integer|min:1',
        ]);

        $validator->after(function ($validator) use ($request, $tratamiento) {
            $descripcion = trim((string) $request->input('descripcion'));
            $productos = collect($request->input('productos', []))
                ->filter(fn ($item) => filled($item['producto_id'] ?? null) || filled($item['cantidad'] ?? null));

            if ($descripcion === '') {
                $validator->errors()->add('descripcion', 'Describe el tratamiento para poder guardarlo.');
            }

            if ($request->filled('historia_clinica_id') && $request->filled('fecha_inicio')) {
                $historia = HistoriaClinica::find($request->input('historia_clinica_id'));

                if ($historia && $historia->fecha && $request->date('fecha_inicio')?->lt($historia->fecha)) {
                    $validator->errors()->add('fecha_inicio', 'La fecha de inicio no puede ser anterior a la fecha de la atención clínica.');
                }
            }

            if ($descripcion !== '' && $request->filled('historia_clinica_id') && $request->filled('fecha_inicio')) {
                $duplicateQuery = Tratamiento::query()
                    ->where('historia_clinica_id', $request->input('historia_clinica_id'))
                    ->where('descripcion', $descripcion)
                    ->whereDate('fecha_inicio', $request->input('fecha_inicio'));

                if ($tratamiento) {
                    $duplicateQuery->whereKeyNot($tratamiento->id);
                }

                if ($duplicateQuery->exists()) {
                    $validator->errors()->add('fecha_inicio', 'Ya existe un tratamiento igual para esta atención clínica en esa fecha.');
                }
            }

            $productoIds = $productos->pluck('producto_id')->filter()->map(fn ($id) => (int) $id);
            if ($productoIds->count() !== $productoIds->unique()->count()) {
                $validator->errors()->add('productos', 'No repitas el mismo producto dentro del tratamiento.');
            }

            foreach ($productos as $index => $producto) {
                if (blank($producto['producto_id'] ?? null) || blank($producto['cantidad'] ?? null)) {
                    $validator->errors()->add("productos.$index.producto_id", 'Completa producto y cantidad en cada insumo agregado.');
                }
            }
        });

        $validated = $validator->validateWithBag('tratamientoStore');
        $validated['descripcion'] = trim((string) $validated['descripcion']);
        $validated['costo'] = $validated['costo'] ?? 0;
        $validated['proximo_control'] = $validated['proximo_control'] ?? ($validated['fecha_fin'] ?? null);
        $validated['productos'] = collect($validated['productos'] ?? [])
            ->filter(fn ($item) => filled($item['producto_id'] ?? null) && filled($item['cantidad'] ?? null))
            ->map(fn ($item) => [
                'producto_id' => (int) $item['producto_id'],
                'cantidad' => (int) $item['cantidad'],
            ])
            ->values()
            ->all();

        return $validated;
    }

    // resolveVeterinarioId() - movido a App\Traits\ResolveVeterinarioTrait

    private function applyActiveScope($query, string $referenceDate)
    {
        return $query->whereDate('fecha_inicio', '<=', $referenceDate)
            ->where(function ($innerQuery) use ($referenceDate) {
                $innerQuery->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $referenceDate);
            });
    }

    private function preparePersistedData(array $validated): array
    {
        return [
            'historia_clinica_id' => $validated['historia_clinica_id'],
            'veterinario_id' => $validated['veterinario_id'],
            'descripcion' => $validated['descripcion'],
            'costo' => $validated['costo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'] ?? null,
            'proximo_control' => $validated['proximo_control'] ?? null,
        ];
    }

    private function syncProductos(Tratamiento $tratamiento, array $productos): void
    {
        $syncPayload = collect($productos)
            ->mapWithKeys(fn ($producto) => [
                (int) $producto['producto_id'] => ['cantidad' => (int) $producto['cantidad']],
            ])
            ->all();

        $tratamiento->productos()->sync($syncPayload);
    }
}

