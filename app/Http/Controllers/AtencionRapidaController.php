<?php

namespace App\Http\Controllers;

use App\Models\Citas;
use App\Models\Clientes;
use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Producto;
use App\Models\Seguimiento;
use App\Models\Vacuna;
use App\Models\Veterinarios;
use App\Services\AttentionFlowService;
use App\Services\ClinicalAttentionService;
use App\Traits\ResolveVeterinarioTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AtencionRapidaController extends Controller
{
    use ResolveVeterinarioTrait;

    public function __construct(
        private readonly ClinicalAttentionService $clinicalAttentionService,
        private readonly AttentionFlowService $attentionFlowService
    ) {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $tipo = $request->input('tipo');
        $requestedMascotaId = (int) $request->input('mascota_id');
        $shouldOpenCreate = $request->boolean('open_create');
        $today = now()->toDateString();
        $weekLimit = now()->addDays(7)->toDateString();

        $baseDirectas = HistoriaClinica::query()
            ->where('origen_atencion', 'manual')
            ->whereNull('cita_id');

        $query = (clone $baseDirectas)->with([
            'mascota.cliente',
            'mascota.vacunas',
            'tratamientos',
            'recetas',
            'seguimientos.cita',
            'servicioProducto',
        ]);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('diagnostico', 'like', '%' . $search . '%')
                    ->orWhere('observaciones', 'like', '%' . $search . '%')
                    ->orWhereHas('mascota', function ($mascotaQuery) use ($search) {
                        $mascotaQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                                $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                                    ->orWhere('dni', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if (!empty($tipo)) {
            $query->where('tipo_atencion', $tipo);
        }

        if ($requestedMascotaId) {
            $query->where('mascota_id', $requestedMascotaId);
        }

        $atenciones = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseDirectas)->count(),
            'hoy' => (clone $baseDirectas)->whereDate('fecha', $today)->count(),
            'consulta' => (clone $baseDirectas)->where('tipo_atencion', 'consulta')->count(),
            'vacunacion' => (clone $baseDirectas)->where('tipo_atencion', 'vacunacion')->count(),
            'control' => (clone $baseDirectas)->where('tipo_atencion', 'control')->count(),
        ];

        $rutaAtencion = [
            'citas_pendientes_hoy' => Citas::where('estado', 'pendiente')->whereDate('fecha', $today)->count(),
            'atenciones_directas_hoy' => $stats['hoy'],
            'vacunas_hoy' => Vacuna::where('estado_aplicacion', 'programada')->whereDate('fecha_programada', $today)->count(),
            'vacunas_semana' => Vacuna::where('estado_aplicacion', 'programada')
                ->whereDate('fecha_programada', '>=', $today)
                ->whereDate('fecha_programada', '<=', $weekLimit)
                ->count(),
            'controles_semana' => Seguimiento::where('estado', 'activo')
                ->whereDate('fecha_proximo_control', '>=', $today)
                ->whereDate('fecha_proximo_control', '<=', $weekLimit)
                ->count(),
        ];

        $citasPendientesHoy = Citas::with('mascota.cliente')
            ->where('estado', 'pendiente')
            ->whereDate('fecha', $today)
            ->orderBy('hora')
            ->limit(5)
            ->get();

        $vacunasPrioritarias = Vacuna::with('mascota.cliente')
            ->where('estado_aplicacion', 'programada')
            ->whereDate('fecha_programada', '<=', $weekLimit)
            ->orderBy('fecha_programada')
            ->limit(4)
            ->get();

        $controlesPendientes = Seguimiento::with(['mascota.cliente', 'cita'])
            ->where('estado', 'activo')
            ->whereNotNull('fecha_proximo_control')
            ->whereDate('fecha_proximo_control', '<=', $weekLimit)
            ->orderBy('fecha_proximo_control')
            ->limit(4)
            ->get();

        $mascotas = Mascotas::with('cliente:id,nombre,dni,telefono')
            ->orderBy('nombre')
            ->get(['id', 'cliente_id', 'nombre', 'tipo_animal', 'raza', 'color', 'foto']);

        $ultimoServicioPorMascota = HistoriaClinica::with('servicioProducto:id,nombre,precio')
            ->whereIn('mascota_id', $mascotas->pluck('id'))
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

        $mascotas->each(function ($mascota) use ($ultimoServicioPorMascota) {
            $ultimo = $ultimoServicioPorMascota->get($mascota->id, []);
            $mascota->setAttribute('ultimo_servicio_id', $ultimo['id'] ?? null);
            $mascota->setAttribute('ultimo_servicio_nombre', $ultimo['nombre'] ?? null);
            $mascota->setAttribute('ultimo_servicio_precio', $ultimo['precio'] ?? null);
        });

        $mascotasRecientes = HistoriaClinica::with('mascota.cliente:id,nombre,dni,telefono')
            ->whereNotNull('mascota_id')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(18)
            ->get()
            ->pluck('mascota')
            ->filter()
            ->unique('id')
            ->take(6)
            ->values();

        if ($mascotasRecientes->isEmpty()) {
            $mascotasRecientes = $mascotas->sortByDesc('id')->take(6)->values();
        }

        $prefillMascotaId = $mascotas->contains('id', $requestedMascotaId) ? $requestedMascotaId : null;

        $veterinarios = Veterinarios::with('user:id,name')->orderBy('id')->get();
        $clientes = Clientes::query()->orderBy('nombre')->get(['id', 'dni', 'nombre']);
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

        return view('atencion-rapida.index', compact(
            'atenciones',
            'stats',
            'rutaAtencion',
            'citasPendientesHoy',
            'vacunasPrioritarias',
            'controlesPendientes',
            'mascotas',
            'mascotasRecientes',
            'veterinarios',
            'clientes',
            'vacunaCatalogo',
            'serviciosCatalogo',
            'prefillMascotaId',
            'shouldOpenCreate'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->attentionFlowService->validate($request, 'atencionRapidaStore');
        $veterinarioId = $this->resolveVeterinarioId($request->input('veterinario_id'));
        $historyData = $this->attentionFlowService->buildHistoriaData($validated, 'flujo rápido');

        $this->clinicalAttentionService->register([
            'mascota_id' => $validated['mascota_id'],
            'origen_atencion' => 'manual',
            'tipo_atencion' => $validated['tipo_atencion'],
            'fecha' => $validated['historia_fecha'],
            'diagnostico' => $historyData['diagnostico'],
            'observaciones' => $historyData['observaciones'],
            'peso' => $validated['peso'] ?? null,
            'temperatura' => $validated['temperatura'] ?? null,
            'veterinario_id' => $veterinarioId,
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
                'descripcion' => $validated['tratamiento_descripcion'] ?? null,
                'costo' => $validated['tratamiento_costo'] ?? 0,
                'fecha_inicio' => $validated['tratamiento_fecha_inicio'] ?? null,
                'fecha_fin' => $validated['tratamiento_fecha_fin'] ?? null,
            ],
            'receta' => [
                'medicamentos' => $validated['receta_medicamentos'] ?? null,
                'indicaciones' => $validated['receta_indicaciones'] ?? null,
            ],
            'seguimiento' => [
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

        return redirect()->route('atencion-rapida.index')->with('toast', [
            'type' => 'success',
            'message' => 'Atención directa registrada correctamente.',
        ]);
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
            if (trim((string) $request->input('nombre')) === '') {
                $validator->errors()->add('nombre', 'El nombre del cliente es obligatorio.');
            }

            if (trim((string) $request->input('direccion')) === '') {
                $validator->errors()->add('direccion', 'La direccion del cliente es obligatoria.');
            }
        });

        $validated = $validator->validateWithBag('atencionRapidaClienteStore');
        $validated['nombre'] = trim((string) $validated['nombre']);
        $validated['telefono'] = trim((string) $validated['telefono']);
        $validated['direccion'] = trim((string) $validated['direccion']);
        $validated['email'] = filled($validated['email'] ?? null) ? trim((string) $validated['email']) : null;

        $cliente = Clientes::create($validated);

        return redirect()->route('atencion-rapida.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Cliente creado. Ahora puedes registrar su mascota para continuar con la atención.',
            ])
            ->with('atencion_rapida_ui', [
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

        $validated = $validator->validateWithBag('atencionRapidaMascotaStore');
        $validated['nombre'] = trim((string) $validated['nombre']);
        $validated['tipo_animal'] = trim((string) $validated['tipo_animal']);
        $validated['raza'] = trim((string) $validated['raza']);
        $validated['color'] = filled($validated['color'] ?? null) ? trim((string) $validated['color']) : null;

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('mascotas', 'public');
        }

        $mascota = Mascotas::create($validated);

        return redirect()->route('atencion-rapida.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Mascota creada. Ya puedes continuar con la atención sin cita.',
            ])
            ->with('atencion_rapida_ui', [
                'open_main' => true,
                'selected_mascota_id' => $mascota->id,
            ]);
    }
}
