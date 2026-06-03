<?php

namespace App\Http\Controllers;

use App\Models\Citas;
use App\Models\Clientes;
use App\Models\Mascotas;
use App\Models\Veterinarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MascotasController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $especie = $request->input('especie');
        $orden = $request->input('orden', 'recientes');
        $today = now()->toDateString();
        $openFichaId = $request->integer('open_ficha');

        if ($openFichaId && !Mascotas::whereKey($openFichaId)->exists()) {
            $openFichaId = null;
        }

        $query = Mascotas::with('cliente')
            ->withCount([
                'historiasClinicas',
                'vacunas',
                'citas as citas_pendientes_count' => function ($subQuery) use ($today) {
                    $subQuery->where('estado', 'pendiente')
                        ->whereDate('fecha', '>=', $today);
                },
            ]);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                    ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                        $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('dni', 'like', '%' . $search . '%');
                    });
            });
        }

        if (!empty($especie)) {
            $query->where('tipo_animal', $especie);
        }

        switch ($orden) {
            case 'nombre_asc':
                $query->orderBy('nombre');
                break;
            case 'nombre_desc':
                $query->orderByDesc('nombre');
                break;
            case 'edad_asc':
                $query->orderBy('edad');
                break;
            case 'edad_desc':
                $query->orderByDesc('edad');
                break;
            default:
                $query->latest();
                break;
        }

        $mascotas = $query->paginate(6)->withQueryString();
        $mascotaIds = $mascotas->getCollection()->pluck('id');

        $proximasCitas = collect();
        $ultimasVisitas = collect();

        if ($mascotaIds->isNotEmpty()) {
            $proximasCitas = Citas::query()
                ->selectRaw('mascota_id, MIN(fecha) as fecha')
                ->whereIn('mascota_id', $mascotaIds)
                ->whereDate('fecha', '>=', now()->toDateString())
                ->groupBy('mascota_id')
                ->pluck('fecha', 'mascota_id');

            $ultimasVisitas = Citas::query()
                ->selectRaw('mascota_id, MAX(fecha) as fecha')
                ->whereIn('mascota_id', $mascotaIds)
                ->whereDate('fecha', '<=', now()->toDateString())
                ->groupBy('mascota_id')
                ->pluck('fecha', 'mascota_id');
        }

        $speciesCounts = Mascotas::query()
            ->whereNotNull('tipo_animal')
            ->selectRaw('tipo_animal, COUNT(*) as total')
            ->groupBy('tipo_animal')
            ->orderByDesc('total')
            ->pluck('total', 'tipo_animal');

        $stats = [
            'total' => Mascotas::count(),
            'activas' => Citas::query()
                ->whereDate('fecha', '>=', now()->toDateString())
                ->distinct()
                ->count('mascota_id'),
            'especies' => Mascotas::query()
                ->whereNotNull('tipo_animal')
                ->distinct()
                ->count('tipo_animal'),
            'citas_hoy' => Citas::query()
                ->whereDate('fecha', now()->toDateString())
                ->count(),
        ];

        $citaMascotas = Mascotas::with('cliente:id,nombre')
            ->orderBy('nombre')
            ->get(['id', 'cliente_id', 'nombre']);

        $veterinarios = Veterinarios::with('user:id,name')
            ->orderBy('id')
            ->get();

        $clientes = Clientes::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'dni']);

        return view('mascotas.index', compact(
            'mascotas',
            'speciesCounts',
            'stats',
            'proximasCitas',
            'ultimasVisitas',
            'orden',
            'clientes',
            'citaMascotas',
            'veterinarios',
            'openFichaId'
        ));
    }

    public function create($cliente_id)
    {
        $cliente = Clientes::findOrFail($cliente_id);

        return view('mascotas.create', compact('cliente'));
    }

    public function edit(Mascotas $mascota)
    {
        $mascota->load('cliente');
        $cliente = $mascota->cliente;

        return view('mascotas.edit', compact('mascota', 'cliente'));
    }

    public function store(Request $request)
    {
        $redirectTo = $this->resolveRedirectTo($request->input('redirect_to'));
        $errorBag = $redirectTo === 'clientes.index' ? 'mascotaStore' : null;
        $payload = $this->validateMascotaStoreFlow($request, $errorBag);
        $data = $payload['mascota'];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('mascotas', 'public');
        } else {
            unset($data['foto']);
        }

        DB::transaction(function () use ($payload, $data) {
            if ($payload['cliente_mode'] === 'new') {
                $cliente = Clientes::create($payload['cliente']);
                $data['cliente_id'] = $cliente->id;
            }

            Mascotas::create($data);
        });

        return redirect()->route($redirectTo)->with('toast', [
            'type' => 'success',
            'message' => 'Mascota registrada correctamente',
        ]);
    }

    public function update(Request $request, Mascotas $mascota)
    {
        $data = $this->validateMascota($request, $mascota);

        if (blank($data['raza'] ?? null)) {
            return back()
                ->withErrors(['raza' => 'Debe seleccionar o escribir una raza'])
                ->withInput();
        }

        if ($request->hasFile('foto')) {
            if ($mascota->foto && file_exists(storage_path('app/public/' . $mascota->foto))) {
                unlink(storage_path('app/public/' . $mascota->foto));
            }

            $data['foto'] = $request->file('foto')->store('mascotas', 'public');
        } else {
            unset($data['foto']);
        }

        $mascota->update($data);

        return redirect()->route('mascotas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Mascota actualizada correctamente',
        ]);
    }

    public function destroy(Mascotas $mascota)
    {
        if ($mascota->citas()->exists() || $mascota->historiasClinicas()->exists() || $mascota->vacunas()->exists()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'No puedes eliminar esta mascota porque ya tiene movimiento clínico o citas registradas.',
            ]);
        }

        if ($mascota->foto && file_exists(storage_path('app/public/' . $mascota->foto))) {
            unlink(storage_path('app/public/' . $mascota->foto));
        }

        $mascota->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Mascota eliminada correctamente',
        ]);
    }

    public function showJson($id)
    {
        $mascota = Mascotas::with([
            'cliente',
            'historiasClinicas' => function ($query) {
                $query->with(['tratamientos.veterinario.user', 'recetas', 'seguimientos.veterinario.user'])
                    ->latest('fecha')
                    ->latest('id');
            },
            'vacunas' => function ($query) {
                $query->latest('fecha_aplicacion')
                    ->latest('id');
            },
            'citas' => function ($query) {
                $query->where('estado', 'pendiente')
                    ->whereDate('fecha', '>=', now()->toDateString())
                    ->orderBy('fecha')
                    ->orderBy('hora');
            },
            'ventas' => function ($query) {
                $query->with(['detalles.producto', 'detalles.tratamiento'])
                    ->latest('fecha')
                    ->latest('id');
            },
        ])->findOrFail($id);

        $historias = $mascota->historiasClinicas->take(3)->map(function ($historia) {
            return [
                'id' => $historia->id,
                'fecha' => optional($historia->fecha)->format('Y-m-d'),
                'diagnostico' => $historia->diagnostico,
                'observaciones' => $historia->observaciones,
                'tratamientos_count' => $historia->tratamientos->count(),
                'recetas_count' => $historia->recetas->count(),
            ];
        })->values();

        $ultimaHistoria = $mascota->historiasClinicas->first();
        $ultimaVacuna = $mascota->vacunas->first();
        $proximaCita = $mascota->citas->first();
        $vacunaPendiente = $mascota->vacunas
            ->filter(function ($vacuna) {
                return optional($vacuna->proxima_dosis)->format('Y-m-d')
                    || optional($vacuna->fecha_programada)->format('Y-m-d');
            })
            ->sortBy(fn ($vacuna) => optional($vacuna->fecha_programada)->format('Y-m-d') ?: optional($vacuna->proxima_dosis)->format('Y-m-d'))
            ->first();
        $vacunaVencida = $mascota->vacunas
            ->filter(function ($vacuna) {
                if (optional($vacuna->proxima_dosis)->format('Y-m-d') && $vacuna->proxima_dosis->isPast()) {
                    return true;
                }

                return optional($vacuna->fecha_programada)->format('Y-m-d') && $vacuna->fecha_programada->isPast();
            })
            ->sortBy(fn ($vacuna) => optional($vacuna->fecha_programada)->format('Y-m-d') ?: optional($vacuna->proxima_dosis)->format('Y-m-d'))
            ->first();
        $tratamientosActivos = $mascota->historiasClinicas
            ->flatMap(function ($historia) {
                return $historia->tratamientos->map(function ($tratamiento) use ($historia) {
                    return [
                        'id' => $tratamiento->id,
                        'fecha_inicio' => optional($tratamiento->fecha_inicio)->format('Y-m-d'),
                        'fecha_fin' => optional($tratamiento->fecha_fin)->format('Y-m-d'),
                        'descripcion' => $tratamiento->descripcion,
                        'costo' => $tratamiento->costo,
                        'profesional' => optional($tratamiento->veterinario)->nombre,
                        'diagnostico' => $historia->diagnostico,
                        'proximo_control' => optional($tratamiento->proximo_control)->format('Y-m-d'),
                    ];
                });
            })
            ->filter(function ($tratamiento) {
                if (!$tratamiento['fecha_inicio']) {
                    return false;
                }

                $hoy = now()->toDateString();

                return $tratamiento['fecha_inicio'] <= $hoy
                    && (!$tratamiento['fecha_fin'] || $tratamiento['fecha_fin'] >= $hoy);
            })
            ->sortByDesc('fecha_inicio')
            ->take(3)
            ->values();

        $recetasRecientes = $mascota->historiasClinicas
            ->flatMap(function ($historia) {
                return $historia->recetas->map(function ($receta) use ($historia) {
                    return [
                        'id' => $receta->id,
                        'fecha' => optional($historia->fecha)->format('Y-m-d') ?: optional($receta->created_at)->format('Y-m-d'),
                        'medicamentos' => $receta->medicamentos,
                        'indicaciones' => $receta->indicaciones,
                        'diagnostico' => $historia->diagnostico,
                    ];
                });
            })
            ->sortByDesc('fecha')
            ->take(3)
            ->values();

        $controlesActivos = $mascota->historiasClinicas
            ->flatMap(function ($historia) {
                return $historia->seguimientos->map(function ($seguimiento) use ($historia) {
                    return [
                        'id' => $seguimiento->id,
                        'tipo' => $seguimiento->tipo ?: 'clinico',
                        'origen' => $seguimiento->origen ?: 'atencion',
                        'titulo' => $seguimiento->titulo,
                        'estado' => $seguimiento->estado,
                        'motivo' => $seguimiento->motivo,
                        'notas' => $seguimiento->notas,
                        'evolucion' => $seguimiento->evolucion,
                        'fecha_inicio' => optional($seguimiento->fecha_inicio)->format('Y-m-d'),
                        'fecha_proximo_control' => optional($seguimiento->fecha_proximo_control)->format('Y-m-d'),
                        'dias_retorno' => $seguimiento->dias_retorno,
                        'profesional' => optional($seguimiento->veterinario)->nombre,
                        'diagnostico' => $historia->diagnostico,
                        'tipo_label' => match ($seguimiento->tipo) {
                            'preventivo' => 'Vacuna pendiente',
                            'terapeutico' => 'Control de tratamiento',
                            default => 'Control médico',
                        },
                    ];
                });
            })
            ->filter(fn ($seguimiento) => ($seguimiento['estado'] ?? 'activo') !== 'cerrado')
            ->sortBy(fn ($seguimiento) => $seguimiento['fecha_proximo_control'] ?: $seguimiento['fecha_inicio'])
            ->take(3)
            ->values();

        $seguimientoPendiente = $controlesActivos
            ->filter(function ($seguimiento) {
                return ($seguimiento['tipo'] ?? 'clinico') !== 'preventivo'
                    && filled($seguimiento['fecha_proximo_control'] ?? null)
                    && $seguimiento['fecha_proximo_control'] >= now()->toDateString();
            })
            ->sortBy('fecha_proximo_control')
            ->first();

        $ultimaVenta = $mascota->ventas->first();
        $tratamientoParaVenta = $mascota->historiasClinicas
            ->flatMap(fn ($historia) => $historia->tratamientos)
            ->sortByDesc('fecha_inicio')
            ->first();
        $alertasClinicas = collect();
        $siguienteAccion = null;

        if ($vacunaVencida) {
            $alertasClinicas->push([
                'tipo' => 'vacuna_vencida',
                'tono' => 'rose',
                'titulo' => 'Vacuna vencida',
                'detalle' => $vacunaVencida->nombre . ' debio atenderse el ' . (optional($vacunaVencida->fecha_programada)->format('d/m/Y') ?: optional($vacunaVencida->proxima_dosis)->format('d/m/Y')) . '.',
            ]);
            $siguienteAccion = [
                'label' => 'Abrir control preventivo',
                'detalle' => 'Hay una dosis vencida pendiente de regularizar.',
                'url' => route('vacunas.index', ['mascota_id' => $mascota->id, 'open_create' => 1]),
                'tone' => 'rose',
            ];
        } elseif ($vacunaPendiente && (optional($vacunaPendiente->proxima_dosis)->format('Y-m-d') || optional($vacunaPendiente->fecha_programada)->format('Y-m-d'))) {
            $alertasClinicas->push([
                'tipo' => 'vacuna_proxima',
                'tono' => 'blue',
                'titulo' => 'Próxima vacuna',
                'detalle' => $vacunaPendiente->nombre . ' programada para el ' . (optional($vacunaPendiente->fecha_programada)->format('d/m/Y') ?: optional($vacunaPendiente->proxima_dosis)->format('d/m/Y')) . '.',
            ]);
            $siguienteAccion = [
                'label' => 'Revisar próxima vacuna',
                'detalle' => 'La siguiente dosis ya tiene fecha cercana.',
                'url' => route('vacunas.index', ['mascota_id' => $mascota->id, 'open_create' => 1]),
                'tone' => 'blue',
            ];
        }

        if ($seguimientoPendiente) {
            $alertasClinicas->push([
                'tipo' => 'seguimiento',
                'tono' => 'violet',
                'titulo' => 'Seguimiento pendiente',
                'detalle' => ($seguimientoPendiente['titulo'] ?: 'Control de retorno') . ' con control previsto para el ' . $this->formatDateForAlert($seguimientoPendiente['fecha_proximo_control']) . '.',
            ]);

            if (!$siguienteAccion) {
                $siguienteAccion = [
                    'label' => 'Abrir seguimiento',
                    'detalle' => 'Este paciente ya tiene un control de retorno activo aparte de su atención puntual.',
                    'url' => route('seguimientos.index', ['mascota_id' => $mascota->id]),
                    'tone' => 'violet',
                ];
            }
        }

        if ($proximaCita) {
            $alertasClinicas->push([
                'tipo' => 'cita',
                'tono' => 'emerald',
                'titulo' => 'Próxima cita agendada',
                'detalle' => 'La mascota vuelve el ' . optional($proximaCita->fecha)->format('d/m/Y') . ' a las ' . substr((string) $proximaCita->hora, 0, 5) . '.',
            ]);
            if (!$siguienteAccion) {
                $siguienteAccion = [
                    'label' => 'Ver próxima cita',
                    'detalle' => 'La mascota ya tiene una visita programada en agenda.',
                    'url' => route('citas.index', ['mascota_id' => $mascota->id]),
                    'tone' => 'emerald',
                ];
            }
        }

        if (!$siguienteAccion) {
            $siguienteAccion = [
                'label' => 'Abrir nueva atención',
                'detalle' => 'No hay alertas urgentes. Si la mascota vuelve a consulta, registra la nueva evolución desde atención.',
                'url' => route('atencion-rapida.index', ['mascota_id' => $mascota->id, 'open_create' => 1]),
                'tone' => 'emerald',
            ];
        }

        $lineaTiempo = collect()
            ->merge($mascota->historiasClinicas->map(function ($historia) {
                return [
                    'tipo' => 'historia',
                    'fecha' => optional($historia->fecha)->format('Y-m-d'),
                    'titulo' => 'Atención clínica registrada',
                    'detalle' => $historia->diagnostico ?: $historia->observaciones ?: 'Se registró una nueva evolución clínica.',
                    'badge' => 'Atencion',
                    'tone' => 'blue',
                ];
            }))
            ->merge($mascota->historiasClinicas->flatMap(function ($historia) {
                return $historia->tratamientos->map(function ($tratamiento) {
                    return [
                        'tipo' => 'tratamiento',
                        'fecha' => optional($tratamiento->fecha_inicio)->format('Y-m-d'),
                        'titulo' => 'Tratamiento iniciado',
                        'detalle' => $tratamiento->descripcion ?: 'Se inició un tratamiento clínico.',
                        'badge' => 'Tratamiento',
                        'tone' => 'amber',
                    ];
                });
            }))
            ->merge($mascota->historiasClinicas->flatMap(function ($historia) {
                return $historia->recetas->map(function ($receta) {
                    return [
                        'tipo' => 'receta',
                        'fecha' => optional($receta->created_at)->format('Y-m-d') ?: optional($receta->updated_at)->format('Y-m-d'),
                        'titulo' => 'Receta emitida',
                        'detalle' => $receta->medicamentos ?: 'Se registraron indicaciones medicamentosas.',
                        'badge' => 'Receta',
                        'tone' => 'violet',
                    ];
                });
            }))
            ->merge($mascota->vacunas->map(function ($vacuna) {
                return [
                    'tipo' => 'vacuna',
                    'fecha' => optional($vacuna->fecha_aplicacion)->format('Y-m-d'),
                    'titulo' => 'Vacuna aplicada: ' . $vacuna->nombre,
                    'detalle' => $vacuna->proxima_dosis
                        ? 'Próxima dosis programada para ' . optional($vacuna->proxima_dosis)->format('Y-m-d')
                        : 'Sin próxima dosis registrada por ahora.',
                    'badge' => 'Vacuna',
                    'tone' => 'emerald',
                ];
            }))
            ->merge($mascota->historiasClinicas->flatMap(function ($historia) {
                return $historia->seguimientos->map(function ($seguimiento) {
                    return [
                        'tipo' => 'seguimiento',
                        'fecha' => optional($seguimiento->fecha_proximo_control)->format('Y-m-d') ?: optional($seguimiento->fecha_inicio)->format('Y-m-d'),
                        'titulo' => $seguimiento->titulo ?: 'Control de retorno',
                        'detalle' => $seguimiento->evolucion ?: $seguimiento->motivo ?: 'Se registro seguimiento posterior a la atención.',
                        'badge' => 'Seguimiento',
                        'tone' => 'rose',
                    ];
                });
            }))
            ->filter(fn ($item) => filled($item['fecha'] ?? null))
            ->sortByDesc('fecha')
            ->take(8)
            ->values();

        return response()->json([
            'id' => $mascota->id,
            'cliente_id' => $mascota->cliente_id,
            'nombre' => $mascota->nombre,
            'tipo_animal' => $mascota->tipo_animal,
            'raza' => $mascota->raza,
            'color' => $mascota->color,
            'edad' => $mascota->edad,
            'sexo' => $mascota->sexo,
            'foto' => $mascota->foto,
            'created_at' => optional($mascota->created_at)->format('Y-m-d'),
            'cliente' => [
                'nombre' => optional($mascota->cliente)->nombre,
                'dni' => optional($mascota->cliente)->dni,
            ],
            'historias_total' => $mascota->historiasClinicas->count(),
            'historias_recientes' => $historias,
            'ultima_historia' => $ultimaHistoria ? [
                'fecha' => optional($ultimaHistoria->fecha)->format('Y-m-d'),
                'diagnostico' => $ultimaHistoria->diagnostico,
                'observaciones' => $ultimaHistoria->observaciones,
                'tratamiento' => optional($ultimaHistoria->tratamientos->first())->descripcion,
                'receta' => optional($ultimaHistoria->recetas->first())->medicamentos,
            ] : null,
            'ultima_vacuna' => $ultimaVacuna ? [
                'nombre' => $ultimaVacuna->nombre,
                'fecha_aplicacion' => optional($ultimaVacuna->fecha_aplicacion)->format('Y-m-d'),
                'proxima_dosis' => optional($ultimaVacuna->proxima_dosis)->format('Y-m-d'),
            ] : null,
            'proxima_cita' => $proximaCita ? [
                'fecha' => optional($proximaCita->fecha)->format('Y-m-d'),
                'hora' => $proximaCita->hora,
            ] : null,
            'alertas_clinicas' => $alertasClinicas->values(),
            'siguiente_accion' => $siguienteAccion,
            'controles_activos' => $controlesActivos,
            'tratamientos_activos' => $tratamientosActivos,
            'recetas_recientes' => $recetasRecientes,
            'ventas_total' => $mascota->ventas->count(),
            'ultima_venta' => $ultimaVenta ? [
                'fecha' => optional($ultimaVenta->fecha)->format('Y-m-d'),
                'total' => $ultimaVenta->total,
                'estado' => $ultimaVenta->estado,
            ] : null,
            'linea_tiempo' => $lineaTiempo,
            'historial_url' => route('historias-clinicas.index', ['mascota_id' => $mascota->id]),
            'nueva_historia_url' => route('atencion-rapida.index', ['mascota_id' => $mascota->id, 'open_create' => 1]),
            'tratamientos_url' => route('tratamientos.index', ['mascota_id' => $mascota->id]),
            'nuevo_tratamiento_url' => $ultimaHistoria
                ? route('tratamientos.index', ['mascota_id' => $mascota->id, 'historia_clinica_id' => $ultimaHistoria->id, 'open_create' => 1])
                : null,
            'recetas_url' => route('recetas.index', ['mascota_id' => $mascota->id]),
            'nueva_receta_url' => $ultimaHistoria
                ? route('recetas.index', ['mascota_id' => $mascota->id, 'historia_clinica_id' => $ultimaHistoria->id, 'open_create' => 1])
                : null,
            'vacunas_url' => route('vacunas.index', ['mascota_id' => $mascota->id]),
            'nueva_vacuna_url' => route('vacunas.index', ['mascota_id' => $mascota->id, 'open_create' => 1]),
            'atencion_rapida_url' => route('atencion-rapida.index', ['mascota_id' => $mascota->id, 'open_create' => 1]),
            'citas_url' => route('citas.index', ['mascota_id' => $mascota->id, 'open_create' => 1]),
            'controles_url' => route('seguimientos.index', ['mascota_id' => $mascota->id]),
            'ventas_url' => route('ventas.index', ['search' => $mascota->nombre]),
            'nueva_venta_url' => $tratamientoParaVenta
                ? route('ventas.index', ['tratamiento_id' => $tratamientoParaVenta->id, 'open_create' => 1])
                : route('ventas.index', ['open_create' => 1]),
        ]);
    }

    private function formatDateForAlert(?string $date): string
    {
        return $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : 'fecha pendiente';
    }

    private function validateMascota(Request $request, ?Mascotas $mascota = null, ?string $errorBag = null): array
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
            'sexo.required' => 'Debe seleccionar el sexo',
            'sexo.in' => 'El sexo debe ser Macho o Hembra',
        ]);

        $validator->after(function ($validator) use ($request, $mascota) {
            $nombre = trim((string) $request->input('nombre'));
            $raza = trim((string) $request->input('raza'));
            $tipoAnimal = trim((string) $request->input('tipo_animal'));

            if (blank($raza)) {
                $validator->errors()->add('raza', 'Debe seleccionar o escribir una raza');
            }

            if (blank($nombre)) {
                $validator->errors()->add('nombre', 'Escribe el nombre de la mascota.');
            }

            if ($nombre !== '' && $request->filled('cliente_id')) {
                $duplicateQuery = Mascotas::query()
                    ->where('cliente_id', $request->input('cliente_id'))
                    ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)]);

                if ($tipoAnimal !== '') {
                    $duplicateQuery->where('tipo_animal', $tipoAnimal);
                }

                if ($mascota) {
                    $duplicateQuery->whereKeyNot($mascota->id);
                }

                if ($duplicateQuery->exists()) {
                    $validator->errors()->add('nombre', 'Ya existe una mascota con ese nombre para el cliente seleccionado.');
                }
            }
        });

        $validated = $errorBag ? $validator->validateWithBag($errorBag) : $validator->validate();
        $validated['nombre'] = trim((string) $validated['nombre']);
        $validated['tipo_animal'] = trim((string) $validated['tipo_animal']);
        $validated['raza'] = trim((string) ($validated['raza'] ?? ''));
        $validated['color'] = trim((string) ($validated['color'] ?? '')) ?: null;

        return $validated;
    }

    private function validateMascotaStoreFlow(Request $request, ?string $errorBag = null): array
    {
        $validator = Validator::make($request->all(), [
            'cliente_mode' => 'required|in:existing,new',
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_dni' => ['nullable', 'digits:8', Rule::unique('clientes', 'dni')],
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_telefono' => 'nullable|digits:9',
            'cliente_email' => ['nullable', 'email', 'max:255', Rule::unique('clientes', 'email')],
            'cliente_direccion' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:255',
            'tipo_animal' => 'required|string|max:255',
            'raza' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:100',
            'edad' => 'required|numeric|min:0',
            'sexo' => 'required|in:Macho,Hembra',
            'foto' => 'nullable|image|max:2048',
        ], [
            'sexo.required' => 'Debe seleccionar el sexo',
            'sexo.in' => 'El sexo debe ser Macho o Hembra',
            'cliente_dni.digits' => 'El DNI del cliente debe tener 8 dígitos.',
            'cliente_telefono.digits' => 'El celular del cliente debe tener 9 dígitos.',
            'cliente_email.email' => 'Escribe un correo válido para el cliente.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $clienteMode = $request->input('cliente_mode', 'existing');
            $nombreMascota = trim((string) $request->input('nombre'));
            $tipoAnimal = trim((string) $request->input('tipo_animal'));
            $raza = trim((string) $request->input('raza'));

            $clienteId = null;

            if ($clienteMode === 'existing') {
                $clienteId = $request->input('cliente_id');

                if (!$clienteId) {
                    $validator->errors()->add('cliente_id', 'Selecciona un cliente para continuar.');
                }
            } else {
                $clienteNombre = trim((string) $request->input('cliente_nombre'));
                $clienteTelefono = trim((string) $request->input('cliente_telefono'));
                $clienteDireccion = trim((string) $request->input('cliente_direccion'));
                $clienteEmail = trim((string) $request->input('cliente_email'));

                if (trim((string) $request->input('cliente_dni')) === '') {
                    $validator->errors()->add('cliente_dni', 'Escribe el DNI del cliente.');
                }

                if ($clienteNombre === '') {
                    $validator->errors()->add('cliente_nombre', 'Escribe el nombre del cliente.');
                }

                if ($clienteTelefono === '') {
                    $validator->errors()->add('cliente_telefono', 'Escribe el celular del cliente.');
                }

                if ($clienteDireccion === '') {
                    $validator->errors()->add('cliente_direccion', 'Escribe una direccion de referencia.');
                }

                if ($clienteEmail !== '') {
                    $duplicateEmail = Clientes::query()
                        ->whereRaw('LOWER(email) = ?', [mb_strtolower($clienteEmail)]);

                    if ($duplicateEmail->exists()) {
                        $validator->errors()->add('cliente_email', 'Ya existe un cliente con ese correo registrado.');
                    }
                }
            }

            if ($raza === '') {
                $validator->errors()->add('raza', 'Debe seleccionar o escribir una raza');
            }

            if ($nombreMascota === '') {
                $validator->errors()->add('nombre', 'Escribe el nombre de la mascota.');
            }

            if ($nombreMascota !== '' && $clienteId) {
                $duplicateQuery = Mascotas::query()
                    ->where('cliente_id', $clienteId)
                    ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreMascota)]);

                if ($tipoAnimal !== '') {
                    $duplicateQuery->where('tipo_animal', $tipoAnimal);
                }

                if ($duplicateQuery->exists()) {
                    $validator->errors()->add('nombre', 'Ya existe una mascota con ese nombre para el cliente seleccionado.');
                }
            }
        });

        $validated = $errorBag ? $validator->validateWithBag($errorBag) : $validator->validate();
        $clienteMode = $validated['cliente_mode'];

        $mascota = [
            'cliente_id' => $clienteMode === 'existing' ? $validated['cliente_id'] : null,
            'nombre' => trim((string) $validated['nombre']),
            'tipo_animal' => trim((string) $validated['tipo_animal']),
            'raza' => trim((string) ($validated['raza'] ?? '')),
            'color' => trim((string) ($validated['color'] ?? '')) ?: null,
            'edad' => $validated['edad'],
            'sexo' => $validated['sexo'],
        ];

        if (isset($validated['foto'])) {
            $mascota['foto'] = $validated['foto'];
        }

        $cliente = null;

        if ($clienteMode === 'new') {
            $cliente = [
                'dni' => trim((string) $validated['cliente_dni']),
                'nombre' => trim((string) $validated['cliente_nombre']),
                'telefono' => trim((string) $validated['cliente_telefono']),
                'email' => trim((string) ($validated['cliente_email'] ?? '')) ?: null,
                'direccion' => trim((string) $validated['cliente_direccion']),
            ];
        }

        return [
            'cliente_mode' => $clienteMode,
            'cliente' => $cliente,
            'mascota' => $mascota,
        ];
    }

    private function resolveRedirectTo(?string $redirectTo): string
    {
        return in_array($redirectTo, ['clientes.index', 'mascotas.index'], true)
            ? $redirectTo
            : 'mascotas.index';
    }
}

