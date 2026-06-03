<?php

namespace App\Services;

use App\Models\Citas;
use App\Models\ConfiguracionSistema;
use App\Models\HistoriaClinica;
use App\Models\Receta;
use App\Models\Seguimiento;
use App\Models\Tratamiento;
use App\Models\Vacuna;
use App\Models\Veterinarios;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ClinicalAttentionService
{
    public function register(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            $historia = $this->persistHistoria($payload);

            $vacuna = $this->persistVacuna($historia, $payload['vacuna'] ?? []);
            $tratamiento = $this->persistTratamiento($historia, $payload['tratamiento'] ?? [], $payload['veterinario_id'] ?? null);
            $receta = $this->persistReceta($historia, $payload['receta'] ?? []);
            $seguimientoClinico = $this->persistClinicalFollowUp($historia, $payload['seguimiento'] ?? [], $payload['veterinario_id'] ?? null);
            $seguimientoPreventivo = $this->syncPreventiveFollowUp($historia, $vacuna, $payload['veterinario_id'] ?? null, Arr::get($payload, 'vacuna.proxima_dosis'));
            $seguimientoTerapeutico = $this->syncTherapeuticFollowUp($historia, $tratamiento, $payload['veterinario_id'] ?? null);

            if (!empty($payload['complete_cita']) && !empty($payload['cita_id'])) {
                $this->markLinkedFollowUpsAsControlled((int) $payload['cita_id'], $payload);

                Citas::whereKey($payload['cita_id'])->update([
                    'estado' => 'completada',
                    'veterinario_id' => $payload['veterinario_id'] ?? null,
                ]);
            }

            return compact('historia', 'vacuna', 'tratamiento', 'receta', 'seguimientoClinico', 'seguimientoPreventivo', 'seguimientoTerapeutico');
        });
    }

    public function registerPreventiveVaccination(array $payload): array
    {
        $payload['tipo_atencion'] = $payload['tipo_atencion'] ?? 'vacunacion';
        $payload['origen_atencion'] = $payload['origen_atencion'] ?? 'preventiva';

        if (blank($payload['diagnostico'] ?? null) && blank($payload['observaciones'] ?? null)) {
            $vacunaNombre = trim((string) Arr::get($payload, 'vacuna.nombre'));

            $payload['diagnostico'] = 'Vacunación preventiva';
            $payload['observaciones'] = $vacunaNombre !== ''
                ? 'Aplicación preventiva de vacuna ' . $vacunaNombre . '.'
                : 'Aplicación preventiva registrada desde el módulo de vacunas.';
        }

        return $this->register($payload);
    }

    public function syncNextVaccinationSchedule(Vacuna $vacunaAplicada, ?string $fechaProgramada): ?Vacuna
    {
        return $this->syncScheduledNextVaccination($vacunaAplicada, $fechaProgramada);
    }

    public function syncAppointmentForSeguimiento(Seguimiento $seguimiento): ?Citas
    {
        $seguimiento->loadMissing(['cita.historiaClinica', 'historiaClinica']);

        $fechaControl = optional($seguimiento->fecha_proximo_control)->format('Y-m-d');
        $horaControl = $this->normalizeHour($seguimiento->hora_proximo_control) ?? $this->defaultControlHour();
        $veterinarioId = $this->resolveAppointmentVeterinarioId($seguimiento->veterinario_id);
        $currentAppointment = $seguimiento->cita;

        if ($seguimiento->estado !== 'activo' || !$fechaControl || !$veterinarioId) {
            $this->deletePendingAppointmentForSeguimiento($seguimiento);

            return null;
        }

        $horaControl = $this->resolveAvailableAppointmentHour(
            $fechaControl,
            $horaControl,
            $veterinarioId,
            $seguimiento->mascota_id,
            $currentAppointment?->id
        );
        $hasOtherFollowUps = $currentAppointment
            ? Seguimiento::where('cita_id', $currentAppointment->id)->whereKeyNot($seguimiento->id)->exists()
            : false;

        if ($currentAppointment && !$hasOtherFollowUps && $this->canReuseAppointment($currentAppointment)) {
            $currentAppointment->update([
                'mascota_id' => $seguimiento->mascota_id,
                'veterinario_id' => $veterinarioId,
                'fecha' => $fechaControl,
                'hora' => $horaControl,
                'estado' => 'pendiente',
            ]);

            $seguimiento->forceFill(['hora_proximo_control' => $horaControl])->saveQuietly();

            return $currentAppointment->refresh();
        }

        if ($currentAppointment && !$hasOtherFollowUps) {
            $this->deletePendingAppointmentForSeguimiento($seguimiento);
        }

        $appointment = Citas::query()
            ->where('mascota_id', $seguimiento->mascota_id)
            ->whereDate('fecha', $fechaControl)
            ->where('hora', $horaControl)
            ->where('estado', 'pendiente')
            ->whereDoesntHave('historiaClinica')
            ->first();

        if (!$appointment) {
            $appointment = Citas::create([
                'mascota_id' => $seguimiento->mascota_id,
                'veterinario_id' => $veterinarioId,
                'fecha' => $fechaControl,
                'hora' => $horaControl,
                'estado' => 'pendiente',
            ]);
        }

        $seguimiento->forceFill([
            'cita_id' => $appointment->id,
            'hora_proximo_control' => $horaControl,
        ])->saveQuietly();

        return $appointment;
    }

    public function syncTherapeuticFollowUpFromTreatment(Tratamiento $tratamiento): ?Seguimiento
    {
        $tratamiento->loadMissing('historiaClinica');

        if (!$tratamiento->historiaClinica) {
            return null;
        }

        return $this->syncTherapeuticFollowUp(
            $tratamiento->historiaClinica,
            $tratamiento,
            $tratamiento->veterinario_id
        );
    }

    public function syncPreventiveFollowUpFromVaccination(Vacuna $vacuna, ?string $fechaProgramada, ?int $veterinarioId = null): ?Seguimiento
    {
        $vacuna->loadMissing('historiaClinica');

        if (!$vacuna->historiaClinica) {
            return null;
        }

        return $this->syncPreventiveFollowUp(
            $vacuna->historiaClinica,
            $vacuna,
            $veterinarioId,
            $fechaProgramada
        );
    }

    public function syncScheduledVaccinationFollowUp(Vacuna $vacuna, ?int $veterinarioId = null): ?Seguimiento
    {
        $fechaProgramada = optional($vacuna->fecha_programada)->format('Y-m-d');

        if ($vacuna->estado_aplicacion !== 'programada' || !$fechaProgramada) {
            $this->deleteScheduledVaccinationFollowUp($vacuna);

            return null;
        }

        $seguimiento = Seguimiento::firstOrNew(['vacuna_id' => $vacuna->id]);
        $seguimiento->fill([
            'mascota_id' => $vacuna->mascota_id,
            'historia_clinica_id' => null,
            'veterinario_id' => $this->resolveAppointmentVeterinarioId($veterinarioId),
            'tipo' => 'preventivo',
            'origen' => 'vacuna',
            'titulo' => 'Vacuna programada',
            'estado' => 'activo',
            'motivo' => 'Aplicar vacuna ' . $vacuna->nombre . '.',
            'notas' => 'Control creado automáticamente al programar la vacuna.',
            'evolucion' => null,
            'fecha_inicio' => now()->toDateString(),
            'fecha_proximo_control' => $fechaProgramada,
            'hora_proximo_control' => $seguimiento->hora_proximo_control ?: $this->defaultControlHour(),
            'dias_retorno' => null,
        ]);
        $seguimiento->save();

        $this->syncAppointmentForSeguimiento($seguimiento);

        return $seguimiento->refresh();
    }

    public function completeScheduledVaccinationFollowUp(Vacuna $vacuna, ?string $evolucion = null): void
    {
        Seguimiento::where('vacuna_id', $vacuna->id)
            ->where('estado', 'activo')
            ->get()
            ->each(function (Seguimiento $seguimiento) use ($vacuna, $evolucion) {
                $this->deletePendingAppointmentForSeguimiento($seguimiento);

                $seguimiento->update([
                    'historia_clinica_id' => $vacuna->historia_clinica_id ?: $seguimiento->historia_clinica_id,
                    'estado' => 'controlado',
                    'evolucion' => $evolucion ?: 'Vacuna aplicada y control de retorno atendido.',
                ]);
            });
    }

    public function applyScheduledVaccinationFromFollowUp(Seguimiento $seguimiento, array $payload): ?Vacuna
    {
        return DB::transaction(function () use ($seguimiento, $payload) {
            $seguimiento->loadMissing(['vacuna', 'cita', 'mascota.cliente']);
            $vacuna = $seguimiento->vacuna;

            if (
                !$vacuna ||
                $seguimiento->tipo !== 'preventivo' ||
                $seguimiento->origen !== 'vacuna' ||
                $vacuna->estado_aplicacion !== 'programada'
            ) {
                return null;
            }

            $fechaAplicacion = $payload['fecha_aplicacion'] ?? now()->toDateString();
            $veterinarioId = $this->resolveAppointmentVeterinarioId($seguimiento->veterinario_id);
            $appointment = $seguimiento->cita;

            $historia = $appointment
                ? HistoriaClinica::where('cita_id', $appointment->id)->first()
                : null;

            $historyData = [
                'mascota_id' => $vacuna->mascota_id,
                'cita_id' => $appointment?->id,
                'origen_atencion' => 'preventiva',
                'tipo_atencion' => 'vacunacion',
                'fecha' => $fechaAplicacion,
                'diagnostico' => 'Vacunación preventiva',
                'observaciones' => 'Aplicación de vacuna ' . $vacuna->nombre . ' registrada desde controles de retorno.',
            ];

            $historia = $historia
                ? tap($historia)->update($historyData)
                : HistoriaClinica::create($historyData);

            $vacuna->update([
                'historia_clinica_id' => $historia->id,
                'estado_aplicacion' => 'aplicada',
                'fecha_programada' => null,
                'fecha_aplicacion' => $fechaAplicacion,
                'proxima_dosis' => null,
            ]);

            if ($appointment) {
                $appointment->update([
                    'estado' => 'completada',
                    'veterinario_id' => $veterinarioId,
                ]);
            }

            $seguimiento->update([
                'historia_clinica_id' => $historia->id,
                'veterinario_id' => $veterinarioId,
                'estado' => 'controlado',
                'evolucion' => $this->nullableTrim($payload['evolucion'] ?? null)
                    ?: 'Vacuna aplicada y control preventivo atendido.',
            ]);

            $nextScheduled = $this->syncNextVaccinationSchedule($vacuna->refresh(), $payload['proxima_dosis'] ?? null);

            if ($nextScheduled) {
                $this->syncScheduledVaccinationFollowUp($nextScheduled, $veterinarioId);
            }

            return $vacuna->refresh();
        });
    }

    public function deleteScheduledVaccinationFollowUp(Vacuna $vacuna): void
    {
        Seguimiento::where('vacuna_id', $vacuna->id)
            ->get()
            ->each(function (Seguimiento $seguimiento) {
                $this->deletePendingAppointmentForSeguimiento($seguimiento);
                $seguimiento->delete();
            });
    }

    public function resyncTherapeuticFollowUpForHistory(HistoriaClinica $historia, ?int $veterinarioId = null): ?Seguimiento
    {
        $nextTreatment = $historia->tratamientos()
            ->whereNotNull('proximo_control')
            ->orderBy('proximo_control')
            ->orderByDesc('id')
            ->first();

        return $this->syncTherapeuticFollowUp(
            $historia,
            $nextTreatment,
            $veterinarioId ?: $nextTreatment?->veterinario_id
        );
    }

    public function deletePendingAppointmentForSeguimiento(Seguimiento $seguimiento): void
    {
        $seguimiento->loadMissing('cita.historiaClinica');
        $appointment = $seguimiento->cita;

        if (!$appointment) {
            return;
        }

        $hasOtherFollowUps = Seguimiento::where('cita_id', $appointment->id)
            ->whereKeyNot($seguimiento->id)
            ->exists();

        $seguimiento->forceFill(['cita_id' => null])->saveQuietly();

        if (!$hasOtherFollowUps && $this->canReuseAppointment($appointment)) {
            $appointment->delete();
        }
    }

    private function persistHistoria(array $payload): HistoriaClinica
    {
        $historiaData = [
            'mascota_id' => $payload['mascota_id'],
            'cita_id' => $payload['cita_id'] ?? null,
            'origen_atencion' => $payload['origen_atencion'] ?? 'manual',
            'tipo_atencion' => $payload['tipo_atencion'] ?? 'consulta',
            'fecha' => $payload['fecha'],
            'diagnostico' => $this->nullableTrim($payload['diagnostico'] ?? null),
            'observaciones' => $this->nullableTrim($payload['observaciones'] ?? null),
            'peso' => $payload['peso'] ?? null,
            'temperatura' => $payload['temperatura'] ?? null,
            'servicio_producto_id' => Arr::get($payload, 'servicio.producto_id'),
            'precio_servicio' => Arr::get($payload, 'servicio.precio'),
        ];

        if (!empty($payload['cita_id'])) {
            return HistoriaClinica::updateOrCreate(
                ['cita_id' => $payload['cita_id']],
                $historiaData
            );
        }

        return HistoriaClinica::create($historiaData);
    }

    private function persistVacuna(HistoriaClinica $historia, array $payload): ?Vacuna
    {
        $nombre = $this->nullableTrim($payload['nombre'] ?? null);
        $fechaAplicacion = $payload['fecha_aplicacion'] ?? null;
        $proximaDosis = $payload['proxima_dosis'] ?? null;

        if (!$nombre || !$fechaAplicacion) {
            return null;
        }

        $scheduledVaccination = $this->resolveScheduledVaccinationForAttention($historia, $nombre);

        if ($scheduledVaccination) {
            $scheduledVaccination->update([
                'historia_clinica_id' => $historia->id,
                'estado_aplicacion' => 'aplicada',
                'fecha_programada' => null,
                'fecha_aplicacion' => $fechaAplicacion,
                'proxima_dosis' => null,
            ]);

            $vacuna = $scheduledVaccination->refresh();
            $this->completeScheduledVaccinationFollowUp($vacuna, 'Vacuna aplicada durante la atención programada.');
        } else {
            $vacuna = Vacuna::updateOrCreate([
                'mascota_id' => $historia->mascota_id,
                'nombre' => $nombre,
                'fecha_aplicacion' => $fechaAplicacion,
            ], [
                'historia_clinica_id' => $historia->id,
                'estado_aplicacion' => 'aplicada',
                'fecha_programada' => null,
                'proxima_dosis' => null,
            ]);
        }

        $this->syncNextVaccinationSchedule($vacuna, $proximaDosis);

        return $vacuna;
    }

    private function syncScheduledNextVaccination(Vacuna $vacunaAplicada, ?string $fechaProgramada): ?Vacuna
    {
        $fechaProgramada = $this->nullableTrim($fechaProgramada);

        $pendingQuery = Vacuna::query()
            ->where('mascota_id', $vacunaAplicada->mascota_id)
            ->where('nombre', $vacunaAplicada->nombre)
            ->where('estado_aplicacion', 'programada')
            ->whereNull('historia_clinica_id')
            ->whereDate('fecha_programada', '>=', $vacunaAplicada->fecha_aplicacion);

        if (!$fechaProgramada) {
            $pendingVaccines = (clone $pendingQuery)->orderBy('fecha_programada')->get();

            if ($pendingVaccines->count() === 1) {
                $pendingVaccination = $pendingVaccines->first();
                $this->deleteScheduledVaccinationFollowUp($pendingVaccination);
                $pendingVaccination->delete();
            }

            return null;
        }

        $exactMatch = (clone $pendingQuery)
            ->whereDate('fecha_programada', $fechaProgramada)
            ->first();

        if ($exactMatch) {
            $exactMatch->update([
                'fecha_aplicacion' => null,
                'proxima_dosis' => null,
            ]);

            return $exactMatch->refresh();
        }

        $pendingVaccines = (clone $pendingQuery)->orderBy('fecha_programada')->get();

        if ($pendingVaccines->count() === 1) {
            $pendingVaccines->first()->update([
                'fecha_programada' => $fechaProgramada,
                'fecha_aplicacion' => null,
                'proxima_dosis' => null,
            ]);

            return $pendingVaccines->first()->refresh();
        }

        return Vacuna::create([
            'mascota_id' => $vacunaAplicada->mascota_id,
            'historia_clinica_id' => null,
            'nombre' => $vacunaAplicada->nombre,
            'estado_aplicacion' => 'programada',
            'fecha_programada' => $fechaProgramada,
            'fecha_aplicacion' => null,
            'proxima_dosis' => null,
        ]);
    }

    private function persistTratamiento(HistoriaClinica $historia, array $payload, ?int $veterinarioId): ?Tratamiento
    {
        $descripcion = $this->nullableTrim($payload['descripcion'] ?? null);
        $fechaInicio = $payload['fecha_inicio'] ?? null;

        if (!$descripcion || !$fechaInicio) {
            return null;
        }

        $tratamiento = $historia->tratamientos()->updateOrCreate(
            ['id' => $payload['id'] ?? null],
            [
                'veterinario_id' => $veterinarioId,
                'descripcion' => $descripcion,
                'costo' => $payload['costo'] ?? 0,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $payload['fecha_fin'] ?? null,
                'proximo_control' => $payload['proximo_control'] ?? $this->suggestNextControl($fechaInicio, $payload['fecha_fin'] ?? null),
            ]
        );

        if (array_key_exists('productos', $payload) && method_exists($tratamiento, 'productos')) {
            $syncData = collect($payload['productos'])
                ->filter(fn ($item) => !empty($item['producto_id']) && !empty($item['cantidad']))
                ->mapWithKeys(fn ($item) => [
                    (int) $item['producto_id'] => ['cantidad' => (int) $item['cantidad']],
                ])
                ->all();

            $tratamiento->productos()->sync($syncData);
        }

        return $tratamiento;
    }

    private function persistReceta(HistoriaClinica $historia, array $payload): ?Receta
    {
        $medicamentos = $this->nullableTrim($payload['medicamentos'] ?? null);
        $indicaciones = $this->nullableTrim($payload['indicaciones'] ?? null);

        if (!$medicamentos || !$indicaciones) {
            return null;
        }

        return $historia->recetas()->updateOrCreate(
            ['id' => $payload['id'] ?? null],
            [
                'medicamentos' => $medicamentos,
                'indicaciones' => $indicaciones,
            ]
        );
    }

    private function persistClinicalFollowUp(HistoriaClinica $historia, array $payload, ?int $veterinarioId): ?Seguimiento
    {
        $titulo = $this->nullableTrim($payload['titulo'] ?? null);
        $motivo = $this->nullableTrim($payload['motivo'] ?? null);
        $notas = $this->nullableTrim($payload['notas'] ?? null);
        $evolucion = $this->nullableTrim($payload['evolucion'] ?? null);
        $diasRetorno = Arr::get($payload, 'dias_retorno');
        $seguimientoId = $payload['id'] ?? null;
        $fechaInicio = $payload['fecha_inicio'] ?? optional($historia->fecha)->format('Y-m-d') ?? now()->toDateString();
        $fechaProximoControl = $this->resolveFollowUpDate($payload, $fechaInicio);
        $horaProximoControl = $this->normalizeHour($payload['hora_proximo_control'] ?? null);

        if (!$titulo && !$motivo && !$notas && !$evolucion && !$fechaProximoControl && !$diasRetorno) {
            $this->resolveClinicalFollowUpQuery($historia, $seguimientoId)
                ->get()
                ->each(function (Seguimiento $seguimiento) {
                    $this->deletePendingAppointmentForSeguimiento($seguimiento);
                    $seguimiento->delete();
                });

            return null;
        }

        $match = $seguimientoId
            ? ['id' => $seguimientoId]
            : [
                'historia_clinica_id' => $historia->id,
                'tipo' => 'clinico',
                'origen' => 'atencion',
            ];

        $seguimiento = $historia->seguimientos()->updateOrCreate(
            $match,
            [
                'mascota_id' => $historia->mascota_id,
                'veterinario_id' => $veterinarioId,
                'tipo' => 'clinico',
                'origen' => 'atencion',
                'titulo' => $titulo ?: 'Control médico',
                'estado' => $payload['estado'] ?? 'activo',
                'motivo' => $motivo,
                'notas' => $notas,
                'evolucion' => $evolucion,
                'fecha_inicio' => $fechaInicio,
                'fecha_proximo_control' => $fechaProximoControl,
                'hora_proximo_control' => $horaProximoControl,
                'dias_retorno' => $diasRetorno ?: null,
            ]
        );

        $this->syncAppointmentForSeguimiento($seguimiento);

        return $seguimiento;
    }

    private function resolveClinicalFollowUpQuery(HistoriaClinica $historia, ?int $seguimientoId)
    {
        $query = Seguimiento::query()->where('tipo', 'clinico')->where('origen', 'atencion');

        if ($seguimientoId) {
            return $query->whereKey($seguimientoId);
        }

        return $query->where('historia_clinica_id', $historia->id);
    }

    private function syncPreventiveFollowUp(HistoriaClinica $historia, ?Vacuna $vacuna, ?int $veterinarioId, ?string $fechaProgramada): ?Seguimiento
    {
        $existing = $historia->seguimientos()
            ->where('tipo', 'preventivo')
            ->where('origen', 'vacuna')
            ->first();
        $fechaProgramada = $this->nullableTrim($fechaProgramada);
        $scheduledVaccination = $vacuna && $fechaProgramada
            ? $this->findScheduledVaccinationFor($vacuna, $fechaProgramada)
            : null;

        if (!$vacuna || !$fechaProgramada) {
            if ($existing) {
                $this->deletePendingAppointmentForSeguimiento($existing);
                $existing->delete();
            }

            return null;
        }

        $match = $existing
            ? ['id' => $existing->id]
            : ($scheduledVaccination
                ? ['vacuna_id' => $scheduledVaccination->id]
                : [
                    'historia_clinica_id' => $historia->id,
                    'tipo' => 'preventivo',
                    'origen' => 'vacuna',
                ]);

        $seguimiento = Seguimiento::updateOrCreate(
            $match,
            [
                'mascota_id' => $historia->mascota_id,
                'historia_clinica_id' => $historia->id,
                'vacuna_id' => $scheduledVaccination?->id,
                'veterinario_id' => $veterinarioId,
                'tipo' => 'preventivo',
                'origen' => 'vacuna',
                'titulo' => 'Próxima vacuna',
                'estado' => 'activo',
                'motivo' => 'Aplicar la siguiente dosis de ' . $vacuna->nombre . '.',
                'notas' => 'Control automático creado desde vacunación preventiva.',
                'evolucion' => null,
                'fecha_inicio' => optional($historia->fecha)->format('Y-m-d') ?? now()->toDateString(),
                'fecha_proximo_control' => $fechaProgramada,
                'hora_proximo_control' => $this->defaultControlHour(),
                'dias_retorno' => null,
            ]
        );

        $this->syncAppointmentForSeguimiento($seguimiento);

        return $seguimiento;
    }

    private function syncTherapeuticFollowUp(HistoriaClinica $historia, ?Tratamiento $tratamiento, ?int $veterinarioId): ?Seguimiento
    {
        $existing = $historia->seguimientos()
            ->where('tipo', 'terapeutico')
            ->where('origen', 'tratamiento')
            ->first();

        if (!$tratamiento || !$tratamiento->proximo_control) {
            if ($existing) {
                $this->deletePendingAppointmentForSeguimiento($existing);
                $existing->delete();
            }

            return null;
        }

        $seguimiento = $historia->seguimientos()->updateOrCreate(
            [
                'historia_clinica_id' => $historia->id,
                'tipo' => 'terapeutico',
                'origen' => 'tratamiento',
            ],
            [
                'mascota_id' => $historia->mascota_id,
                'veterinario_id' => $veterinarioId,
                'titulo' => 'Revisión de tratamiento',
                'estado' => 'activo',
                'motivo' => 'Revisar cómo responde al tratamiento indicado.',
                'notas' => $tratamiento->descripcion,
                'evolucion' => null,
                'fecha_inicio' => optional($tratamiento->fecha_inicio)->format('Y-m-d') ?? optional($historia->fecha)->format('Y-m-d') ?? now()->toDateString(),
                'fecha_proximo_control' => optional($tratamiento->proximo_control)->format('Y-m-d'),
                'hora_proximo_control' => $this->defaultControlHour(),
                'dias_retorno' => null,
            ]
        );

        $this->syncAppointmentForSeguimiento($seguimiento);

        return $seguimiento;
    }

    private function markLinkedFollowUpsAsControlled(int $citaId, array $payload): void
    {
        $evolucion = $this->nullableTrim($payload['diagnostico'] ?? null)
            ?: $this->nullableTrim($payload['observaciones'] ?? null)
            ?: 'Paciente controlado en cita de retorno.';

        Seguimiento::where('cita_id', $citaId)
            ->where('estado', 'activo')
            ->get()
            ->each(function (Seguimiento $seguimiento) use ($evolucion) {
                $seguimiento->update([
                    'estado' => 'controlado',
                    'evolucion' => $seguimiento->evolucion ?: $evolucion,
                ]);
            });
    }

    private function resolveScheduledVaccinationForAttention(HistoriaClinica $historia, string $nombre): ?Vacuna
    {
        if (!$historia->cita_id) {
            return null;
        }

        $linkedFollowUp = Seguimiento::with('vacuna')
            ->where('cita_id', $historia->cita_id)
            ->where('tipo', 'preventivo')
            ->where('origen', 'vacuna')
            ->get()
            ->first(fn (Seguimiento $seguimiento) => $seguimiento->vacuna
                && $seguimiento->vacuna->estado_aplicacion === 'programada'
                && $this->sameVaccineName($seguimiento->vacuna->nombre, $nombre));

        if ($linkedFollowUp?->vacuna) {
            return $linkedFollowUp->vacuna;
        }

        return Vacuna::query()
            ->where('mascota_id', $historia->mascota_id)
            ->where('estado_aplicacion', 'programada')
            ->whereNull('historia_clinica_id')
            ->where('nombre', $nombre)
            ->when($historia->fecha, fn ($query) => $query->whereDate('fecha_programada', $historia->fecha))
            ->orderBy('fecha_programada')
            ->first();
    }

    private function findScheduledVaccinationFor(Vacuna $vacunaAplicada, string $fechaProgramada): ?Vacuna
    {
        return Vacuna::query()
            ->where('mascota_id', $vacunaAplicada->mascota_id)
            ->where('nombre', $vacunaAplicada->nombre)
            ->where('estado_aplicacion', 'programada')
            ->whereNull('historia_clinica_id')
            ->whereDate('fecha_programada', $fechaProgramada)
            ->orderByDesc('id')
            ->first();
    }

    private function sameVaccineName(?string $left, ?string $right): bool
    {
        return mb_strtolower(trim((string) $left)) === mb_strtolower(trim((string) $right));
    }

    private function resolveFollowUpDate(array $payload, string $fechaInicio): ?string
    {
        $fechaProximoControl = $this->nullableTrim($payload['fecha_proximo_control'] ?? null);
        $diasRetorno = Arr::get($payload, 'dias_retorno');

        if ($fechaProximoControl) {
            return $fechaProximoControl;
        }

        if (!$diasRetorno) {
            return null;
        }

        return now()->parse($fechaInicio)->addDays((int) $diasRetorno)->toDateString();
    }

    private function normalizeHour(?string $hour): ?string
    {
        $hour = trim((string) $hour);

        if ($hour === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}/', $hour)) {
            return substr($hour, 0, 5);
        }

        return null;
    }

    private function defaultControlHour(): string
    {
        return $this->normalizeHour((string) ConfiguracionSistema::valor('default_control_time', '09:00')) ?? '09:00';
    }

    private function canReuseAppointment(Citas $appointment): bool
    {
        return $appointment->estado === 'pendiente' && !$appointment->historiaClinica()->exists();
    }

    private function resolveAppointmentVeterinarioId(?int $veterinarioId): ?int
    {
        if ($veterinarioId && Veterinarios::whereKey($veterinarioId)->exists()) {
            return $veterinarioId;
        }

        return Veterinarios::query()->orderBy('id')->value('id');
    }

    private function resolveAvailableAppointmentHour(string $date, string $preferredHour, int $veterinarioId, int $mascotaId, ?int $ignoreCitaId = null): string
    {
        $slots = collect([$preferredHour])
            ->merge(collect(range(8, 18))->flatMap(fn ($hour) => [
                sprintf('%02d:00', $hour),
                sprintf('%02d:30', $hour),
            ]))
            ->unique()
            ->values();

        foreach ($slots as $slot) {
            $conflict = Citas::query()
                ->whereDate('fecha', $date)
                ->where('hora', $slot)
                ->where(function ($query) use ($veterinarioId, $mascotaId) {
                    $query->where('veterinario_id', $veterinarioId)
                        ->orWhere('mascota_id', $mascotaId);
                })
                ->when($ignoreCitaId, fn ($query) => $query->whereKeyNot($ignoreCitaId))
                ->where('estado', '!=', 'cancelada')
                ->exists();

            if (!$conflict) {
                return $slot;
            }
        }

        return $preferredHour;
    }

    private function suggestNextControl(?string $fechaInicio, ?string $fechaFin): ?string
    {
        if ($fechaFin) {
            return $fechaFin;
        }

        if (!$fechaInicio) {
            return null;
        }

        return now()->parse($fechaInicio)->addDays(7)->toDateString();
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}


