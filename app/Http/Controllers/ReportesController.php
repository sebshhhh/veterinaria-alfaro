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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $periodo = in_array($request->input('periodo'), ['7', '30', '90'], true)
            ? (int) $request->input('periodo')
            : 30;
        $rangeStart = $today->copy()->subDays($periodo - 1);
        $previousStart = $rangeStart->copy()->subDays($periodo);
        $previousEnd = $rangeStart->copy()->subDay();
        $chartDays = min($periodo, 14);
        $chartStart = $today->copy()->subDays($chartDays - 1);
        $periodLabel = $rangeStart->format('d/m/Y') . ' - ' . $today->format('d/m/Y');

        $ingresosPeriodo = (float) Venta::where('estado', 'pagado')
            ->whereDate('fecha', '>=', $rangeStart)
            ->sum('total');
        $ingresosPrevios = (float) Venta::where('estado', 'pagado')
            ->whereBetween('fecha', [$previousStart, $previousEnd])
            ->sum('total');
        $ventasPeriodo = Venta::whereDate('fecha', '>=', $rangeStart)->count();
        $ventasPrevias = Venta::whereBetween('fecha', [$previousStart, $previousEnd])->count();
        $atencionesPeriodo = HistoriaClinica::whereDate('fecha', '>=', $rangeStart)->count();
        $atencionesPrevias = HistoriaClinica::whereBetween('fecha', [$previousStart, $previousEnd])->count();
        $citasPeriodo = Citas::whereDate('fecha', '>=', $rangeStart)->count();
        $citasPrevias = Citas::whereBetween('fecha', [$previousStart, $previousEnd])->count();
        $ticketPromedio = $ventasPeriodo > 0 ? $ingresosPeriodo / $ventasPeriodo : 0;
        $ingresoPromedioDiario = $periodo > 0 ? $ingresosPeriodo / $periodo : 0;
        $conversionAgenda = $citasPeriodo > 0
            ? round((Citas::where('estado', 'completada')->whereDate('fecha', '>=', $rangeStart)->count() / $citasPeriodo) * 100)
            : 0;
        $pendienteCobro = (float) Venta::where('estado', 'pendiente')->sum('total');

        $trend = function (float|int $current, float|int $previous): string {
            if ($previous == 0) {
                return $current > 0 ? '+100%' : '0%';
            }

            $value = (($current - $previous) / max($previous, 1)) * 100;

            return ($value > 0 ? '+' : '') . round($value) . '%';
        };

        $kpis = [
            [
                'label' => 'Ingresos',
                'value' => 'S/ ' . number_format($ingresosPeriodo, 2),
                'helper' => $trend($ingresosPeriodo, $ingresosPrevios) . ' frente al periodo anterior',
                'tone' => 'blue',
            ],
            [
                'label' => 'Atenciones',
                'value' => $atencionesPeriodo,
                'helper' => $trend($atencionesPeriodo, $atencionesPrevias) . ' en productividad clínica',
                'tone' => 'emerald',
            ],
            [
                'label' => 'Citas',
                'value' => $citasPeriodo,
                'helper' => $conversionAgenda . '% conversion agenda-atencion',
                'tone' => 'violet',
            ],
            [
                'label' => 'Ticket promedio',
                'value' => 'S/ ' . number_format($ticketPromedio, 2),
                'helper' => $trend($ventasPeriodo, $ventasPrevias) . ' en ventas registradas',
                'tone' => 'amber',
            ],
        ];

        $actividadRaw = HistoriaClinica::selectRaw('DATE(fecha) as dia, COUNT(*) as total')
            ->whereDate('fecha', '>=', $chartStart)
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $agendaRaw = Citas::selectRaw('DATE(fecha) as dia, COUNT(*) as total')
            ->whereDate('fecha', '>=', $chartStart)
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $actividadSemanal = collect(range($chartDays - 1, 0))->map(function ($offset) use ($today, $actividadRaw, $agendaRaw) {
            $date = $today->copy()->subDays($offset);
            $key = $date->toDateString();

            return [
                'label' => $date->format('d/m'),
                'atenciones' => (int) ($actividadRaw[$key] ?? 0),
                'citas' => (int) ($agendaRaw[$key] ?? 0),
            ];
        });

        $maxActividad = max(1, (int) $actividadSemanal->max(fn ($item) => max($item['atenciones'], $item['citas'])));

        $estadoCitas = Citas::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $estadoAgenda = collect([
            ['label' => 'Pendientes', 'value' => (int) ($estadoCitas['pendiente'] ?? 0), 'tone' => 'blue'],
            ['label' => 'Atendidas', 'value' => (int) ($estadoCitas['completada'] ?? 0), 'tone' => 'emerald'],
            ['label' => 'Canceladas', 'value' => (int) ($estadoCitas['cancelada'] ?? 0), 'tone' => 'rose'],
        ]);

        $maxEstado = max(1, (int) $estadoAgenda->max('value'));

        $preventivo = [
            [
                'label' => 'Vacunas vencidas',
                'value' => Vacuna::where('estado_aplicacion', 'programada')
                    ->whereNotNull('fecha_programada')
                    ->whereDate('fecha_programada', '<', $today)
                    ->count(),
                'url' => route('vacunas.index', ['estado' => 'vencidas']),
                'tone' => 'rose',
            ],
            [
                'label' => 'Controles vencidos',
                'value' => Seguimiento::where('estado', '!=', 'cerrado')
                    ->whereNotNull('fecha_proximo_control')
                    ->whereDate('fecha_proximo_control', '<', $today)
                    ->count(),
                'url' => route('seguimientos.index', ['estado' => 'vencidos']),
                'tone' => 'amber',
            ],
            [
                'label' => 'Citas pendientes',
                'value' => Citas::where('estado', 'pendiente')->count(),
                'url' => route('citas.index'),
                'tone' => 'blue',
            ],
        ];

        $riesgoTotal = collect($preventivo)->sum('value');

        $resumenOperativo = [
            ['label' => 'Clientes', 'value' => Clientes::count(), 'helper' => 'base total', 'tone' => 'blue'],
            ['label' => 'Mascotas', 'value' => Mascotas::count(), 'helper' => 'pacientes registrados', 'tone' => 'emerald'],
            ['label' => 'Citas hoy', 'value' => Citas::whereDate('fecha', $today)->count(), 'helper' => 'agenda del dia', 'tone' => 'violet'],
            ['label' => 'Ventas pendientes', 'value' => Venta::where('estado', 'pendiente')->count(), 'helper' => 'por cobrar', 'tone' => 'amber'],
        ];

        $bloquesClinicos = collect([
            ['label' => 'Consultas / servicios', 'value' => $atencionesPeriodo, 'helper' => 'historias clínicas', 'url' => route('historias-clinicas.index')],
            ['label' => 'Vacunas aplicadas', 'value' => Vacuna::where('estado_aplicacion', 'aplicada')->whereDate('fecha_aplicacion', '>=', $rangeStart)->count(), 'helper' => 'control preventivo', 'url' => route('vacunas.index')],
            ['label' => 'Tratamientos', 'value' => Tratamiento::whereDate('fecha_inicio', '>=', $rangeStart)->count(), 'helper' => 'planes indicados', 'url' => route('tratamientos.index')],
            ['label' => 'Recetas', 'value' => Receta::whereHas('historiaClinica', fn ($query) => $query->whereDate('fecha', '>=', $rangeStart))->count(), 'helper' => 'indicaciones emitidas', 'url' => route('recetas.index')],
            ['label' => 'Controles activos', 'value' => Seguimiento::where('estado', '!=', 'cerrado')->count(), 'helper' => 'controles abiertos', 'url' => route('seguimientos.index')],
            ['label' => 'Stock bajo', 'value' => Producto::where('es_servicio', false)->where('stock', '<=', 5)->count(), 'helper' => 'requiere revisión', 'url' => route('productos.index')],
        ]);

        $lecturaEjecutiva = [
            [
                'title' => 'Control financiero',
                'value' => 'S/ ' . number_format($ingresosPeriodo, 2),
                'detail' => 'Cobrado en el periodo. Pendiente por cobrar: S/ ' . number_format($pendienteCobro, 2),
            ],
            [
                'title' => 'Flujo clinico',
                'value' => $atencionesPeriodo . ' atenciones',
                'detail' => $conversionAgenda . '% de citas cerradas como atendidas.',
            ],
            [
                'title' => 'Riesgo operativo',
                'value' => $riesgoTotal . ' alertas',
                'detail' => 'Vacunas, controles y citas pendientes que necesitan accion.',
            ],
        ];

        $ingresosPorMetodo = Venta::select('metodo_pago', DB::raw('SUM(total) as total'))
            ->where('estado', 'pagado')
            ->whereDate('fecha', '>=', $rangeStart)
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'label' => ucfirst((string) $item->metodo_pago),
                'value' => (float) $item->total,
            ]);

        $maxMetodo = max(1, (float) $ingresosPorMetodo->max('value'));

        $topServicios = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'pagado')
            ->whereDate('ventas.fecha', '>=', $rangeStart)
            ->select('productos.nombre', DB::raw('SUM(detalle_ventas.subtotal) as total'))
            ->groupBy('productos.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'label' => $item->nombre,
                'value' => (float) $item->total,
            ]);

        $especiesReporte = HistoriaClinica::query()
            ->join('mascotas', 'historias_clinicas.mascota_id', '=', 'mascotas.id')
            ->whereDate('historias_clinicas.fecha', '>=', $rangeStart)
            ->selectRaw("COALESCE(NULLIF(mascotas.tipo_animal, ''), 'Sin especie') as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'label' => $item->label,
                'value' => (int) $item->value,
            ]);

        $totalMascotasAtendidas = HistoriaClinica::whereDate('fecha', '>=', $rangeStart)
            ->distinct()
            ->count('mascota_id');

        $ultimasAtenciones = HistoriaClinica::with(['mascota.cliente'])
            ->latest('fecha')
            ->limit(6)
            ->get();

        $pacientesFrecuentes = HistoriaClinica::query()
            ->join('mascotas', 'historias_clinicas.mascota_id', '=', 'mascotas.id')
            ->leftJoin('clientes', 'mascotas.cliente_id', '=', 'clientes.id')
            ->whereDate('historias_clinicas.fecha', '>=', $rangeStart)
            ->select(
                'mascotas.nombre as mascota',
                'clientes.nombre as cliente',
                DB::raw('COUNT(historias_clinicas.id) as total')
            )
            ->groupBy('mascotas.nombre', 'clientes.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('reportes.index', compact(
            'kpis',
            'periodo',
            'periodLabel',
            'ingresosPeriodo',
            'ticketPromedio',
            'ingresoPromedioDiario',
            'atencionesPeriodo',
            'conversionAgenda',
            'chartDays',
            'resumenOperativo',
            'bloquesClinicos',
            'lecturaEjecutiva',
            'actividadSemanal',
            'maxActividad',
            'estadoAgenda',
            'maxEstado',
            'preventivo',
            'riesgoTotal',
            'ingresosPorMetodo',
            'maxMetodo',
            'topServicios',
            'especiesReporte',
            'totalMascotasAtendidas',
            'ultimasAtenciones',
            'pacientesFrecuentes',
            'pendienteCobro'
        ));
    }
}
