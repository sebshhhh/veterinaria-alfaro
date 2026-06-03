<?php

namespace App\Http\Controllers;

use App\Models\Citas;
use App\Models\Clientes;
use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Producto;
use App\Models\Vacuna;
use App\Models\Veterinarios;
use App\Services\AttentionFlowService;
use App\Services\ClinicalAttentionService;
use App\Traits\ResolveVeterinarioTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CitasController extends Controller
{
    use ResolveVeterinarioTrait;

    public function __construct(
        private readonly ClinicalAttentionService $clinicalAttentionService,
        private readonly AttentionFlowService $attentionFlowService
    )
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');
        $requestedMascotaId = (int) $request->input('mascota_id');
        $accion = $request->input('accion');

        $query = Citas::with([
            'mascota.cliente',
            'mascota.vacunas',
            'veterinario.user',
            'historiaClinica.tratamientos',
            'historiaClinica.recetas',
            'historiaClinica.seguimientos',
            'historiaClinica.servicioProducto',
            'seguimientos',
        ]);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('mascota', function ($mascotaQuery) use ($search) {
                    $mascotaQuery->where('nombre', 'like', '%' . $search . '%')
                        ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                            $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                                ->orWhere('dni', 'like', '%' . $search . '%');
                        });
                })->orWhereHas('veterinario.user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        if (!empty($fecha)) {
            $query->whereDate('fecha', $fecha);
        }

        if ($requestedMascotaId) {
            $query->where('mascota_id', $requestedMascotaId);
        }

        $citas = $query
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(6)
            ->withQueryString();

        $ultimoServicioPorMascota = HistoriaClinica::with('servicioProducto:id,nombre,precio')
            ->whereIn('mascota_id', $citas->getCollection()->pluck('mascota_id')->filter()->unique())
            ->whereNotNull('servicio_producto_id')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->unique('mascota_id')
            ->mapWithKeys(fn ($historia) => [
                $historia->mascota_id => [
                    'id' => $historia->servicioProducto?->id,
                    'nombre' => $historia->servicioProducto?->nombre,
                    'precio' => $historia->precio_servicio ?? $historia->servicioProducto?->precio,
                ],
            ]);

        $stats = [
            'total' => Citas::count(),
            'pendientes' => Citas::where('estado', 'pendiente')->count(),
            'hoy' => Citas::whereDate('fecha', now()->toDateString())->count(),
            'mascotas' => Citas::distinct()->count('mascota_id'),
            'completadas' => Citas::where('estado', 'completada')->count(),
            'canceladas' => Citas::where('estado', 'cancelada')->count(),
        ];

        $citaMascotas = Mascotas::with('cliente:id,nombre,dni,telefono')
            ->orderBy('nombre')
            ->get(['id', 'cliente_id', 'nombre', 'tipo_animal', 'raza', 'color', 'foto']);

        $citaMascotasRecientes = Citas::with('mascota.cliente:id,nombre,dni,telefono')
            ->whereNotNull('mascota_id')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->limit(18)
            ->get()
            ->pluck('mascota')
            ->filter()
            ->unique('id')
            ->take(6)
            ->values();

        if ($citaMascotasRecientes->isEmpty()) {
            $citaMascotasRecientes = $citaMascotas
                ->sortByDesc('id')
                ->take(6)
                ->values();
        }

        $prefillMascotaId = $citaMascotas->contains('id', $requestedMascotaId)
            ? $requestedMascotaId
            : null;

        $selectedMascota = $prefillMascotaId
            ? $citaMascotas->firstWhere('id', $prefillMascotaId)
            : null;

        $shouldOpenCreate = $request->boolean('open_create');

        $veterinarios = Veterinarios::with('user:id,name')
            ->orderBy('id')
            ->get();

        $clientes = Clientes::query()
            ->orderBy('nombre')
            ->get(['id', 'dni', 'nombre']);

        $vacunaCatalogo = Vacuna::query()
            ->whereNotNull('nombre')
            ->where('nombre', '!=', '')
            ->select('nombre')
            ->distinct()
            ->orderBy('nombre')
            ->pluck('nombre');

        $serviciosCatalogo = Producto::query()
            ->where('es_servicio', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio']);

        return view('citas.index', compact(
            'citas',
            'stats',
            'citaMascotas',
            'citaMascotasRecientes',
            'veterinarios',
            'clientes',
            'vacunaCatalogo',
            'serviciosCatalogo',
            'ultimoServicioPorMascota',
            'prefillMascotaId',
            'selectedMascota',
            'shouldOpenCreate',
            'accion'
        ));
    }

    public function storeCliente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|digits:8|unique:clientes,dni',
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|digits:9',
            'direccion' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clientes', 'email')],
        ]);

        $validator->after(function ($validator) use ($request) {
            $nombre = trim((string) $request->input('nombre'));
            $direccion = trim((string) $request->input('direccion'));

            if ($nombre === '') {
                $validator->errors()->add('nombre', 'El nombre del cliente es obligatorio.');
            }

            if ($direccion === '') {
                $validator->errors()->add('direccion', 'La direccion del cliente es obligatoria.');
            }
        });

        $validated = $validator->validateWithBag('citaClienteStore');

        $validated['nombre'] = trim((string) $validated['nombre']);
        $validated['telefono'] = trim((string) $validated['telefono']);
        $validated['direccion'] = trim((string) $validated['direccion']);
        $validated['email'] = filled($validated['email'] ?? null) ? trim((string) $validated['email']) : null;

        $cliente = Clientes::create($validated);

        return redirect()->route('citas.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Cliente creado. Ahora puedes registrar su mascota para programar la cita.',
            ])
            ->with('cita_ui', [
                'open_mascota' => true,
                'selected_cliente_id' => $cliente->id,
            ]);
    }

    public function storeMascota(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'nombre' => 'required|string|max:255',
            'tipo_animal' => 'required|string|max:255',
            'raza' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:100',
            'edad' => 'required|numeric|min:0',
            'sexo' => 'required|in:Macho,Hembra',
            'foto' => 'nullable|image|max:2048',
        ], [
            'sexo.required' => 'Debe seleccionar el sexo.',
            'sexo.in' => 'El sexo debe ser Macho o Hembra.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $nombre = trim((string) $request->input('nombre'));
            $raza = trim((string) $request->input('raza'));
            $tipoAnimal = trim((string) $request->input('tipo_animal'));

            if ($raza === '') {
                $validator->errors()->add('raza', 'Debe seleccionar o escribir una raza.');
            }

            if ($nombre !== '' && $request->filled('cliente_id')) {
                $duplicateQuery = Mascotas::query()
                    ->where('cliente_id', $request->input('cliente_id'))
                    ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)]);

                if ($tipoAnimal !== '') {
                    $duplicateQuery->where('tipo_animal', $tipoAnimal);
                }

                if ($duplicateQuery->exists()) {
                    $validator->errors()->add('nombre', 'Ya existe una mascota con ese nombre para el cliente seleccionado.');
                }
            }
        });

        $validated = $validator->validateWithBag('citaMascotaStore');

        $validated['nombre'] = trim((string) $validated['nombre']);
        $validated['tipo_animal'] = trim((string) $validated['tipo_animal']);
        $validated['raza'] = filled($validated['raza'] ?? null) ? trim((string) $validated['raza']) : null;
        $validated['color'] = filled($validated['color'] ?? null) ? trim((string) $validated['color']) : null;

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('mascotas', 'public');
        }

        $mascota = Mascotas::create($validated);

        return redirect()->route('citas.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Mascota creada. Ya puedes continuar con la programacion de la cita.',
            ])
            ->with('cita_ui', [
                'open_main' => true,
                'selected_mascota_id' => $mascota->id,
            ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCita($request);

        $validated['veterinario_id'] = $this->resolveVeterinarioId($request->input('veterinario_id'));

        Citas::create($validated);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Cita registrada correctamente.',
        ]);
    }

    public function update(Request $request, Citas $cita)
    {
        if ($cita->estado === 'completada' || $cita->historiaClinica()->exists()) {
            return redirect()->route('citas.index')->with('toast', [
                'type' => 'error',
                'message' => 'La cita ya fue atendida. Si necesitas cambiar información, hazlo desde el módulo clínico correspondiente.',
            ]);
        }

        $validated = $this->validateCita($request, $cita);

        $validated['veterinario_id'] = $this->resolveVeterinarioId($request->input('veterinario_id'));

        $cita->update($validated);

        return redirect()->route('citas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cita actualizada correctamente.',
        ]);
    }

    public function updateEstado(Request $request, Citas $cita)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,cancelada',
        ]);

        if ($cita->estado === 'completada' || $cita->historiaClinica()->exists()) {
            return redirect()->route('citas.index')->with('toast', [
                'type' => 'error',
                'message' => 'La cita ya fue atendida y su estado no puede modificarse desde este módulo.',
            ]);
        }

        if ($validated['estado'] === 'cancelada' && $cita->historiaClinica()->exists()) {
            return redirect()->route('citas.index')->with('toast', [
                'type' => 'error',
                'message' => 'No puedes cancelar una cita que ya tiene atención clínica registrada.',
            ]);
        }

        $cita->update([
            'estado' => $validated['estado'],
        ]);

        $messages = [
            'pendiente' => 'La cita fue marcada como pendiente.',
            'cancelada' => 'La cita fue marcada como cancelada.',
        ];

        return redirect()->route('citas.index')->with('toast', [
            'type' => 'success',
            'message' => $messages[$validated['estado']] ?? 'Estado actualizado correctamente.',
        ]);
    }

    public function atender(Request $request, Citas $cita)
    {
        if ($cita->estado === 'cancelada') {
            return redirect()->route('citas.index')->with('toast', [
                'type' => 'error',
                'message' => 'No puedes atender una cita cancelada.',
            ]);
        }

        if ($cita->estado === 'completada' && $cita->historiaClinica()->exists()) {
            return redirect()->route('citas.index')->with('toast', [
                'type' => 'error',
                'message' => 'Esta cita ya fue atendida. Revisa el historial del paciente para consultar la atención registrada.',
            ]);
        }

        $horaCita = \Illuminate\Support\Str::of($cita->hora)->substr(0, 5)->toString();
        $fechaHoraCita = \Illuminate\Support\Carbon::parse(optional($cita->fecha)->format('Y-m-d') . ' ' . $horaCita);

        if ($fechaHoraCita->gt(now())) {
            return redirect()->route('citas.index')->with('toast', [
                'type' => 'error',
                'message' => 'Esta cita aún no llega a su fecha y hora de atencion. Puedes editarla o cancelarla desde agenda.',
            ]);
        }

        $validated = $this->attentionFlowService->validate($request, 'attendCita', true);

        if ((int) $validated['cita_id'] !== (int) $cita->id) {
            abort(422, 'La cita enviada no coincide con la atención seleccionada.');
        }

        $veterinarioId = $this->resolveVeterinarioId($cita->veterinario_id ?: $request->input('veterinario_id'));
        $historiaActual = $cita->historiaClinica;
        $tratamientoActual = $historiaActual?->tratamientos()->first();
        $recetaActual = $historiaActual?->recetas()->first();
        $seguimientoActual = $historiaActual?->seguimientos()
            ->where('tipo', 'clinico')
            ->where('origen', 'atencion')
            ->first();

        $historyData = $this->attentionFlowService->buildHistoriaData($validated, 'agenda');

        $this->clinicalAttentionService->register([
            'mascota_id' => $cita->mascota_id,
            'cita_id' => $cita->id,
            'origen_atencion' => 'programada',
            'tipo_atencion' => $validated['tipo_atencion'],
            'fecha' => $validated['historia_fecha'],
            'diagnostico' => $historyData['diagnostico'],
            'observaciones' => $historyData['observaciones'],
            'peso' => $validated['peso'] ?? null,
            'temperatura' => $validated['temperatura'] ?? null,
            'veterinario_id' => $veterinarioId,
            'complete_cita' => true,
            'servicio' => [
                'producto_id' => $validated['servicio_producto_id'] ?? null,
                'precio' => $validated['precio_servicio'] ?? null,
            ],
            'vacuna' => [
                'nombre' => $validated['vacuna_nombre'] ?? null,
                'fecha_aplicacion' => $validated['vacuna_fecha_aplicacion'] ?? null,
                'proxima_dosis' => $validated['vacuna_proxima_dosis'] ?? null,
            ],
            'tratamiento' => [
                'id' => $tratamientoActual?->id,
                'descripcion' => $validated['tratamiento_descripcion'] ?? null,
                'costo' => $validated['tratamiento_costo'] ?? 0,
                'fecha_inicio' => $validated['tratamiento_fecha_inicio'] ?? null,
                'fecha_fin' => $validated['tratamiento_fecha_fin'] ?? null,
            ],
            'receta' => [
                'id' => $recetaActual?->id,
                'medicamentos' => $validated['receta_medicamentos'] ?? null,
                'indicaciones' => $validated['receta_indicaciones'] ?? null,
            ],
            'seguimiento' => [
                'id' => $seguimientoActual?->id,
                'titulo' => $this->attentionFlowService->buildSeguimientoTitulo($validated),
                'motivo' => $validated['seguimiento_motivo'] ?? null,
                'notas' => $validated['seguimiento_notas'] ?? null,
                'evolucion' => null,
                'fecha_inicio' => $validated['historia_fecha'],
                'fecha_proximo_control' => $validated['seguimiento_fecha_proximo_control'] ?? null,
                'hora_proximo_control' => $validated['seguimiento_hora_proximo_control'] ?? null,
                'dias_retorno' => $validated['seguimiento_dias_retorno'] ?? null,
                'estado' => 'activo',
            ],
        ]);

        return redirect()->route('citas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Atención clínica registrada y cita completada correctamente.',
        ]);
    }

    public function destroy(Citas $cita)
    {
        if ($cita->estado === 'completada' || $cita->historiaClinica()->exists()) {
            return redirect()->route('citas.index')->with('toast', [
                'type' => 'error',
                'message' => 'No puedes eliminar una cita que ya fue atendida.',
            ]);
        }

        $cita->delete();

        return redirect()->route('citas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Cita eliminada correctamente.',
        ]);
    }

    // resolveVeterinarioId() - movido a App\Traits\ResolveVeterinarioTrait

    private function validateCita(Request $request, ?Citas $cita = null): array
    {
        $validator = Validator::make($request->all(), [
            'mascota_id' => 'required|exists:mascotas,id',
            'veterinario_id' => 'nullable|exists:veterinarios,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'estado' => 'required|in:pendiente,cancelada',
        ]);

        $validator->after(function ($validator) use ($request, $cita) {
            if (!$request->filled('mascota_id') || !$request->filled('fecha') || !$request->filled('hora')) {
                return;
            }

            $mascotaConflict = Citas::query()
                ->where('mascota_id', $request->input('mascota_id'))
                ->whereDate('fecha', $request->input('fecha'))
                ->where('hora', $request->input('hora'));

            if ($cita) {
                $mascotaConflict->whereKeyNot($cita->id);
            }

            if ($mascotaConflict->exists()) {
                $validator->errors()->add('hora', 'La mascota ya tiene una cita registrada para esa fecha y hora.');
            }

            $veterinarioId = $request->input('veterinario_id');

            if (filled($veterinarioId)) {
                $veterinarioConflict = Citas::query()
                    ->where('veterinario_id', $veterinarioId)
                    ->whereDate('fecha', $request->input('fecha'))
                    ->where('hora', $request->input('hora'));

                if ($cita) {
                    $veterinarioConflict->whereKeyNot($cita->id);
                }

                if ($veterinarioConflict->exists()) {
                    $validator->errors()->add('hora', 'El profesional ya tiene una cita asignada para esa fecha y hora.');
                }
            }
        });

        return $validator->validateWithBag('citaStore');
    }

}

