<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttentionFlowService
{
    public function validate(Request $request, string $errorBag, bool $requiresCita = false): array
    {
        $rules = [
            'tipo_atencion' => 'required|in:consulta,vacunacion,control,desparasitacion,servicio,otro',
            'historia_fecha' => 'required|date',
            'diagnostico' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'peso' => 'nullable|numeric|min:0|max:200',
            'temperatura' => 'nullable|numeric|min:30|max:45',
            'servicio_producto_id' => 'nullable|exists:productos,id',
            'precio_servicio' => 'nullable|numeric|min:0',
            'vacuna_nombre_select' => 'nullable|string|max:255',
            'vacuna_nombre_custom' => 'nullable|string|max:255',
            'vacuna_fecha_aplicacion' => 'nullable|date',
            'vacuna_proxima_dosis' => 'nullable|date|after_or_equal:vacuna_fecha_aplicacion',
            'tratamiento_descripcion' => 'nullable|string',
            'tratamiento_costo' => 'nullable|numeric|min:0',
            'tratamiento_fecha_inicio' => 'nullable|date',
            'tratamiento_fecha_fin' => 'nullable|date|after_or_equal:tratamiento_fecha_inicio',
            'receta_medicamentos' => 'nullable|string',
            'receta_indicaciones' => 'nullable|string',
            'requiere_seguimiento' => 'nullable|boolean',
            'seguimiento_motivo' => 'nullable|string',
            'seguimiento_notas' => 'nullable|string',
            'seguimiento_fecha_proximo_control' => 'nullable|date|after_or_equal:historia_fecha',
            'seguimiento_hora_proximo_control' => 'nullable|date_format:H:i',
            'seguimiento_dias_retorno' => 'nullable|integer|min:1|max:365',
        ];

        if ($requiresCita) {
            $rules['cita_id'] = 'required|exists:citas,id';
        } else {
            $rules['mascota_id'] = 'required|exists:mascotas,id';
            $rules['veterinario_id'] = 'nullable|exists:veterinarios,id';
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $tipoAtencion = $request->input('tipo_atencion', 'consulta');
            $diagnostico = trim((string) $request->input('diagnostico'));
            $observaciones = trim((string) $request->input('observaciones'));
            $vacunaNombre = $this->resolveVacunaNombre($request->input('vacuna_nombre_select'), $request->input('vacuna_nombre_custom'));
            $tratamientoDescripcion = trim((string) $request->input('tratamiento_descripcion'));
            $tratamientoCosto = (float) $request->input('tratamiento_costo', 0);
            $recetaMedicamentos = trim((string) $request->input('receta_medicamentos'));
            $recetaIndicaciones = trim((string) $request->input('receta_indicaciones'));
            $seguimientoMotivo = trim((string) $request->input('seguimiento_motivo'));
            $seguimientoNotas = trim((string) $request->input('seguimiento_notas'));
            $hasVacunaContent = $vacunaNombre !== ''
                || $request->filled('vacuna_proxima_dosis')
                || $tipoAtencion === 'vacunacion';
            $hasSeguimiento = $request->boolean('requiere_seguimiento')
                || $seguimientoMotivo !== ''
                || $seguimientoNotas !== ''
                || $request->filled('seguimiento_fecha_proximo_control')
                || $request->filled('seguimiento_dias_retorno');
            $esServicio = $tipoAtencion === 'servicio';
            $requiresDefaultClinicalText = in_array($tipoAtencion, ['vacunacion', 'control', 'desparasitacion'], true);

            if (
                $diagnostico === '' &&
                $observaciones === '' &&
                !$esServicio &&
                $vacunaNombre === '' &&
                $tratamientoDescripcion === '' &&
                $recetaMedicamentos === '' &&
                $recetaIndicaciones === '' &&
                !$hasSeguimiento &&
                !$requiresDefaultClinicalText
            ) {
                $validator->errors()->add('diagnostico', 'Registra al menos diagnóstico, observaciones, vacuna, tratamiento, receta o control.');
            }

            if ($esServicio) {
                if (!$request->filled('servicio_producto_id')) {
                    $validator->errors()->add('servicio_producto_id', 'Selecciona el servicio realizado.');
                }

                if (!$request->filled('precio_servicio')) {
                    $validator->errors()->add('precio_servicio', 'Indica el precio del servicio.');
                }
            }

            if ($vacunaNombre !== '' && !$request->filled('vacuna_fecha_aplicacion')) {
                $validator->errors()->add('vacuna_fecha_aplicacion', 'Indica la fecha de aplicación de la vacuna.');
            }

            if ($request->filled('vacuna_proxima_dosis') && $vacunaNombre === '') {
                $validator->errors()->add('vacuna_nombre_select', 'Selecciona la vacuna aplicada para programar la siguiente dosis.');
            }

            if ($request->filled('vacuna_proxima_dosis') && !$request->filled('vacuna_fecha_aplicacion')) {
                $validator->errors()->add('vacuna_fecha_aplicacion', 'Indica primero la fecha de aplicación.');
            }

            if ($hasVacunaContent && $tipoAtencion === 'vacunacion' && $vacunaNombre === '') {
                $validator->errors()->add('vacuna_nombre_select', 'Selecciona o escribe la vacuna aplicada.');
            }

            if ((string) $request->input('vacuna_nombre_select') === '__custom__' && $vacunaNombre === '') {
                $validator->errors()->add('vacuna_nombre_custom', 'Escribe el nombre de la vacuna.');
            }

            $hasTratamientoContent = $tratamientoDescripcion !== ''
                || $request->filled('tratamiento_fecha_fin')
                || $tratamientoCosto > 0;

            if ($hasTratamientoContent && $tratamientoDescripcion === '') {
                $validator->errors()->add('tratamiento_descripcion', 'Escribe el tratamiento indicado o limpia ese bloque.');
            }

            if ($hasTratamientoContent && !$request->filled('tratamiento_fecha_inicio')) {
                $validator->errors()->add('tratamiento_fecha_inicio', 'Indica desde cuándo inicia el tratamiento.');
            }

            if (($recetaMedicamentos !== '' || $recetaIndicaciones !== '') && ($recetaMedicamentos === '' || $recetaIndicaciones === '')) {
                $validator->errors()->add('receta_medicamentos', 'Completa medicamentos e indicaciones.');
                $validator->errors()->add('receta_indicaciones', 'Completa medicamentos e indicaciones.');
            }

            if ($hasSeguimiento && $seguimientoMotivo === '') {
                $validator->errors()->add('seguimiento_motivo', 'Describe el motivo del próximo control.');
            }

            if ($hasSeguimiento && !$request->filled('seguimiento_fecha_proximo_control') && !$request->filled('seguimiento_dias_retorno')) {
                $validator->errors()->add('seguimiento_fecha_proximo_control', 'Indica fecha de control o días de retorno.');
            }
        });

        $validated = $validator->validateWithBag($errorBag);
        $validated['vacuna_nombre'] = $this->resolveVacunaNombre(
            $validated['vacuna_nombre_select'] ?? null,
            $validated['vacuna_nombre_custom'] ?? null
        );

        return $validated;
    }

    public function buildHistoriaData(array $validated, string $source): array
    {
        $diagnostico = trim((string) ($validated['diagnostico'] ?? ''));
        $observaciones = trim((string) ($validated['observaciones'] ?? ''));
        $vacunaNombre = trim((string) ($validated['vacuna_nombre'] ?? ''));

        if ($diagnostico !== '' || $observaciones !== '') {
            return [
                'diagnostico' => $diagnostico !== '' ? $diagnostico : null,
                'observaciones' => $observaciones !== '' ? $observaciones : null,
            ];
        }

        return match ($validated['tipo_atencion'] ?? 'consulta') {
            'servicio' => [
                'diagnostico' => $this->resolveServicioDiagnostico($validated),
                'observaciones' => $this->resolveServicioObservaciones($validated, $source),
            ],
            'vacunacion' => [
                'diagnostico' => 'Vacunación preventiva',
                'observaciones' => $vacunaNombre !== ''
                    ? 'Aplicación de vacuna ' . $vacunaNombre . ' desde ' . $source . '.'
                    : 'Atención preventiva registrada desde ' . $source . '.',
            ],
            'control' => [
                'diagnostico' => 'Control clínico',
                'observaciones' => 'Paciente atendido en control clínico desde ' . $source . '.',
            ],
            'desparasitacion' => [
                'diagnostico' => 'Desparasitación preventiva',
                'observaciones' => 'Control antiparasitario registrado desde ' . $source . '.',
            ],
            default => [
                'diagnostico' => null,
                'observaciones' => null,
            ],
        };
    }

    public function buildSeguimientoTitulo(array $validated): ?string
    {
        if (trim((string) ($validated['seguimiento_motivo'] ?? '')) === '') {
            return null;
        }

        return match ($validated['tipo_atencion'] ?? 'consulta') {
            'vacunacion' => 'Próxima vacuna',
            'control' => 'Control médico',
            'desparasitacion' => 'Control antiparasitario',
            default => 'Control médico',
        };
    }

    private function resolveVacunaNombre(?string $selectedName, ?string $customName): string
    {
        $selectedName = trim((string) $selectedName);
        $customName = trim((string) $customName);

        return $selectedName === '__custom__' ? $customName : $selectedName;
    }

    private function resolveServicioDiagnostico(array $validated): ?string
    {
        $servicio = Producto::find($validated['servicio_producto_id'] ?? null);

        return $servicio ? 'Servicio: ' . $servicio->nombre : 'Servicio veterinario';
    }

    private function resolveServicioObservaciones(array $validated, string $source): string
    {
        $precio = isset($validated['precio_servicio']) && $validated['precio_servicio'] !== null
            ? ' Precio registrado: S/ ' . number_format((float) $validated['precio_servicio'], 2) . '.'
            : '';

        return 'Atención de servicio registrada desde ' . $source . '.' . $precio;
    }
}
