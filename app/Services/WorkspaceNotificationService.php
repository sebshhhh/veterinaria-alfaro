<?php

namespace App\Services;

use App\Models\Citas;
use App\Models\ConfiguracionSistema;
use App\Models\Mascotas;
use App\Models\Producto;
use App\Models\Seguimiento;
use App\Models\Vacuna;

class WorkspaceNotificationService
{
    public function build(): array
    {
        $today = now()->toDateString();
        $vaccineAlertDays = (int) ConfiguracionSistema::numero('vaccine_alert_days', 3);
        $controlAlertDays = (int) ConfiguracionSistema::numero('control_alert_days', 7);
        $lowStockThreshold = (int) ConfiguracionSistema::numero('low_stock_threshold', 5);
        $threeDaysAhead = now()->copy()->addDays($vaccineAlertDays)->toDateString();
        $weekAhead = now()->copy()->addDays($controlAlertDays)->toDateString();

        $items = collect();

        $citasPendientesHoy = Citas::query()
            ->where('estado', 'pendiente')
            ->whereDate('fecha', $today)
            ->count();

        if ($citasPendientesHoy > 0) {
            $items->push([
                'section' => 'Operación de hoy',
                'title' => 'Citas pendientes hoy',
                'detail' => $citasPendientesHoy . ' cita' . ($citasPendientesHoy === 1 ? '' : 's') . ' necesita' . ($citasPendientesHoy === 1 ? '' : 'n') . ' atención o confirmación.',
                'count' => $citasPendientesHoy,
                'tone' => 'blue',
                'icon' => 'calendar',
                'window' => 'Hoy',
                'action' => 'Ver agenda',
                'url' => route('citas.index', ['fecha' => $today, 'estado' => 'pendiente']),
            ]);
        }

        $vacunasHoy = Vacuna::query()
            ->where(function ($query) use ($today) {
                $query->where(function ($subQuery) use ($today) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', $today);
                })->orWhere(function ($subQuery) use ($today) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', $today);
                });
            })
            ->count();

        if ($vacunasHoy > 0) {
            $items->push([
                'section' => 'Preventivo',
                'title' => 'Vacunas para hoy',
                'detail' => $vacunasHoy . ' dosis preventiva' . ($vacunasHoy === 1 ? '' : 's') . ' deberia' . ($vacunasHoy === 1 ? '' : 'n') . ' resolverse hoy.',
                'count' => $vacunasHoy,
                'tone' => 'emerald',
                'icon' => 'shield',
                'window' => 'Hoy',
                'action' => 'Atender vacunas',
                'url' => route('vacunas.index', ['estado_dosis' => 'proximas']),
            ]);
        }

        $vacunasVencidas = Vacuna::query()
            ->where(function ($query) use ($today) {
                $query->where(function ($subQuery) use ($today) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '<', $today);
                })->orWhere(function ($subQuery) use ($today) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '<', $today);
                });
            })
            ->count();

        if ($vacunasVencidas > 0) {
            $items->push([
                'section' => 'Preventivo',
                'title' => 'Vacunas vencidas',
                'detail' => $vacunasVencidas . ' control' . ($vacunasVencidas === 1 ? '' : 'es') . ' preventivo' . ($vacunasVencidas === 1 ? '' : 's') . ' requiere' . ($vacunasVencidas === 1 ? '' : 'n') . ' regularizacion.',
                'count' => $vacunasVencidas,
                'tone' => 'rose',
                'icon' => 'shield-alert',
                'window' => 'Urgente',
                'action' => 'Abrir vacunas',
                'url' => route('vacunas.index', ['estado_dosis' => 'vencidas']),
            ]);
        }

        $vacunasProximas72h = Vacuna::query()
            ->where(function ($query) use ($today, $threeDaysAhead) {
                $query->where(function ($subQuery) use ($today, $threeDaysAhead) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '>=', $today)
                        ->whereDate('proxima_dosis', '<=', $threeDaysAhead);
                })->orWhere(function ($subQuery) use ($today, $threeDaysAhead) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '>=', $today)
                        ->whereDate('fecha_programada', '<=', $threeDaysAhead);
                });
            })
            ->count();

        if ($vacunasProximas72h > 0) {
            $items->push([
                'section' => 'Preventivo',
                'title' => 'Vacunas próximas',
                'detail' => $vacunasProximas72h . ' vacuna' . ($vacunasProximas72h === 1 ? '' : 's') . ' debe' . ($vacunasProximas72h === 1 ? '' : 'n') . ' revisarse en las próximas 72 horas.',
                'count' => $vacunasProximas72h,
                'tone' => 'amber',
                'icon' => 'clock',
                'window' => '72 h',
                'action' => 'Preparar seguimiento',
                'url' => route('vacunas.index', ['estado_dosis' => 'proximas']),
            ]);
        }

        $seguimientosVencidos = Seguimiento::query()
            ->where('estado', '!=', 'cerrado')
            ->whereNotNull('fecha_proximo_control')
            ->whereDate('fecha_proximo_control', '<', $today)
            ->count();

        $seguimientosHoy = Seguimiento::query()
            ->where('estado', '!=', 'cerrado')
            ->whereNotNull('fecha_proximo_control')
            ->whereDate('fecha_proximo_control', $today)
            ->count();

        $seguimientosProximos = Seguimiento::query()
            ->where('estado', '!=', 'cerrado')
            ->whereNotNull('fecha_proximo_control')
            ->whereDate('fecha_proximo_control', '>', $today)
            ->whereDate('fecha_proximo_control', '<=', $weekAhead)
            ->count();
        $seguimientosTotal = $seguimientosVencidos + $seguimientosHoy + $seguimientosProximos;

        if ($seguimientosTotal > 0) {
            $seguimientoTone = $seguimientosVencidos > 0 ? 'rose' : ($seguimientosHoy > 0 ? 'blue' : 'violet');
            $seguimientoUrl = route('seguimientos.index', [
                'estado' => $seguimientosVencidos > 0 ? 'vencidos' : ($seguimientosHoy > 0 ? 'hoy' : 'próximos'),
            ]);

            $items->push([
                'section' => 'Control de retorno',
                'title' => $seguimientosVencidos > 0 ? 'Controles vencidos' : 'Controles programados',
                'detail' => $seguimientosVencidos . ' vencido' . ($seguimientosVencidos === 1 ? '' : 's') . ', ' . $seguimientosHoy . ' para hoy y ' . $seguimientosProximos . ' próximo' . ($seguimientosProximos === 1 ? '' : 's') . ' en agenda.',
                'count' => $seguimientosTotal,
                'tone' => $seguimientoTone,
                'icon' => 'git-merge',
                'window' => $seguimientosVencidos > 0 ? 'Urgente' : ($seguimientosHoy > 0 ? 'Hoy' : '7 días'),
                'action' => 'Abrir controles',
                'url' => $seguimientoUrl,
            ]);
        }

        $stockBajo = Producto::query()
            ->where('es_servicio', false)
            ->where('stock', '>', 0)
            ->where('stock', '<=', $lowStockThreshold)
            ->count();

        if ($stockBajo > 0) {
            $items->push([
                'section' => 'Gestion',
                'title' => 'Stock bajo',
                'detail' => $stockBajo . ' producto' . ($stockBajo === 1 ? '' : 's') . ' necesita' . ($stockBajo === 1 ? '' : 'n') . ' reabastecimiento.',
                'count' => $stockBajo,
                'tone' => 'slate',
                'icon' => 'package',
                'window' => 'Gestion',
                'action' => 'Ver productos',
                'url' => route('productos.index'),
            ]);
        }

        $mascotasSinVacuna = Mascotas::query()->doesntHave('vacunas')->count();

        if ($mascotasSinVacuna > 0) {
            $items->push([
                'section' => 'Oportunidades clínicas',
                'title' => 'Pacientes sin control preventivo',
                'detail' => $mascotasSinVacuna . ' mascota' . ($mascotasSinVacuna === 1 ? '' : 's') . ' aún no registra' . ($mascotasSinVacuna === 1 ? '' : 'n') . ' vacunas en el sistema.',
                'count' => $mascotasSinVacuna,
                'tone' => 'sky',
                'icon' => 'heart',
                'window' => 'Revision',
                'action' => 'Ver mascotas',
                'url' => route('mascotas.index'),
            ]);
        }

        $priorityMap = [
            'rose' => 1,
            'amber' => 2,
            'blue' => 3,
            'emerald' => 3,
            'violet' => 4,
            'slate' => 5,
            'sky' => 5,
        ];

        $sortedItems = $items
            ->map(function (array $item) use ($priorityMap) {
                $item['priority_rank'] = $priorityMap[$item['tone']] ?? 99;
                $item['severity'] = match ($item['tone']) {
                    'rose' => 'Critica',
                    'amber' => 'Alta',
                    'blue', 'emerald' => 'Hoy',
                    'violet' => 'Control',
                    default => 'Gestion',
                };

                return $item;
            })
            ->sortBy([
                fn (array $item) => $item['priority_rank'],
                fn (array $item) => -1 * (int) $item['count'],
                fn (array $item) => $item['section'],
            ])
            ->values();

        $totalRecords = (int) $sortedItems->sum('count');
        $activeItems = $sortedItems->count();
        $criticalItems = $sortedItems->whereIn('tone', ['rose', 'amber'])->count();
        $criticalCases = (int) $sortedItems->whereIn('tone', ['rose', 'amber'])->sum('count');
        $todayCases = $citasPendientesHoy + $vacunasHoy;
        $followupCases = $seguimientosTotal + $vacunasProximas72h;
        $groupedItems = $sortedItems
            ->groupBy('section')
            ->map(fn ($group, $section) => [
                'section' => $section,
                'items' => $group->values()->all(),
            ])
            ->values()
            ->all();
        $summaryCards = [
            ['label' => 'Hoy', 'value' => $citasPendientesHoy + $vacunasHoy, 'tone' => 'blue', 'caption' => 'pendientes inmediatos'],
            ['label' => 'Criticas', 'value' => $vacunasVencidas + $stockBajo, 'tone' => 'rose', 'caption' => 'requieren reaccion'],
            ['label' => 'Controles', 'value' => $seguimientosTotal + $vacunasProximas72h, 'tone' => 'violet', 'caption' => 'retornos por revisar'],
        ];
        $quickActions = collect([
            ['label' => 'Agenda', 'url' => route('citas.index'), 'show' => $citasPendientesHoy > 0],
            ['label' => 'Vacunas', 'url' => route('vacunas.index'), 'show' => ($vacunasHoy + $vacunasVencidas + $vacunasProximas72h) > 0],
            ['label' => 'Controles', 'url' => route('seguimientos.index'), 'show' => $seguimientosTotal > 0],
            ['label' => 'Productos', 'url' => route('productos.index'), 'show' => $stockBajo > 0],
        ])->where('show', true)->take(4)->map(fn (array $action) => [
            'label' => $action['label'],
            'url' => $action['url'],
        ])->values()->all();
        $primaryItem = $sortedItems->first();
        $primaryAction = $primaryItem
            ? [
                'section' => $primaryItem['section'],
                'title' => $primaryItem['title'],
                'detail' => $primaryItem['detail'],
                'action' => $primaryItem['action'],
                'url' => $primaryItem['url'],
                'count' => $primaryItem['count'],
                'window' => $primaryItem['window'],
                'severity' => $primaryItem['severity'],
                'hint' => match ($primaryItem['severity']) {
                    'Critica' => 'Conviene resolver esto antes de continuar con el resto de pendientes.',
                    'Alta', 'Hoy' => 'Impacta directamente la operacion de hoy y conviene atenderlo primero.',
                    'Control' => 'Mantener este control al día evita atrasos clínicos posteriores.',
                    default => 'Es una tarea importante de soporte para mantener el sistema ordenado.',
                },
            ]
            : null;
        $focusLine = match (true) {
            $criticalItems > 0 => 'Empieza por las alertas criticas y luego continua con las tareas del dia.',
            $todayCases > 0 => 'Hoy la prioridad esta en agenda y preventivos programados.',
            $followupCases > 0 => 'No hay urgencias, pero si controles de retorno que conviene revisar.',
            $activeItems > 0 => 'El sistema tiene pendientes de gestion listos para resolver.',
            default => 'No hay alertas urgentes registradas en este momento.',
        };
        $overviewChips = [
            ['label' => 'Criticas', 'value' => $criticalCases, 'tone' => 'rose'],
            ['label' => 'Hoy', 'value' => $todayCases, 'tone' => 'blue'],
            ['label' => 'Controles', 'value' => $followupCases, 'tone' => 'violet'],
        ];

        return [
            'items' => $sortedItems->take(6)->values()->all(),
            'sections' => $groupedItems,
            'summary_cards' => $summaryCards,
            'quick_actions' => $quickActions,
            'primary_action' => $primaryAction,
            'focus_line' => $focusLine,
            'overview_chips' => $overviewChips,
            'total' => $totalRecords,
            'active_items' => $activeItems,
            'display_total' => $activeItems > 99 ? '99+' : (string) $activeItems,
            'critical_items' => $criticalItems,
            'has_items' => $sortedItems->isNotEmpty(),
            'meta' => $criticalItems > 0
                ? $criticalItems . ' crítica' . ($criticalItems === 1 ? '' : 's') . ' · ' . $activeItems . ' alerta' . ($activeItems === 1 ? '' : 's')
                : ($sortedItems->isNotEmpty() ? $activeItems . ' alerta' . ($activeItems === 1 ? '' : 's') . ' activa' . ($activeItems === 1 ? '' : 's') : 'Sin pendientes'),
            'headline' => $criticalItems > 0
                ? $criticalItems . ' alerta' . ($criticalItems === 1 ? '' : 's') . ' prioritaria' . ($criticalItems === 1 ? '' : 's')
                : 'Todo bajo control',
        ];
    }
}

