<?php

namespace App\Http\Controllers;

use App\Models\Citas;
use App\Models\Clientes;
use App\Models\HistoriaClinica;
use App\Models\Mascotas;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Seguimiento;
use App\Models\Tratamiento;
use App\Models\Vacuna;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $todayDate = $today->toDateString();
        $twoDaysAhead = $today->copy()->addDays(2)->toDateString();
        $threeDaysAhead = $today->copy()->addDays(3)->toDateString();
        $weekAhead = $today->copy()->addDays(7)->toDateString();
        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $rangeStart = $today->copy()->subDays(6)->toDateString();

        $totalClientes = Clientes::count();
        $totalMascotas = Mascotas::count();
        $citasHoy = Citas::whereDate('fecha', $todayDate)->count();
        $citasPendientes = Citas::where('estado', 'pendiente')->count();
        $citasPendientesHoy = Citas::where('estado', 'pendiente')
            ->whereDate('fecha', $todayDate)
            ->count();
        $citasCompletadas = Citas::where('estado', 'completada')->count();
        $citasCanceladas = Citas::where('estado', 'cancelada')->count();
        $citasProximas48h = Citas::where('estado', 'pendiente')
            ->whereDate('fecha', '>=', $todayDate)
            ->whereDate('fecha', '<=', $twoDaysAhead)
            ->count();
        $historiasMes = HistoriaClinica::whereDate('fecha', '>=', $monthStart)->count();
        $tratamientosActivos = Tratamiento::whereDate('fecha_inicio', '<=', $todayDate)
            ->where(function ($query) use ($todayDate) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $todayDate);
            })
            ->count();
        $vacunasProximas = Vacuna::where(function ($query) use ($todayDate, $weekAhead) {
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
            ->count();
        $vacunasHoy = Vacuna::where(function ($query) use ($todayDate) {
            $query->where(function ($subQuery) use ($todayDate) {
                $subQuery->where('estado_aplicacion', 'aplicada')
                    ->whereNotNull('proxima_dosis')
                    ->whereDate('proxima_dosis', $todayDate);
            })->orWhere(function ($subQuery) use ($todayDate) {
                $subQuery->where('estado_aplicacion', 'programada')
                    ->whereNotNull('fecha_programada')
                    ->whereDate('fecha_programada', $todayDate);
            });
        })
            ->count();
        $vacunasEn72h = Vacuna::where(function ($query) use ($todayDate, $threeDaysAhead) {
            $query->where(function ($subQuery) use ($todayDate, $threeDaysAhead) {
                $subQuery->where('estado_aplicacion', 'aplicada')
                    ->whereNotNull('proxima_dosis')
                    ->whereDate('proxima_dosis', '>=', $todayDate)
                    ->whereDate('proxima_dosis', '<=', $threeDaysAhead);
            })->orWhere(function ($subQuery) use ($todayDate, $threeDaysAhead) {
                $subQuery->where('estado_aplicacion', 'programada')
                    ->whereNotNull('fecha_programada')
                    ->whereDate('fecha_programada', '>=', $todayDate)
                    ->whereDate('fecha_programada', '<=', $threeDaysAhead);
            });
        })
            ->count();
        $vacunasVencidas = Vacuna::where(function ($query) use ($todayDate) {
            $query->where(function ($subQuery) use ($todayDate) {
                $subQuery->where('estado_aplicacion', 'aplicada')
                    ->whereNotNull('proxima_dosis')
                    ->whereDate('proxima_dosis', '<', $todayDate);
            })->orWhere(function ($subQuery) use ($todayDate) {
                $subQuery->where('estado_aplicacion', 'programada')
                    ->whereNotNull('fecha_programada')
                    ->whereDate('fecha_programada', '<', $todayDate);
            });
        })
            ->count();
        $mascotasSinVacuna = Mascotas::doesntHave('vacunas')->count();
        $totalProductos = Producto::count();
        $stockBajo = Producto::where('es_servicio', false)->where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $productosAgotados = Producto::where('es_servicio', false)->where('stock', '<=', 0)->count();
        $ventasHoy = Venta::whereDate('fecha', $todayDate)->count();
        $ingresosHoy = (float) Venta::where('estado', 'pagado')->whereDate('fecha', $todayDate)->sum('total');
        $ingresosMes = (float) Venta::where('estado', 'pagado')->whereDate('fecha', '>=', $monthStart)->sum('total');
        $atencionesProgramadas = HistoriaClinica::where('origen_atencion', 'programada')->count();
        $atencionesManuales = HistoriaClinica::where('origen_atencion', 'manual')->count();
        $atencionesPreventivas = HistoriaClinica::where('origen_atencion', 'preventiva')->count();

        $agendaHoy = Citas::with(['mascota.cliente', 'veterinario.user'])
            ->whereDate('fecha', $todayDate)
            ->orderBy('hora')
            ->get();

        $proximasCitas = Citas::with(['mascota.cliente', 'veterinario.user'])
            ->where('estado', 'pendiente')
            ->where(function ($query) use ($todayDate) {
                $query->whereDate('fecha', '>', $todayDate)
                    ->orWhere(function ($inner) use ($todayDate) {
                        $inner->whereDate('fecha', $todayDate)
                            ->whereTime('hora', '>=', now()->format('H:i:s'));
                    });
            })
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(6)
            ->get();

        $ultimasAtenciones = HistoriaClinica::with(['mascota.cliente'])
            ->latest('fecha')
            ->limit(5)
            ->get();

        $ultimasRecetas = Receta::with(['historiaClinica.mascota.cliente'])
            ->latest('id')
            ->limit(4)
            ->get();

        $alertasVacunas = Vacuna::with(['mascota.cliente'])
            ->where(function ($query) use ($todayDate, $weekAhead) {
                $query->where(function ($subQuery) use ($todayDate, $weekAhead) {
                    $subQuery->where('estado_aplicacion', 'aplicada')
                        ->whereNotNull('proxima_dosis')
                        ->whereDate('proxima_dosis', '<=', $weekAhead);
                })->orWhere(function ($subQuery) use ($todayDate, $weekAhead) {
                    $subQuery->where('estado_aplicacion', 'programada')
                        ->whereNotNull('fecha_programada')
                        ->whereDate('fecha_programada', '<=', $weekAhead);
                });
            })
            ->orderByRaw('COALESCE(fecha_programada, proxima_dosis) asc')
            ->limit(6)
            ->get();

        $seguimientosActivos = Seguimiento::where('estado', '!=', 'cerrado')->count();
        $seguimientosHoy = Seguimiento::where('estado', '!=', 'cerrado')
            ->whereDate('fecha_proximo_control', $todayDate)
            ->count();
        $seguimientosVencidos = Seguimiento::where('estado', '!=', 'cerrado')
            ->whereNotNull('fecha_proximo_control')
            ->whereDate('fecha_proximo_control', '<', $todayDate)
            ->count();
        $seguimientosSemana = Seguimiento::where('estado', '!=', 'cerrado')
            ->whereNotNull('fecha_proximo_control')
            ->whereDate('fecha_proximo_control', '>=', $todayDate)
            ->whereDate('fecha_proximo_control', '<=', $weekAhead)
            ->count();

        $proximosControles = Seguimiento::with(['mascota.cliente', 'historiaClinica', 'cita'])
            ->where('estado', '!=', 'cerrado')
            ->where(function ($query) use ($todayDate, $weekAhead) {
                $query->where(function ($subQuery) use ($todayDate) {
                    $subQuery->whereNotNull('fecha_proximo_control')
                        ->whereDate('fecha_proximo_control', '<', $todayDate);
                })->orWhere(function ($subQuery) use ($todayDate, $weekAhead) {
                    $subQuery->whereNotNull('fecha_proximo_control')
                        ->whereDate('fecha_proximo_control', '>=', $todayDate)
                        ->whereDate('fecha_proximo_control', '<=', $weekAhead);
                });
            })
            ->orderByRaw("CASE WHEN fecha_proximo_control < ? THEN 0 WHEN fecha_proximo_control = ? THEN 1 ELSE 2 END", [$todayDate, $todayDate])
            ->orderBy('fecha_proximo_control')
            ->limit(5)
            ->get();

        $tratamientosActivosLista = Tratamiento::with(['historiaClinica.mascota.cliente'])
            ->whereDate('fecha_inicio', '<=', $todayDate)
            ->where(function ($query) use ($todayDate) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $todayDate);
            })
            ->orderByRaw('CASE WHEN proximo_control IS NULL THEN 1 ELSE 0 END')
            ->orderBy('proximo_control')
            ->orderByDesc('fecha_inicio')
            ->limit(4)
            ->get();
        $proximosControlesCount = $seguimientosSemana;

        $citasSemanalRaw = Citas::selectRaw('DATE(fecha) as dia, COUNT(*) as total')
            ->whereDate('fecha', '>=', $rangeStart)
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $atencionesSemanalRaw = HistoriaClinica::selectRaw('DATE(fecha) as dia, COUNT(*) as total')
            ->whereDate('fecha', '>=', $rangeStart)
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $actividadSemanal = collect(range(6, 0))->map(function ($offset) use ($today, $citasSemanalRaw, $atencionesSemanalRaw) {
            $date = $today->copy()->subDays($offset);
            $key = $date->toDateString();

            return [
                'label' => $date->format('d/m'),
                'citas' => (int) ($citasSemanalRaw[$key] ?? 0),
                'atenciones' => (int) ($atencionesSemanalRaw[$key] ?? 0),
            ];
        });

        $especies = Mascotas::select('tipo_animal', DB::raw('count(*) as total'))
            ->groupBy('tipo_animal')
            ->orderByDesc('total')
            ->get();

        $especiesTotal = max((int) $especies->sum('total'), 1);
        $especiesResumen = $especies->map(function ($item) use ($especiesTotal) {
            return [
                'nombre' => $item->tipo_animal ?: 'Sin especie',
                'total' => (int) $item->total,
                'porcentaje' => round(((int) $item->total / $especiesTotal) * 100),
            ];
        });

        $estadoCitasRaw = Citas::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $estadoCitas = collect([
            ['estado' => 'pendiente', 'label' => 'Pendientes', 'color' => 'blue'],
            ['estado' => 'completada', 'label' => 'Atendidas', 'color' => 'emerald'],
            ['estado' => 'cancelada', 'label' => 'Canceladas', 'color' => 'rose'],
        ])->map(function ($item) use ($estadoCitasRaw) {
            return [
                'label' => $item['label'],
                'color' => $item['color'],
                'total' => (int) ($estadoCitasRaw[$item['estado']] ?? 0),
            ];
        });

        $estadoTotal = max((int) $estadoCitas->sum('total'), 1);
        $estadoCitas = $estadoCitas->map(function ($item) use ($estadoTotal) {
            $item['porcentaje'] = round(($item['total'] / $estadoTotal) * 100);
            return $item;
        });

        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', fecha) AS INTEGER)"
            : 'MONTH(fecha)';

        $ingresosMensualesRaw = Venta::selectRaw($monthExpression.' as mes, SUM(total) as total')
            ->where('estado', 'pagado')
            ->whereYear('fecha', $today->year)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $monthLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $ingresosMensuales = collect(range(1, 12))->map(fn ($month) => [
            'label' => $monthLabels[$month - 1],
            'value' => (float) ($ingresosMensualesRaw[$month] ?? 0),
        ]);
        $maxIngresosMensuales = max(1, (float) $ingresosMensuales->max('value'));

        $siguientePaciente = $proximasCitas->first();
        $resumenOperativo = [
            'pacientes_hoy' => $agendaHoy->where('estado', '!=', 'cancelada')->count(),
            'atenciones_hoy' => $agendaHoy->where('estado', 'completada')->count(),
            'pendientes_hoy' => $agendaHoy->where('estado', 'pendiente')->count(),
        ];
        $actividadResumen = [
            'citas_semana' => (int) $actividadSemanal->sum('citas'),
            'atenciones_semana' => (int) $actividadSemanal->sum('atenciones'),
            'cumplimiento' => $actividadSemanal->sum('citas') > 0
                ? round(($actividadSemanal->sum('atenciones') / $actividadSemanal->sum('citas')) * 100)
                : 0,
        ];

        $chartPayload = [
            'actividad' => [
                'labels' => $actividadSemanal->pluck('label')->values()->all(),
                'citas' => $actividadSemanal->pluck('citas')->values()->all(),
                'atenciones' => $actividadSemanal->pluck('atenciones')->values()->all(),
            ],
            'especies' => [
                'labels' => $especiesResumen->pluck('nombre')->values()->all(),
                'values' => $especiesResumen->pluck('total')->values()->all(),
            ],
            'estados' => [
                'labels' => $estadoCitas->pluck('label')->values()->all(),
                'values' => $estadoCitas->pluck('total')->values()->all(),
            ],
            'vacunas' => [
                'labels' => ['Proximas', 'Vencidas', 'Sin control'],
                'values' => [$vacunasProximas, $vacunasVencidas, $mascotasSinVacuna],
            ],
            'origenes' => [
                'labels' => ['Programada', 'Manual', 'Preventiva'],
                'values' => [$atencionesProgramadas, $atencionesManuales, $atencionesPreventivas],
            ],
        ];

        return view('dashboard', compact(
            'totalClientes',
            'totalMascotas',
            'citasHoy',
            'citasPendientes',
            'citasPendientesHoy',
            'citasCompletadas',
            'citasCanceladas',
            'citasProximas48h',
            'historiasMes',
            'tratamientosActivos',
            'vacunasProximas',
            'vacunasHoy',
            'vacunasEn72h',
            'vacunasVencidas',
            'seguimientosActivos',
            'seguimientosHoy',
            'seguimientosVencidos',
            'mascotasSinVacuna',
            'totalProductos',
            'stockBajo',
            'productosAgotados',
            'ventasHoy',
            'ingresosHoy',
            'ingresosMes',
            'atencionesProgramadas',
            'atencionesManuales',
            'atencionesPreventivas',
            'agendaHoy',
            'proximasCitas',
            'ultimasAtenciones',
            'ultimasRecetas',
            'alertasVacunas',
            'proximosControles',
            'tratamientosActivosLista',
            'proximosControlesCount',
            'actividadSemanal',
            'especiesResumen',
            'estadoCitas',
            'siguientePaciente',
            'resumenOperativo',
            'actividadResumen',
            'chartPayload',
            'ingresosMensuales',
            'maxIngresosMensuales'
        ));
    }
}
