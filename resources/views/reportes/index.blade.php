<x-app-layout>
@php
    $periodOptions = [7 => '7 días', 30 => '30 días', 90 => '90 días'];
    $reportDate = now()->format('d/m/Y');
    $reportHour = now()->format('h:i a');
    $speciesColors = ['#2563eb', '#14b8a6', '#f59e0b', '#8b5cf6', '#64748b'];
    $speciesTotal = max(1, $especiesReporte->sum('value'));
    $currentSpecies = 0;
    $donutParts = [];

    foreach ($especiesReporte->values() as $index => $item) {
        $percent = round(($item['value'] / $speciesTotal) * 100);
        $start = $currentSpecies;
        $end = min(100, $currentSpecies + $percent);
        $donutParts[] = $speciesColors[$index % count($speciesColors)] . ' ' . $start . '% ' . $end . '%';
        $currentSpecies = $end;
    }

    if ($currentSpecies < 100) {
        $donutParts[] = '#e2e8f0 ' . $currentSpecies . '% 100%';
    }

    $speciesGradient = count($donutParts) ? implode(', ', $donutParts) : '#e2e8f0 0% 100%';
    $topTotal = max(1, $topServicios->sum('value'));
    $maxMetodoSafe = max(1, $maxMetodo);
    $conversionSafe = min(100, max(0, $conversionAgenda));
    $controlScore = max(0, 100 - min(100, ($riesgoTotal * 14) + ($pendienteCobro > 0 ? 10 : 0)));
    $controlLabel = $controlScore >= 80 ? 'Estable' : ($controlScore >= 55 ? 'En observación' : 'Crítico');

    $toneMap = [
        'blue' => ['text' => 'text-blue-700', 'icon' => 'bg-blue-600', 'soft' => 'bg-blue-50 border-blue-100', 'bar' => 'from-blue-600 to-sky-400'],
        'emerald' => ['text' => 'text-emerald-700', 'icon' => 'bg-emerald-600', 'soft' => 'bg-emerald-50 border-emerald-100', 'bar' => 'from-emerald-600 to-teal-400'],
        'violet' => ['text' => 'text-violet-700', 'icon' => 'bg-violet-600', 'soft' => 'bg-violet-50 border-violet-100', 'bar' => 'from-violet-600 to-indigo-400'],
        'amber' => ['text' => 'text-amber-700', 'icon' => 'bg-amber-500', 'soft' => 'bg-amber-50 border-amber-100', 'bar' => 'from-amber-500 to-orange-400'],
        'rose' => ['text' => 'text-rose-700', 'icon' => 'bg-rose-500', 'soft' => 'bg-rose-50 border-rose-100', 'bar' => 'from-rose-500 to-red-400'],
    ];

    $kpiIcons = ['M4 19V5M4 19h16M8 15l3-3 3 2 5-7', 'M5 3h14v18H5zM8 8h8M8 13h8M8 18h5', 'M8 2v4M16 2v4M4 9h16M5 5h14v16H5z', 'M12 2v20M17 5.5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6'];
    $kpiDeck = collect($kpis)->map(function ($item, $index) use ($kpiIcons) {
        $item['icon'] = $kpiIcons[$index] ?? $kpiIcons[0];
        return $item;
    });

    $executiveActions = collect($preventivo)->map(function ($item) {
        return [
            'label' => $item['label'],
            'value' => $item['value'],
            'url' => $item['url'],
            'tone' => $item['tone'],
            'detail' => $item['value'] > 0 ? 'Revisar ahora' : 'Sin pendientes',
        ];
    })->push([
        'label' => 'Cobros pendientes',
        'value' => 'S/ ' . number_format($pendienteCobro, 2),
        'url' => route('ventas.index'),
        'tone' => $pendienteCobro > 0 ? 'amber' : 'emerald',
        'detail' => $pendienteCobro > 0 ? 'Por confirmar' : 'Al día',
    ]);

    $reportChartPayload = [
        'labels' => $actividadSemanal->pluck('label')->values(),
        'atenciones' => $actividadSemanal->pluck('atenciones')->values(),
        'citas' => $actividadSemanal->pluck('citas')->values(),
        'services' => $topServicios->values(),
        'species' => $especiesReporte->values(),
    ];
@endphp

<div class="module-page reportes-page">
    <div class="module-page__inner space-y-5">
        <div class="report-screen-content space-y-5">
        <section class="report-document-head report-print-card">
            <div class="report-document-head__main">
                <p class="report-eyebrow text-blue-600">Informe gerencial</p>
                <h1 class="mt-1 text-4xl font-black tracking-tight text-slate-950">Análisis de gestión veterinaria</h1>
                <p class="mt-2 max-w-4xl text-sm font-semibold leading-6 text-slate-600">
                    Este módulo resume el comportamiento del periodo seleccionado. A diferencia del dashboard, aquí se analizan tendencias, resultados, riesgos y detalle histórico para sustentar decisiones.
                </p>
            </div>
            <div class="report-document-meta">
                <div>
                    <span>Periodo evaluado</span>
                    <strong>{{ $periodLabel }}</strong>
                </div>
                <div>
                    <span>Fecha de emisión</span>
                    <strong>{{ $reportDate }} · {{ $reportHour }}</strong>
                </div>
                <div>
                    <span>Estado de control</span>
                    <strong>{{ $controlLabel }} · {{ $controlScore }}/100</strong>
                </div>
            </div>
        </section>

        <section class="report-control-strip report-print-card">
            <div class="report-control-strip__periods">
                <span class="report-control-label">Rango</span>
                @foreach($periodOptions as $days => $label)
                    <a href="{{ route('reportes.index', ['periodo' => $days]) }}" class="report-period-link {{ (int) $periodo === $days ? 'report-period-link--active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reportes.index', ['periodo' => $periodo]) }}" class="report-action report-action--primary">Actualizar informe</a>
                <button type="button" onclick="window.print()" class="no-print report-action">Exportar informe PDF</button>
            </div>
        </section>

        <section class="report-metric-strip report-print-card">
            <div class="report-metric-strip__title">
                <p class="report-eyebrow text-slate-500">Resumen cuantitativo</p>
                <h2>Indicadores del periodo</h2>
            </div>
            <div class="report-metric-strip__grid">
                @foreach($kpiDeck as $card)
                    @php $style = $toneMap[$card['tone']] ?? $toneMap['blue']; @endphp
                    <article class="report-metric-cell">
                        <span class="report-metric-cell__icon {{ $style['icon'] }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $card['icon'] }}"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p>{{ $card['label'] }}</p>
                            <strong>{{ $card['value'] }}</strong>
                            <span class="{{ $style['text'] }}">{{ $card['helper'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="report-index report-print-card">
            <div>
                <p class="report-eyebrow text-blue-600">Contenido del informe</p>
                <h2 class="report-title">Bloques de análisis incluidos</h2>
            </div>
            <div class="report-index__grid">
                <span>01 Tendencia de agenda</span>
                <span>02 Lectura ejecutiva</span>
                <span>03 Ingresos por servicio</span>
                <span>04 Pacientes por especie</span>
                <span>05 Acciones pendientes</span>
                <span>06 Producción por módulo</span>
                <span>07 Bitácora clínica</span>
                <span>08 Pacientes frecuentes</span>
            </div>
        </section>

        <section data-report-charts='@json($reportChartPayload)' class="report-board">
            <article class="report-card report-card--main report-span-8 report-print-card">
                <div class="report-card-header">
                    <div>
                        <p class="report-eyebrow text-blue-600">01 · Tendencia principal</p>
                        <h2 class="report-title">Agenda y atenciones</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Visualiza la relación entre citas programadas y atenciones registradas durante el periodo.</p>
                    </div>
                    <div class="report-legend-group">
                        <span class="report-legend report-legend--blue">Atenciones</span>
                        <span class="report-legend report-legend--teal">Citas</span>
                    </div>
                </div>
                <div class="report-main-chart-wrap">
                    <canvas data-report-overview-canvas class="report-main-chart"></canvas>
                    <div data-report-canvas-tooltip data-visible="false" class="report-canvas-tooltip"></div>
                </div>
            </article>

            <aside class="report-card report-span-4 report-print-card">
                <div class="report-card-header compact">
                    <div>
                        <p class="report-eyebrow text-emerald-600">02 · Lectura ejecutiva</p>
                        <h2 class="report-title">Estado del periodo</h2>
                    </div>
                    <span class="report-score" style="--score: {{ $controlScore }}%;"><span>{{ $controlScore }}</span></span>
                </div>
                <div class="space-y-3 p-5 pt-0">
                    <div class="rounded-[20px] border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Nivel de control</p>
                        <p class="mt-1 text-xl font-black text-slate-950">{{ $controlLabel }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Basado en alertas, controles y cobros pendientes.</p>
                    </div>
                    @foreach($lecturaEjecutiva as $item)
                        <div class="rounded-[18px] border border-slate-200 bg-white p-3">
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">{{ $item['title'] }}</p>
                            <p class="mt-1 text-lg font-black text-slate-950">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </aside>

            <article class="report-card report-span-7 report-print-card">
                <div class="report-card-header">
                    <div>
                        <p class="report-eyebrow text-emerald-600">03 · Ingresos</p>
                        <h2 class="report-title">Servicios con mayor ingreso</h2>
                    </div>
                    <span class="report-pill">Top 5</span>
                </div>
                <div class="grid gap-5 p-5 pt-0 lg:grid-cols-[280px_minmax(0,1fr)]">
                    <div data-service-detail class="report-service-detail"></div>
                    <div class="space-y-3">
                        @forelse($topServicios as $item)
                            @php $width = round(($item['value'] / $topTotal) * 100); @endphp
                            <button type="button" data-service-row data-label="{{ $item['label'] }}" data-value="{{ $item['value'] }}" class="report-service-row w-full">
                                <span class="flex items-center justify-between gap-3 text-sm font-black text-slate-700">
                                    <span class="truncate">{{ $item['label'] }}</span>
                                    <span>S/ {{ number_format($item['value'], 2) }}</span>
                                </span>
                                <span class="mt-2 block h-3 rounded-full bg-slate-100"><span class="block h-3 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400" style="width: {{ max(5, $width) }}%"></span></span>
                            </button>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center text-sm font-semibold text-slate-500">Sin ventas detalladas.</div>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="report-card report-span-5 report-print-card" data-species-total="{{ $speciesTotal }}">
                <div class="report-card-header compact">
                    <div>
                        <p class="report-eyebrow text-violet-600">04 · Pacientes</p>
                        <h2 class="report-title">Distribución por especie</h2>
                    </div>
                </div>
                <div class="grid gap-4 p-5 pt-0 sm:grid-cols-[160px_minmax(0,1fr)] sm:items-center xl:grid-cols-1">
                    <div class="relative mx-auto h-40 w-40 rounded-full shadow-inner" style="background: conic-gradient({{ $speciesGradient }});">
                        <div class="absolute inset-8 flex flex-col items-center justify-center rounded-full bg-white text-center shadow-inner">
                            <span data-species-center-value class="text-3xl font-black text-slate-950">{{ number_format($speciesTotal) }}</span>
                            <span data-species-center-label class="text-xs font-bold text-slate-400">Total</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @forelse($especiesReporte->values() as $index => $item)
                            @php $percent = round(($item['value'] / $speciesTotal) * 100); @endphp
                            <button type="button" data-species-row data-label="{{ $item['label'] }}" data-value="{{ $item['value'] }}" class="report-species-row w-full">
                                <span class="flex min-w-0 items-center gap-2"><span class="h-3 w-3 rounded-full" style="background: {{ $speciesColors[$index % count($speciesColors)] }}"></span><span class="truncate">{{ $item['label'] }}</span></span>
                                <span>{{ $item['value'] }} <span class="text-slate-400">({{ $percent }}%)</span></span>
                            </button>
                        @empty
                            <p class="text-center text-sm font-semibold text-slate-500">Sin especies atendidas.</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="report-card report-span-4 report-print-card">
                <div class="report-card-header compact">
                    <div>
                        <p class="report-eyebrow text-rose-600">05 · Prioridades</p>
                        <h2 class="report-title">Acciones pendientes</h2>
                    </div>
                </div>
                <div class="space-y-3 p-5 pt-0">
                    @foreach($executiveActions as $action)
                        @php $style = $toneMap[$action['tone']] ?? $toneMap['blue']; @endphp
                        <a href="{{ $action['url'] }}" class="report-priority {{ $style['soft'] }}">
                            <span>
                                <span class="block text-sm font-black text-slate-900">{{ $action['label'] }}</span>
                                <span class="block text-xs font-bold text-slate-500">{{ $action['detail'] }}</span>
                            </span>
                            <span class="rounded-xl bg-white px-3 py-1.5 text-sm font-black text-slate-950 shadow-sm">{{ $action['value'] }}</span>
                        </a>
                    @endforeach
                </div>
            </article>

            <article class="report-card report-span-8 report-print-card">
                <div class="report-card-header compact">
                    <div>
                        <p class="report-eyebrow text-blue-600">06 · Producción</p>
                        <h2 class="report-title">Actividad por módulo</h2>
                    </div>
                    <span class="report-pill">{{ $periodLabel }}</span>
                </div>
                <div class="report-module-grid p-5 pt-0">
                    @foreach($bloquesClinicos as $block)
                        <a href="{{ $block['url'] }}" class="report-module-card">
                            <span>
                                <span class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">{{ $block['helper'] }}</span>
                                <span class="mt-2 block text-3xl font-black text-slate-950">{{ number_format($block['value']) }}</span>
                                <span class="mt-1 block text-sm font-black text-slate-700">{{ $block['label'] }}</span>
                            </span>
                            <span class="report-module-arrow">↗</span>
                        </a>
                    @endforeach
                </div>
            </article>

            <article class="report-card report-span-8 report-print-card">
                <div class="report-card-header compact">
                    <div>
                        <p class="report-eyebrow text-slate-500">07 · Detalle</p>
                        <h2 class="report-title">Últimas atenciones</h2>
                    </div>
                    <a href="{{ route('historias-clinicas.index') }}" class="report-action">Ver historial</a>
                </div>
                <div class="overflow-x-auto p-5 pt-0">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                            <tr><th class="pb-3 pr-4">Fecha</th><th class="pb-3 pr-4">Cliente</th><th class="pb-3 pr-4">Mascota</th><th class="pb-3 pr-4">Resumen clínico</th><th class="pb-3">Estado</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($ultimasAtenciones as $historia)
                                <tr>
                                    <td class="py-3 pr-4 font-black text-slate-700">{{ optional($historia->fecha)->format('d/m/Y') }}</td>
                                    <td class="py-3 pr-4 font-semibold text-slate-600">{{ optional(optional($historia->mascota)->cliente)->nombre ?: 'Sin propietario' }}</td>
                                    <td class="py-3 pr-4 font-black text-slate-950">{{ optional($historia->mascota)->nombre ?: 'Sin mascota' }}</td>
                                    <td class="py-3 pr-4 font-semibold text-slate-500">{{ Illuminate\Support\Str::limit($historia->diagnostico ?: $historia->observaciones ?: 'Atención registrada', 90) }}</td>
                                    <td class="py-3"><span class="rounded-xl bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-700">Completado</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-10 text-center font-semibold text-slate-500">No hay atenciones para mostrar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="report-card report-span-4 report-print-card">
                <div class="report-card-header compact">
                    <div>
                        <p class="report-eyebrow text-amber-600">08 · Recurrencia</p>
                        <h2 class="report-title">Pacientes frecuentes</h2>
                    </div>
                </div>
                <div class="space-y-3 p-5 pt-0">
                    @forelse($pacientesFrecuentes as $patient)
                        <div class="report-patient-row">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-black text-slate-900">{{ $patient->mascota ?: 'Sin mascota' }}</span>
                                <span class="block truncate text-xs font-bold uppercase tracking-[0.12em] text-slate-400">{{ $patient->cliente ?: 'Sin propietario' }}</span>
                            </span>
                            <span class="rounded-xl bg-white px-3 py-1.5 text-sm font-black text-slate-950 shadow-sm">{{ $patient->total }}</span>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center text-sm font-semibold text-slate-500">Sin pacientes frecuentes.</p>
                    @endforelse
                </div>
            </article>
        </section>
        </div>

        <section class="report-print-document">
            <header class="print-cover">
                <div class="print-brand">
                    <img src="{{ asset('img/logo.png') }}" alt="Dra. Alfaro">
                    <div>
                        <h1>DRA. ALFARO</h1>
                        <p>Sistema de gestión veterinaria</p>
                    </div>
                </div>
                <div class="print-report-code">
                    <span>REPORTE GERENCIAL</span>
                    <strong>{{ $reportDate }}</strong>
                </div>
            </header>

            <section class="print-title-block">
                <p>Informe de gestión veterinaria</p>
                <h2>Análisis operativo, clínico y financiero</h2>
                <div class="print-meta-grid">
                    <div><span>Periodo evaluado</span><strong>{{ $periodLabel }}</strong></div>
                    <div><span>Fecha de emisión</span><strong>{{ $reportDate }} · {{ $reportHour }}</strong></div>
                    <div><span>Estado de control</span><strong>{{ $controlLabel }} · {{ $controlScore }}/100</strong></div>
                    <div><span>Conversión agenda-atención</span><strong>{{ $conversionSafe }}%</strong></div>
                </div>
            </section>

            <section class="print-section">
                <h3>1. Resumen ejecutivo</h3>
                <p>
                    El presente reporte consolida los principales indicadores del sistema veterinario durante el periodo seleccionado.
                    Su finalidad es facilitar la revisión de la productividad clínica, ingresos, agenda, controles preventivos y riesgos operativos.
                </p>
                <div class="print-summary-grid">
                    @foreach($lecturaEjecutiva as $item)
                        <div>
                            <span>{{ $item['title'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                            <p>{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="print-section">
                <h3>2. Indicadores principales</h3>
                <table class="print-table">
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th>Resultado</th>
                            <th>Lectura</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kpiDeck as $card)
                            <tr>
                                <td>{{ $card['label'] }}</td>
                                <td>{{ $card['value'] }}</td>
                                <td>{{ $card['helper'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="print-section">
                <h3>3. Agenda y atenciones</h3>
                <table class="print-table print-table--compact">
                    <thead>
                        <tr>
                            <th>Día</th>
                            @foreach($actividadSemanal as $day)
                                <th>{{ $day['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Atenciones</td>
                            @foreach($actividadSemanal as $day)
                                <td>{{ $day['atenciones'] }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Citas</td>
                            @foreach($actividadSemanal as $day)
                                <td>{{ $day['citas'] }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
                <p class="print-note">La comparación permite verificar si las citas programadas se convierten en atenciones registradas dentro del sistema.</p>
            </section>

            <section class="print-section print-two-columns">
                <div>
                    <h3>4. Ingresos por servicio</h3>
                    <table class="print-table">
                        <thead><tr><th>Servicio</th><th>Monto</th></tr></thead>
                        <tbody>
                            @forelse($topServicios as $item)
                                <tr><td>{{ $item['label'] }}</td><td>S/ {{ number_format($item['value'], 2) }}</td></tr>
                            @empty
                                <tr><td colspan="2">Sin ventas detalladas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3>5. Distribución por especie</h3>
                    <table class="print-table">
                        <thead><tr><th>Especie</th><th>Cantidad</th><th>%</th></tr></thead>
                        <tbody>
                            @forelse($especiesReporte as $item)
                                @php $percent = round(($item['value'] / $speciesTotal) * 100); @endphp
                                <tr><td>{{ $item['label'] }}</td><td>{{ $item['value'] }}</td><td>{{ $percent }}%</td></tr>
                            @empty
                                <tr><td colspan="3">Sin especies atendidas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="print-section page-break-before">
                <h3>6. Actividad por módulo</h3>
                <table class="print-table">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Registros</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bloquesClinicos as $block)
                            <tr>
                                <td>{{ $block['label'] }}</td>
                                <td>{{ number_format($block['value']) }}</td>
                                <td>{{ $block['helper'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="print-section print-two-columns">
                <div>
                    <h3>7. Acciones pendientes</h3>
                    <table class="print-table">
                        <thead><tr><th>Prioridad</th><th>Valor</th><th>Estado</th></tr></thead>
                        <tbody>
                            @foreach($executiveActions as $action)
                                <tr><td>{{ $action['label'] }}</td><td>{{ $action['value'] }}</td><td>{{ $action['detail'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3>8. Pacientes frecuentes</h3>
                    <table class="print-table">
                        <thead><tr><th>Mascota</th><th>Cliente</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse($pacientesFrecuentes as $patient)
                                <tr><td>{{ $patient->mascota ?: 'Sin mascota' }}</td><td>{{ $patient->cliente ?: 'Sin propietario' }}</td><td>{{ $patient->total }}</td></tr>
                            @empty
                                <tr><td colspan="3">Sin pacientes frecuentes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="print-section">
                <h3>9. Últimas atenciones registradas</h3>
                <table class="print-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Mascota</th>
                            <th>Resumen clínico</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasAtenciones as $historia)
                            <tr>
                                <td>{{ optional($historia->fecha)->format('d/m/Y') }}</td>
                                <td>{{ optional(optional($historia->mascota)->cliente)->nombre ?: 'Sin propietario' }}</td>
                                <td>{{ optional($historia->mascota)->nombre ?: 'Sin mascota' }}</td>
                                <td>{{ Illuminate\Support\Str::limit($historia->diagnostico ?: $historia->observaciones ?: 'Atención registrada', 110) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No hay atenciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="print-section print-conclusion">
                <h3>10. Conclusión gerencial</h3>
                <p>
                    El periodo evaluado presenta un estado de control <strong>{{ strtolower($controlLabel) }}</strong>.
                    Se recomienda priorizar las acciones pendientes, revisar la conversión entre citas y atenciones,
                    y mantener seguimiento sobre los servicios que generan mayor ingreso para sostener una gestión clínica ordenada.
                </p>
            </section>

            <footer class="print-footer">
                <span>DRA. ALFARO · Sistema de Gestión Veterinaria</span>
                <span>Reporte generado automáticamente</span>
            </footer>
        </section>
    </div>
</div>
</x-app-layout>
