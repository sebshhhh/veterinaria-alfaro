<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Seguimiento;
use App\Models\Veterinarios;
use App\Services\ClinicalAttentionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class SeguimientosController extends Controller
{
    public function __construct(private readonly ClinicalAttentionService $clinicalAttentionService)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $estado = $request->input('estado');
        $tipo = $request->input('tipo');
        $fecha = $request->input('fecha');
        $mascotaId = $request->input('mascota_id');
        $historiaClinicaId = $request->input('historia_clinica_id');
        $today = now()->toDateString();
        $controlAlertDays = (int) ConfiguracionSistema::numero('control_alert_days', 7);
        $weekAhead = now()->copy()->addDays($controlAlertDays)->toDateString();
        $defaultControlTime = (string) ConfiguracionSistema::valor('default_control_time', '09:00');

        $query = Seguimiento::with([
            'mascota.cliente',
            'historiaClinica',
            'veterinario.user',
            'cita',
            'vacuna',
        ]);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('titulo', 'like', '%' . $search . '%')
                    ->orWhere('motivo', 'like', '%' . $search . '%')
                    ->orWhere('notas', 'like', '%' . $search . '%')
                    ->orWhere('evolucion', 'like', '%' . $search . '%')
                    ->orWhereHas('mascota', function ($mascotaQuery) use ($search) {
                        $mascotaQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                                $clienteQuery->where('nombre', 'like', '%' . $search . '%')
                                    ->orWhere('dni', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if (filled($mascotaId)) {
            $query->where('mascota_id', $mascotaId);
        }

        if (filled($historiaClinicaId)) {
            $query->where('historia_clinica_id', $historiaClinicaId);
        }

        if (filled($fecha)) {
            $query->whereDate('fecha_proximo_control', $fecha);
        }

        if (filled($tipo)) {
            $query->where('tipo', $tipo);
        }

        if (filled($estado)) {
            if ($estado === 'hoy') {
                $query->where('estado', '!=', 'cerrado')
                    ->whereNotNull('fecha_proximo_control')
                    ->whereDate('fecha_proximo_control', $today);
            } elseif ($estado === 'vencidos') {
                $query->where('estado', '!=', 'cerrado')
                    ->whereNotNull('fecha_proximo_control')
                    ->whereDate('fecha_proximo_control', '<', $today);
            } elseif (in_array($estado, ['proximos', 'próximos'], true)) {
                $query->whereNotNull('fecha_proximo_control')
                    ->whereDate('fecha_proximo_control', '>=', $today)
                    ->whereDate('fecha_proximo_control', '<=', $weekAhead)
                    ->where('estado', '!=', 'cerrado');
            } elseif ($estado === 'pendientes') {
                $query->where('estado', 'activo')
                    ->where(function ($subQuery) use ($today) {
                        $subQuery->whereNull('fecha_proximo_control')
                            ->orWhereDate('fecha_proximo_control', '>', $today);
                    });
            } elseif ($estado === 'en_control') {
                $query->where('estado', 'controlado');
            } else {
                $query->where('estado', $estado);
            }
        }

        $seguimientos = $query
            ->orderByRaw("CASE WHEN estado = 'activo' THEN 0 WHEN estado = 'controlado' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE WHEN fecha_proximo_control IS NULL THEN 1 WHEN DATE(fecha_proximo_control) < '$today' THEN 0 WHEN DATE(fecha_proximo_control) = '$today' THEN 1 ELSE 2 END")
            ->orderByRaw('COALESCE(fecha_proximo_control, fecha_inicio) asc')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $seguimientos->getCollection()->transform(function ($seguimiento) use ($today, $weekAhead) {
            $seguimiento->ui_type = $this->resolveFollowUpType($seguimiento);
            $seguimiento->ui_type_label = $this->resolveFollowUpTypeLabel($seguimiento->ui_type);
            $seguimiento->ui_origin_label = $this->resolveOriginLabel($seguimiento);
            $seguimiento->ui_bucket = $this->resolveBucket($seguimiento, $today, $weekAhead);
            $seguimiento->ui_bucket_label = $this->resolveBucketLabel($seguimiento->ui_bucket);
            $seguimiento->ui_bucket_tone = $this->resolveBucketTone($seguimiento->ui_bucket);
            $seguimiento->ui_is_due = $this->isFollowUpDue($seguimiento);
            $seguimiento->ui_due_message = $this->resolveDueMessage($seguimiento);

            return $seguimiento;
        });

        $stats = [
            'total' => Seguimiento::count(),
            'activos' => Seguimiento::where('estado', 'activo')->count(),
            'pendientes' => Seguimiento::where('estado', 'activo')
                ->where(function ($query) use ($today) {
                    $query->whereNull('fecha_proximo_control')
                        ->orWhereDate('fecha_proximo_control', '>', $today);
                })
                ->count(),
            'proximos' => Seguimiento::where('estado', '!=', 'cerrado')
                ->whereNotNull('fecha_proximo_control')
                ->whereDate('fecha_proximo_control', '>=', $today)
                ->whereDate('fecha_proximo_control', '<=', $weekAhead)
                ->count(),
            'controlados' => Seguimiento::where('estado', 'controlado')->count(),
            'cerrados' => Seguimiento::where('estado', 'cerrado')->count(),
            'hoy' => Seguimiento::where('estado', '!=', 'cerrado')
                ->whereNotNull('fecha_proximo_control')
                ->whereDate('fecha_proximo_control', $today)
                ->count(),
            'vencidos' => Seguimiento::where('estado', '!=', 'cerrado')
                ->whereNotNull('fecha_proximo_control')
                ->whereDate('fecha_proximo_control', '<', $today)
                ->count(),
            'preventivos' => Seguimiento::where('tipo', 'preventivo')->count(),
            'clinicos' => Seguimiento::where('tipo', 'clinico')->count(),
            'terapeuticos' => Seguimiento::where('tipo', 'terapeutico')->count(),
        ];

        $historiaCatalogo = HistoriaClinica::with('mascota.cliente')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $veterinarios = Veterinarios::with('user:id,name')
            ->orderBy('id')
            ->get();

        $selectedMascota = filled($mascotaId)
            ? Mascotas::with('cliente')->find($mascotaId)
            : null;

        $prefillHistoriaId = $historiaCatalogo->contains('id', (int) $historiaClinicaId)
            ? (int) $historiaClinicaId
            : null;

        $shouldOpenCreate = $request->boolean('open_create');

        return view('seguimientos.index', compact(
            'seguimientos',
            'stats',
            'historiaCatalogo',
            'veterinarios',
            'selectedMascota',
            'prefillHistoriaId',
            'shouldOpenCreate',
            'defaultControlTime',
            'controlAlertDays'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSeguimiento($request);
        $seguimiento = Seguimiento::create($validated);
        $this->clinicalAttentionService->syncAppointmentForSeguimiento($seguimiento);

        return redirect()->route('seguimientos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Control registrado y cita de retorno sincronizada.',
        ]);
    }

    public function update(Request $request, Seguimiento $seguimiento)
    {
        $validated = $this->validateSeguimiento($request, $seguimiento);
        $seguimiento->update($validated);
        $this->clinicalAttentionService->syncAppointmentForSeguimiento($seguimiento);

        return redirect()->route('seguimientos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Control actualizado y agenda sincronizada correctamente.',
        ]);
    }

    public function cerrar(Request $request, Seguimiento $seguimiento)
    {
        $validated = Validator::make($request->all(), [
            'evolucion_cierre' => 'nullable|string|max:1000',
        ])->validate();

        $this->clinicalAttentionService->deletePendingAppointmentForSeguimiento($seguimiento);

        $seguimiento->update([
            'estado' => 'cerrado',
            'evolucion' => filled($validated['evolucion_cierre'] ?? null)
                ? trim((string) $validated['evolucion_cierre'])
                : ($seguimiento->evolucion ?: 'Control cerrado sin nuevo retorno pendiente.'),
        ]);

        return redirect()->route('seguimientos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Control cerrado y agenda sincronizada correctamente.',
        ]);
    }

    public function aplicarVacuna(Request $request, Seguimiento $seguimiento)
    {
        if (!$this->isFollowUpDue($seguimiento)) {
            return redirect()->route('seguimientos.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Esta vacuna aún no llega a su fecha y hora programada. El sistema la mantiene protegida para evitar registros adelantados.',
            ]);
        }

        $validated = Validator::make($request->all(), [
            'apply_seguimiento_id' => 'nullable|integer',
            'fecha_aplicacion' => 'required|date',
            'proxima_dosis' => 'nullable|date|after_or_equal:fecha_aplicacion',
            'evolucion' => 'nullable|string|max:1000',
        ], [
            'fecha_aplicacion.required' => 'Indica la fecha de aplicación de la vacuna.',
            'proxima_dosis.after_or_equal' => 'La próxima dosis no puede ser anterior a la aplicación.',
        ])->validateWithBag('aplicarVacuna');

        $vacuna = $this->clinicalAttentionService->applyScheduledVaccinationFromFollowUp($seguimiento, $validated);

        if (!$vacuna) {
            return redirect()->route('seguimientos.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Este control no tiene una vacuna programada pendiente para aplicar.',
            ]);
        }

        return redirect()->route('seguimientos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Vacuna aplicada, historial actualizado y control sincronizado correctamente.',
        ]);
    }

    public function destroy(Seguimiento $seguimiento)
    {
        if ($seguimiento->origen !== 'manual') {
            return redirect()->route('seguimientos.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Los controles automáticos se cierran, no se eliminan, para conservar trazabilidad clínica.',
            ]);
        }

        $this->clinicalAttentionService->deletePendingAppointmentForSeguimiento($seguimiento);
        $seguimiento->delete();

        return redirect()->route('seguimientos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Control manual eliminado correctamente.',
        ]);
    }

    private function validateSeguimiento(Request $request, ?Seguimiento $seguimiento = null): array
    {
        $validator = Validator::make($request->all(), [
            'historia_clinica_id' => 'required|exists:historias_clinicas,id',
            'veterinario_id' => 'nullable|exists:veterinarios,id',
            'tipo' => 'nullable|in:clinico,preventivo,terapeutico',
            'origen' => 'nullable|in:atencion,vacuna,tratamiento,manual',
            'titulo' => 'nullable|string|max:255',
            'estado' => 'required|in:activo,controlado,cerrado',
            'motivo' => 'required|string',
            'notas' => 'nullable|string',
            'evolucion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_proximo_control' => 'nullable|date|after_or_equal:fecha_inicio',
            'hora_proximo_control' => 'nullable|date_format:H:i',
            'dias_retorno' => 'nullable|integer|min:1|max:365',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('fecha_proximo_control') && !$request->filled('dias_retorno')) {
                $validator->errors()->add('fecha_proximo_control', 'Indica cuándo debe volver el paciente: fecha exacta o días de retorno.');
            }
        });

        $validated = $validator->validateWithBag('seguimientoStore');
        $historia = HistoriaClinica::with('mascota')->findOrFail($validated['historia_clinica_id']);

        if (!filled($validated['fecha_proximo_control'] ?? null) && filled($validated['dias_retorno'] ?? null)) {
            $validated['fecha_proximo_control'] = now()
                ->parse($validated['fecha_inicio'])
                ->addDays((int) $validated['dias_retorno'])
                ->toDateString();
        }

        $validated['mascota_id'] = $historia->mascota_id;
        $validated['veterinario_id'] = $validated['veterinario_id'] ?? $this->resolveVeterinarioId($seguimiento?->veterinario_id);
        $validated['tipo'] = $validated['tipo'] ?? $seguimiento?->tipo ?? 'clinico';
        $validated['origen'] = $validated['origen'] ?? $seguimiento?->origen ?? 'manual';
        $validated['titulo'] = filled($validated['titulo'] ?? null)
            ? trim((string) $validated['titulo'])
            : $this->defaultTitleForType($validated['tipo']);
        $validated['hora_proximo_control'] = $validated['hora_proximo_control']
            ?? $seguimiento?->hora_proximo_control
            ?? ConfiguracionSistema::valor('default_control_time', '09:00');

        return $validated;
    }

    private function defaultTitleForType(string $type): string
    {
        return match ($type) {
            'preventivo' => 'Vacuna pendiente',
            'terapeutico' => 'Control de tratamiento',
            default => 'Control clínico',
        };
    }

    private function resolveFollowUpType(Seguimiento $seguimiento): string
    {
        return $seguimiento->tipo ?: 'clinico';
    }

    private function resolveFollowUpTypeLabel(string $type): string
    {
        return match ($type) {
            'preventivo' => 'Vacuna pendiente',
            'terapeutico' => 'Control de tratamiento',
            default => 'Control clínico',
        };
    }

    private function resolveOriginLabel(Seguimiento $seguimiento): string
    {
        return match ($seguimiento->origen) {
            'vacuna' => 'Generado por vacuna',
            'tratamiento' => 'Generado por tratamiento',
            'manual' => 'Registro manual',
            default => 'Generado por atención',
        };
    }

    private function resolveBucket(Seguimiento $seguimiento, string $today, string $weekAhead): string
    {
        if ($seguimiento->estado === 'cerrado') {
            return 'cerrado';
        }

        $controlDate = optional($seguimiento->fecha_proximo_control)?->format('Y-m-d');

        if (!$controlDate) {
            return $seguimiento->estado === 'controlado' ? 'en_control' : 'pendiente';
        }

        if ($controlDate < $today) {
            return 'vencido';
        }

        if ($controlDate === $today) {
            return 'hoy';
        }

        if ($controlDate <= $weekAhead) {
            return 'proximo';
        }

        return $seguimiento->estado === 'controlado' ? 'en_control' : 'pendiente';
    }

    private function resolveBucketLabel(string $bucket): string
    {
        return match ($bucket) {
            'vencido' => 'Vencido',
            'hoy' => 'Para hoy',
            'proximo' => 'Próximo',
            'en_control' => 'Atendido',
            'cerrado' => 'Cerrado',
            default => 'Pendiente',
        };
    }

    private function resolveBucketTone(string $bucket): string
    {
        return match ($bucket) {
            'vencido' => 'rose',
            'hoy' => 'amber',
            'proximo' => 'blue',
            'en_control' => 'emerald',
            'cerrado' => 'slate',
            default => 'violet',
        };
    }

    private function isFollowUpDue(Seguimiento $seguimiento): bool
    {
        if ($seguimiento->estado !== 'activo' || !$seguimiento->fecha_proximo_control) {
            return false;
        }

        $date = $seguimiento->fecha_proximo_control->format('Y-m-d');
        $hour = $seguimiento->hora_proximo_control
            ? substr((string) $seguimiento->hora_proximo_control, 0, 5)
            : (string) ConfiguracionSistema::valor('default_control_time', '09:00');

        return Carbon::parse($date . ' ' . $hour)->lte(now());
    }

    private function resolveDueMessage(Seguimiento $seguimiento): string
    {
        if ($seguimiento->estado === 'cerrado') {
            return 'Control cerrado.';
        }

        if ($seguimiento->estado === 'controlado') {
            return 'Evolución registrada.';
        }

        if (!$seguimiento->fecha_proximo_control) {
            return 'Sin fecha de retorno definida.';
        }

        return $this->isFollowUpDue($seguimiento)
            ? 'Listo para atender.'
            : 'Se habilita en la fecha y hora programada.';
    }

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
