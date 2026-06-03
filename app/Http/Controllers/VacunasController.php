<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Vacuna;
use App\Models\Veterinarios;
use App\Services\ClinicalAttentionService;
use App\Traits\ResolveVeterinarioTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VacunasController extends Controller
{
    use ResolveVeterinarioTrait;

    public function __construct(private readonly ClinicalAttentionService $clinicalAttentionService)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $estadoDosis = $request->input('estado_dosis');
        $requestedMascotaId = $request->input('mascota_id');
        $todayDate = now()->toDateString();
        $weekAhead = now()->copy()->addDays(7)->toDateString();

        $query = Vacuna::with(['mascota.cliente', 'historiaClinica']);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                    ->orWhereHas('mascota', function ($mascotaQuery) use ($search) {
                        $mascotaQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                                $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                                    ->orWhere('dni', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if ($estadoDosis === 'aplicadas') {
            $query->where('estado_aplicacion', 'aplicada');
        }

        if ($estadoDosis === 'programadas') {
            $query->where('estado_aplicacion', 'programada');
        }

        if ($estadoDosis === 'proximas') {
            $query->where(function ($subQuery) use ($todayDate) {
                $subQuery->where(function ($appliedQuery) use ($todayDate) {
                    $appliedQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '>=', $todayDate);
                })->orWhere(function ($scheduledQuery) use ($todayDate) {
                    $scheduledQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '>=', $todayDate);
                });
            });
        }

        if ($estadoDosis === 'vencidas') {
            $query->where(function ($subQuery) use ($todayDate) {
                $subQuery->where(function ($nextDoseQuery) use ($todayDate) {
                    $nextDoseQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '<', $todayDate);
                })->orWhere(function ($scheduledQuery) use ($todayDate) {
                    $scheduledQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '<', $todayDate);
                });
            });
        }

        if (!empty($requestedMascotaId)) {
            $query->where('mascota_id', $requestedMascotaId);
        }

        $vacunas = $query
            ->orderByRaw('COALESCE(fecha_programada, fecha_aplicacion, proxima_dosis) desc')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $stats = [
            'total' => Vacuna::count(),
            'aplicadas' => Vacuna::where('estado_aplicacion', 'aplicada')->count(),
            'programadas' => Vacuna::where('estado_aplicacion', 'programada')->count(),
            'mes' => Vacuna::where('estado_aplicacion', 'aplicada')
                ->whereMonth('fecha_aplicacion', now()->month)
                ->whereYear('fecha_aplicacion', now()->year)
                ->count(),
            'proximas' => Vacuna::where(function ($query) use ($todayDate) {
                $query->where(function ($subQuery) use ($todayDate) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '>=', $todayDate);
                })->orWhere(function ($subQuery) use ($todayDate) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '>=', $todayDate);
                });
            })->count(),
            'vencidas' => Vacuna::where(function ($query) use ($todayDate) {
                $query->where(function ($subQuery) use ($todayDate) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '<', $todayDate);
                })->orWhere(function ($subQuery) use ($todayDate) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '<', $todayDate);
                });
            })->count(),
            'hoy' => Vacuna::where(function ($query) use ($todayDate) {
                $query->where(function ($subQuery) use ($todayDate) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', $todayDate);
                })->orWhere(function ($subQuery) use ($todayDate) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', $todayDate);
                });
            })->count(),
            'semana' => Vacuna::where(function ($query) use ($todayDate, $weekAhead) {
                $query->where(function ($subQuery) use ($todayDate, $weekAhead) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '>=', $todayDate)
                        ->whereDate('proxima_dosis', '<=', $weekAhead);
                })->orWhere(function ($subQuery) use ($todayDate, $weekAhead) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '>=', $todayDate)
                        ->whereDate('fecha_programada', '<=', $weekAhead);
                });
            })
                ->count(),
        ];

        $vacunaMascotas = Mascotas::with('cliente:id,nombre,dni')
            ->orderBy('nombre')
            ->get(['id', 'cliente_id', 'nombre', 'tipo_animal', 'foto']);

        $vacunaCatalogo = Vacuna::query()
            ->whereNotNull('nombre')
            ->where('nombre', '!=', '')
            ->select('nombre')
            ->distinct()
            ->orderBy('nombre')
            ->pluck('nombre');

        $prefillMascotaId = $vacunaMascotas->contains('id', (int) $requestedMascotaId)
            ? (int) $requestedMascotaId
            : null;

        $shouldOpenCreate = $request->boolean('open_create');
        $selectedMascota = $prefillMascotaId
            ? $vacunaMascotas->firstWhere('id', $prefillMascotaId)
            : null;

        $vacunasUrgentes = Vacuna::with(['mascota.cliente'])
            ->where(function ($query) use ($todayDate, $weekAhead) {
                $query->where(function ($subQuery) use ($todayDate, $weekAhead) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '>=', $todayDate)
                        ->whereDate('proxima_dosis', '<=', $weekAhead);
                })->orWhere(function ($subQuery) use ($todayDate, $weekAhead) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '>=', $todayDate)
                        ->whereDate('fecha_programada', '<=', $weekAhead);
                });
            })
            ->orderByRaw('COALESCE(fecha_programada, proxima_dosis) asc')
            ->limit(4)
            ->get();

        $vacunaMascotasRecientes = Vacuna::with(['mascota.cliente'])
            ->whereNotNull('mascota_id')
            ->orderByRaw('COALESCE(fecha_programada, fecha_aplicacion, proxima_dosis) desc')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->filter(fn ($vacuna) => $vacuna->mascota)
            ->unique('mascota_id')
            ->take(5)
            ->values()
            ->map(fn ($vacuna) => $vacuna->mascota);

        return view('vacunas.index', compact(
            'vacunas',
            'stats',
            'vacunaMascotas',
            'vacunaCatalogo',
            'prefillMascotaId',
            'shouldOpenCreate',
            'selectedMascota',
            'vacunasUrgentes',
            'vacunaMascotasRecientes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateVacuna($request);

        if ($validated['estado_aplicacion'] === 'programada') {
            $vacuna = Vacuna::create($this->extractPersistedData($validated));
            $this->clinicalAttentionService->syncScheduledVaccinationFollowUp(
                $vacuna,
                $this->resolveVeterinarioId(null)
            );

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Vacuna programada correctamente. El sistema creo su control preventivo y cita de retorno.',
            ]);
        }

        $veterinarioId = $this->resolveVeterinarioId(null);

        $this->clinicalAttentionService->registerPreventiveVaccination([
            'mascota_id' => $validated['mascota_id'],
            'cita_id' => null,
            'origen_atencion' => 'preventiva',
            'tipo_atencion' => 'vacunacion',
            'fecha' => $validated['fecha_aplicacion'],
            'veterinario_id' => $veterinarioId,
            'vacuna' => [
                'nombre' => $validated['nombre'],
                'estado_aplicacion' => 'aplicada',
                'fecha_aplicacion' => $validated['fecha_aplicacion'],
                'proxima_dosis' => $validated['proxima_dosis'] ?? null,
            ],
        ]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Vacuna registrada correctamente y vinculada al historial preventivo.',
        ]);
    }

    public function update(Request $request, Vacuna $vacuna)
    {
        $validated = $this->validateVacuna($request, $vacuna);
        $wasProgramada = $vacuna->estado_aplicacion === 'programada';

        DB::transaction(function () use ($vacuna, $validated, $wasProgramada) {
            $vacuna->update($this->extractPersistedData($validated));

            if ($validated['estado_aplicacion'] === 'aplicada') {
                $this->syncPreventiveHistory($vacuna, $validated);
                if ($wasProgramada) {
                    $this->clinicalAttentionService->completeScheduledVaccinationFollowUp(
                        $vacuna->refresh(),
                        'Vacuna aplicada desde el módulo preventivo.'
                    );
                }
                $this->clinicalAttentionService->syncNextVaccinationSchedule($vacuna, $validated['proxima_dosis'] ?? null);
                $this->clinicalAttentionService->syncPreventiveFollowUpFromVaccination(
                    $vacuna->refresh(),
                    $validated['proxima_dosis'] ?? null,
                    $this->resolveVeterinarioId(null)
                );
                return;
            }

            $this->cleanupPreventiveHistory($vacuna);
            $this->clinicalAttentionService->syncScheduledVaccinationFollowUp(
                $vacuna->refresh(),
                $this->resolveVeterinarioId(null)
            );
        });

        return redirect()->route('vacunas.index')->with('toast', [
            'type' => 'success',
            'message' => $validated['estado_aplicacion'] === 'aplicada'
                ? 'Vacuna actualizada correctamente y sincronizada con el historial preventivo.'
                : 'Vacuna reprogramada correctamente.',
        ]);
    }

    public function destroy(Vacuna $vacuna)
    {
        DB::transaction(function () use ($vacuna) {
            $historia = $vacuna->historiaClinica;

            $this->clinicalAttentionService->deleteScheduledVaccinationFollowUp($vacuna);

            if ($historia) {
                $this->clinicalAttentionService->syncPreventiveFollowUpFromVaccination($vacuna, null);
            }

            $vacuna->delete();

            if (
                $historia &&
                $historia->cita_id === null &&
                $historia->origen_atencion === 'preventiva' &&
                !$historia->tratamientos()->exists() &&
                !$historia->recetas()->exists() &&
                !$historia->seguimientos()->exists() &&
                !Vacuna::where('historia_clinica_id', $historia->id)->exists()
            ) {
                $historia->delete();
            }
        });

        return redirect()->route('vacunas.index')->with('toast', [
            'type' => 'success',
            'message' => 'Vacuna eliminada correctamente.',
        ]);
    }

    private function validateVacuna(Request $request, ?Vacuna $vacuna = null): array
    {
        $validator = Validator::make($request->all(), [
            'mascota_id' => 'required|exists:mascotas,id',
            'estado_aplicacion' => 'required|in:aplicada,programada',
            'vacuna_nombre_select' => 'nullable|string|max:255',
            'vacuna_nombre_custom' => 'nullable|string|max:255',
            'fecha_aplicacion' => 'nullable|date',
            'fecha_programada' => 'nullable|date',
            'proxima_dosis' => 'nullable|date',
        ]);

        $validator->after(function ($validator) use ($request, $vacuna) {
            $nombre = $this->resolveVacunaNombre($request->input('vacuna_nombre_select'), $request->input('vacuna_nombre_custom'));
            $estadoAplicacion = (string) $request->input('estado_aplicacion');
            $fechaAplicacion = $request->input('fecha_aplicacion');
            $fechaProgramada = $request->input('fecha_programada');
            $proximaDosis = $request->input('proxima_dosis');

            if ($nombre === '') {
                $validator->errors()->add('vacuna_nombre_custom', 'Selecciona o escribe el nombre de la vacuna.');
            }

            if ($estadoAplicacion === 'aplicada' && !$fechaAplicacion) {
                $validator->errors()->add('fecha_aplicacion', 'Indica la fecha en que la vacuna ya fue aplicada.');
            }

            if ($estadoAplicacion === 'programada' && !$fechaProgramada) {
                $validator->errors()->add('fecha_programada', 'Indica la fecha programada para aplicar la vacuna.');
            }

            if ($estadoAplicacion === 'programada' && $proximaDosis) {
                $validator->errors()->add('proxima_dosis', 'La próxima dosis solo se define cuando la vacuna ya fue aplicada.');
            }

            if ($estadoAplicacion === 'aplicada' && $fechaAplicacion && $proximaDosis && $proximaDosis < $fechaAplicacion) {
                $validator->errors()->add('proxima_dosis', 'La próxima dosis no puede ser anterior a la fecha de aplicación.');
            }

            if (
                $estadoAplicacion === 'programada' &&
                $vacuna &&
                $vacuna->historiaClinica &&
                ($vacuna->historiaClinica->cita_id !== null || $vacuna->historiaClinica->origen_atencion !== 'preventiva')
            ) {
                $validator->errors()->add('estado_aplicacion', 'Esta vacuna se registró como aplicada dentro de una atención clínica y no puede volver a estado programada desde este módulo.');
            }

            if (
                $nombre !== '' &&
                $request->filled('mascota_id') &&
                (($estadoAplicacion === 'aplicada' && $fechaAplicacion) || ($estadoAplicacion === 'programada' && $fechaProgramada))
            ) {
                $duplicateQuery = Vacuna::query()
                    ->where('mascota_id', $request->input('mascota_id'))
                    ->where('nombre', $nombre);

                if ($estadoAplicacion === 'aplicada') {
                    $duplicateQuery->where('estado_aplicacion', 'aplicada')
                        ->whereDate('fecha_aplicacion', $fechaAplicacion);
                } else {
                    $duplicateQuery->where('estado_aplicacion', 'programada')
                        ->whereDate('fecha_programada', $fechaProgramada);
                }

                if ($vacuna) {
                    $duplicateQuery->whereKeyNot($vacuna->id);
                }

                if ($duplicateQuery->exists()) {
                    $validator->errors()->add(
                        $estadoAplicacion === 'aplicada' ? 'fecha_aplicacion' : 'fecha_programada',
                        'Ya existe este control de vacuna registrado para la mascota en esa fecha.'
                    );
                }
            }
        });

        $validated = $validator->validateWithBag('vacunaStore');
        $validated['nombre'] = $this->resolveVacunaNombre(
            $validated['vacuna_nombre_select'] ?? null,
            $validated['vacuna_nombre_custom'] ?? null
        );

        return $validated;
    }

    private function resolveVacunaNombre(?string $selectedName, ?string $customName): string
    {
        $selectedName = trim((string) $selectedName);
        $customName = trim((string) $customName);

        if ($selectedName === '__custom__') {
            return $customName;
        }

        return $selectedName;
    }

    private function extractPersistedData(array $validated): array
    {
        $isApplied = $validated['estado_aplicacion'] === 'aplicada';

        return [
            'mascota_id' => $validated['mascota_id'],
            'nombre' => $validated['nombre'],
            'estado_aplicacion' => $validated['estado_aplicacion'],
            'fecha_programada' => $isApplied ? null : ($validated['fecha_programada'] ?? null),
            'fecha_aplicacion' => $isApplied ? ($validated['fecha_aplicacion'] ?? null) : null,
            'proxima_dosis' => $isApplied ? ($validated['proxima_dosis'] ?? null) : null,
        ];
    }

    private function buildPreventiveTexts(string $nombre): array
    {
        $diagnostico = 'Vacunación preventiva';
        $observaciones = $nombre !== ''
            ? 'Aplicación preventiva de vacuna ' . $nombre . '.'
            : 'Aplicación preventiva registrada desde el módulo de vacunas.';

        return [$diagnostico, $observaciones];
    }

    private function syncPreventiveHistory(Vacuna $vacuna, array $validated): void
    {
        [$diagnostico, $observaciones] = $this->buildPreventiveTexts($validated['nombre']);
        $historia = $vacuna->historiaClinica;

        if (!$historia) {
            $historia = HistoriaClinica::create([
                'mascota_id' => $vacuna->mascota_id,
                'cita_id' => null,
                'origen_atencion' => 'preventiva',
                'tipo_atencion' => 'vacunacion',
                'fecha' => $validated['fecha_aplicacion'],
                'diagnostico' => $diagnostico,
                'observaciones' => $observaciones,
            ]);
        } elseif ($historia->cita_id === null && $historia->origen_atencion === 'preventiva') {
            $historia->update([
                'fecha' => $validated['fecha_aplicacion'],
                'tipo_atencion' => 'vacunacion',
                'diagnostico' => $diagnostico,
                'observaciones' => $observaciones,
            ]);
        }

        $vacuna->update([
            'historia_clinica_id' => $historia?->id,
        ]);
    }

    private function cleanupPreventiveHistory(Vacuna $vacuna): void
    {
        $historia = $vacuna->historiaClinica;

        if ($historia) {
            $this->clinicalAttentionService->syncPreventiveFollowUpFromVaccination($vacuna, null);
        }

        $vacuna->update([
            'historia_clinica_id' => null,
        ]);

        if (
            $historia &&
            $historia->cita_id === null &&
            $historia->origen_atencion === 'preventiva' &&
            !$historia->tratamientos()->exists() &&
            !$historia->recetas()->exists() &&
            !$historia->seguimientos()->exists() &&
            !Vacuna::where('historia_clinica_id', $historia->id)->exists()
        ) {
            $historia->delete();
        }
    }

    // resolveVeterinarioId() - movido a App\Traits\ResolveVeterinarioTrait
}

