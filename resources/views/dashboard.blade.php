<x-app-layout>
@php
    $todayLabel = now()->format('d/m/Y');
    $todayValue = now()->toDateString();
    $agendaVisible = $proximasCitas->take(5);
    $nextTime = $siguientePaciente ? Illuminate\Support\Str::of($siguientePaciente->hora)->substr(0, 5)->toString() : null;
    $weekMax = max(1, (int) $actividadSemanal->max(fn ($item) => max($item['citas'], $item['atenciones'])));
    $weekCount = max(1, $actividadSemanal->count() - 1);
    $linePoints = $actividadSemanal->values()->map(function ($item, $index) use ($weekMax, $weekCount) {
        $x = $weekCount > 0 ? round(($index / $weekCount) * 100, 2) : 50;
        $y = round(88 - (($item['citas'] / $weekMax) * 68), 2);
        return $x . ',' . $y;
    })->implode(' ');
    $areaPoints = '0,100 ' . $linePoints . ' 100,100';

    $speciesColors = ['#2563eb', '#14b8a6', '#f59e0b', '#8b5cf6', '#94a3b8'];
    $currentSpecies = 0;
    $donutParts = [];
    foreach ($especiesResumen->values() as $index => $item) {
        $start = $currentSpecies;
        $end = min(100, $currentSpecies + (int) $item['porcentaje']);
        $donutParts[] = $speciesColors[$index % count($speciesColors)] . ' ' . $start . '% ' . $end . '%';
        $currentSpecies = $end;
    }
    if ($currentSpecies < 100) {
        $donutParts[] = '#e2e8f0 ' . $currentSpecies . '% 100%';
    }
    $speciesGradient = count($donutParts) ? implode(', ', $donutParts) : '#e2e8f0 0% 100%';

    $metricCards = [
        ['label' => 'Clientes registrados', 'value' => number_format($totalClientes), 'helper' => 'Base general activa', 'tone' => 'blue', 'icon' => 'M16 21v-2a4 4 0 0 0-8 0v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z'],
        ['label' => 'Mascotas activas', 'value' => number_format($totalMascotas), 'helper' => 'Pacientes registrados', 'tone' => 'teal', 'icon' => 'M8.5 14.5c-1.6 0-3 1.1-3 2.6 0 1.3 1 2.4 2.4 2.4h8.2c1.4 0 2.4-1.1 2.4-2.4 0-1.5-1.4-2.6-3-2.6-.9 0-1.4.5-2 .5s-1.1-.5-2-.5ZM6.5 10.5a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6Zm11 0a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6Zm-8-3.5a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6Zm5 0a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6Z'],
        ['label' => 'Citas de hoy', 'value' => number_format($citasHoy), 'helper' => $nextTime ? 'Proxima cita: ' . $nextTime : 'Agenda disponible', 'tone' => 'violet', 'icon' => 'M8 2v4M16 2v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z'],
        ['label' => 'Ingresos del mes', 'value' => 'S/ ' . number_format($ingresosMes, 2), 'helper' => $ventasHoy . ' ventas registradas hoy', 'tone' => 'green', 'icon' => 'M12 2v20M17 5.5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6'],
    ];

    $toneMap = [
        'blue' => ['circle' => 'from-blue-600 to-blue-400', 'text' => 'text-blue-600', 'soft' => 'bg-blue-50 border-blue-100'],
        'teal' => ['circle' => 'from-teal-500 to-cyan-400', 'text' => 'text-teal-600', 'soft' => 'bg-teal-50 border-teal-100'],
        'violet' => ['circle' => 'from-violet-600 to-indigo-400', 'text' => 'text-violet-600', 'soft' => 'bg-violet-50 border-violet-100'],
        'green' => ['circle' => 'from-green-600 to-lime-500', 'text' => 'text-green-600', 'soft' => 'bg-green-50 border-green-100'],
        'amber' => ['circle' => 'from-amber-500 to-orange-400', 'text' => 'text-amber-600', 'soft' => 'bg-amber-50 border-amber-100'],
        'rose' => ['circle' => 'from-rose-500 to-red-400', 'text' => 'text-rose-600', 'soft' => 'bg-rose-50 border-rose-100'],
    ];

    $alertItems = [
        ['label' => 'Stock bajo', 'detail' => $stockBajo . ' productos requieren revisión', 'value' => $stockBajo, 'tone' => 'rose', 'url' => route('productos.index')],
        ['label' => 'Vacunas pendientes', 'detail' => $vacunasVencidas . ' vencidas y ' . $vacunasHoy . ' para hoy', 'value' => $vacunasVencidas + $vacunasHoy, 'tone' => 'amber', 'url' => route('vacunas.index')],
        ['label' => 'Controles clínicos', 'detail' => $seguimientosVencidos . ' vencidos y ' . $seguimientosHoy . ' para hoy', 'value' => $seguimientosVencidos + $seguimientosHoy, 'tone' => 'blue', 'url' => route('seguimientos.index')],
    ];

    $quickActions = [
        ['label' => 'Nueva cita', 'detail' => 'Programar paciente', 'url' => route('citas.index', ['open_create' => 1]), 'tone' => 'blue', 'icon' => 'M12 5v14M5 12h14'],
        ['label' => 'Atención rápida', 'detail' => 'Paciente sin cita', 'url' => route('atencion-rapida.index', ['open_create' => 1]), 'tone' => 'green', 'icon' => 'M13 2 4 14h7l-1 8 9-12h-7l1-8Z'],
        ['label' => 'Vacuna', 'detail' => 'Aplicar o programar', 'url' => route('vacunas.index'), 'tone' => 'amber', 'icon' => 'M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6l-7-3Z'],
        ['label' => 'Control', 'detail' => 'Revisar retornos', 'url' => route('seguimientos.index'), 'tone' => 'violet', 'icon' => 'M12 8v4l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['label' => 'Venta', 'detail' => 'Registrar cobro', 'url' => route('ventas.index'), 'tone' => 'teal', 'icon' => 'M12 2v20M17 5.5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6'],
        ['label' => 'Reportes', 'detail' => 'Ver análisis', 'url' => route('reportes.index'), 'tone' => 'blue', 'icon' => 'M4 19V5M4 19h16M8 16v-5M12 16V8M16 16v-8'],
    ];

    $workQueue = collect([
        ['label' => 'Citas por atender hoy', 'value' => $citasPendientesHoy, 'helper' => 'Prioridad de recepción', 'url' => route('citas.index', ['estado' => 'pendiente', 'fecha' => $todayValue]), 'tone' => 'blue'],
        ['label' => 'Controles vencidos', 'value' => $seguimientosVencidos, 'helper' => 'Pacientes que deben volver', 'url' => route('seguimientos.index'), 'tone' => 'rose'],
        ['label' => 'Vacunas vencidas', 'value' => $vacunasVencidas, 'helper' => 'Prevención atrasada', 'url' => route('vacunas.index'), 'tone' => 'amber'],
        ['label' => 'Productos agotados', 'value' => $productosAgotados, 'helper' => 'Reposición necesaria', 'url' => route('productos.index'), 'tone' => 'rose'],
    ]);
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-5">
        <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-slate-950">Dashboard</h1>
                <p class="mt-1 text-base font-medium text-slate-500">Centro operativo para atender, decidir y actuar rápido.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form action="{{ route('citas.index') }}" method="GET" class="flex h-12 w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 shadow-sm sm:w-[24rem]">
                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input name="search" class="w-full border-0 bg-transparent p-0 text-sm font-semibold text-slate-700 placeholder:text-slate-400 focus:ring-0" placeholder="Buscar mascota, cliente o DNI...">
                </form>
                <div class="inline-flex h-12 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm">
                    <svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M4 9h16M5 5h14v16H5z"/></svg>
                    Hoy, {{ $todayLabel }}
                </div>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
            <article class="overflow-hidden rounded-[30px] border border-blue-100 bg-gradient-to-br from-blue-600 via-blue-600 to-cyan-500 p-6 text-white shadow-[0_24px_60px_-34px_rgba(37,99,235,0.75)]">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-center">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-blue-100">Mesa operativa</p>
                        <h2 class="mt-3 text-3xl font-black leading-tight">
                            {{ $siguientePaciente ? 'Siguiente paciente: ' . optional($siguientePaciente->mascota)->nombre : 'No hay paciente pendiente inmediato' }}
                        </h2>
                        <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-blue-50">
                            Usa este panel para entrar directo al trabajo diario: agenda, atención rápida, vacunas, controles, ventas y reportes sin perder tiempo buscando módulos.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('citas.index', ['estado' => 'pendiente', 'fecha' => $todayValue]) }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-black text-blue-700 shadow-lg shadow-blue-950/10 transition hover:-translate-y-0.5 hover:bg-blue-50">
                                Ver agenda de hoy
                            </a>
                            <a href="{{ route('atencion-rapida.index', ['open_create' => 1]) }}" class="inline-flex items-center justify-center rounded-2xl border border-white/35 bg-white/15 px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-white/25">
                                Atender sin cita
                            </a>
                        </div>
                    </div>

                    <div class="rounded-[26px] border border-white/25 bg-white/15 p-4 backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-100">Estado del día</p>
                        <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-2xl bg-white/15 px-3 py-4">
                                <p class="text-2xl font-black">{{ $resumenOperativo['pacientes_hoy'] }}</p>
                                <p class="mt-1 text-[11px] font-bold text-blue-50">Pacientes</p>
                            </div>
                            <div class="rounded-2xl bg-white/15 px-3 py-4">
                                <p class="text-2xl font-black">{{ $resumenOperativo['pendientes_hoy'] }}</p>
                                <p class="mt-1 text-[11px] font-bold text-blue-50">Pendientes</p>
                            </div>
                            <div class="rounded-2xl bg-white/15 px-3 py-4">
                                <p class="text-2xl font-black">{{ $resumenOperativo['atenciones_hoy'] }}</p>
                                <p class="mt-1 text-[11px] font-bold text-blue-50">Atendidos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-blue-600">Acciones rápidas</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950">Atajos de trabajo</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-500">Operativo</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    @foreach($quickActions as $action)
                        @php $style = $toneMap[$action['tone']]; @endphp
                        <a href="{{ $action['url'] }}" class="group rounded-[20px] border border-slate-200 bg-slate-50 px-4 py-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white hover:shadow-lg hover:shadow-slate-200/70">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $style['circle'] }} text-white shadow-sm transition group-hover:scale-105">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $action['icon'] }}"/></svg>
                            </span>
                            <span class="mt-3 block text-sm font-black text-slate-950">{{ $action['label'] }}</span>
                            <span class="mt-1 block text-xs font-semibold text-slate-500">{{ $action['detail'] }}</span>
                        </a>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-4">
            @foreach($workQueue as $item)
                @php $style = $toneMap[$item['tone']]; @endphp
                <a href="{{ $item['url'] }}" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/80">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-slate-950">{{ $item['label'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item['helper'] }}</p>
                        </div>
                        <span class="rounded-2xl px-4 py-2 text-2xl font-black {{ $style['text'] }} {{ $style['soft'] }}">{{ $item['value'] }}</span>
                    </div>
                </a>
            @endforeach
        </section>

        <section class="grid gap-4 xl:grid-cols-4">
            @foreach($metricCards as $card)
                @php $style = $toneMap[$card['tone']]; @endphp
                <article class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_18px_45px_-34px_rgba(15,23,42,0.45)]">
                    <div class="flex items-center gap-5">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gradient-to-br {{ $style['circle'] }} text-white shadow-lg shadow-slate-200">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $card['icon'] }}"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-1 truncate text-3xl font-black text-slate-950">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs font-bold {{ $style['text'] }}">{{ $card['helper'] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-[1.15fr_.85fr_1fr]">
            <article class="rounded-[26px] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V5M4 19h16M8 15l3-3 3 2 5-7"/></svg></span>
                        <h2 class="text-lg font-black text-slate-950">Citas por semana</h2>
                    </div>
                    <span class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500">Ultimos 7 días</span>
                </div>
                <div class="px-5 py-5">
                    <div class="relative h-56 rounded-[22px] bg-gradient-to-b from-blue-50 to-white px-3 py-3 ring-1 ring-slate-100">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                            <polygon points="{{ $areaPoints }}" fill="rgba(37,99,235,.10)"></polygon>
                            <polyline points="{{ $linePoints }}" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></polyline>
                            @foreach($actividadSemanal->values() as $index => $day)
                                @php
                                    $x = $weekCount > 0 ? round(($index / $weekCount) * 100, 2) : 50;
                                    $y = round(88 - (($day['citas'] / $weekMax) * 68), 2);
                                @endphp
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="2.3" fill="#2563eb" stroke="white" stroke-width="1.5"></circle>
                            @endforeach
                        </svg>
                    </div>
                    <div class="mt-3 grid grid-cols-7 gap-1 text-center text-[11px] font-bold text-slate-400">
                        @foreach($actividadSemanal as $day)
                            <span>{{ $day['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5c-1.6 0-3 1.1-3 2.6 0 1.3 1 2.4 2.4 2.4h8.2c1.4 0 2.4-1.1 2.4-2.4 0-1.5-1.4-2.6-3-2.6-.9 0-1.4.5-2 .5s-1.1-.5-2-.5Z"/><circle cx="6.5" cy="8.7" r="1.8"/><circle cx="17.5" cy="8.7" r="1.8"/><circle cx="9.5" cy="5.2" r="1.8"/><circle cx="14.5" cy="5.2" r="1.8"/></svg></span>
                    <h2 class="text-lg font-black text-slate-950">Distribucion de especies</h2>
                </div>
                <div class="grid gap-5 px-5 py-5 sm:grid-cols-[150px_1fr] sm:items-center xl:grid-cols-1 2xl:grid-cols-[150px_1fr]">
                    <div class="relative mx-auto h-40 w-40 rounded-full" style="background: conic-gradient({{ $speciesGradient }});">
                        <div class="absolute inset-8 flex flex-col items-center justify-center rounded-full bg-white shadow-inner">
                            <span class="text-3xl font-black text-slate-950">{{ number_format($totalMascotas) }}</span>
                            <span class="text-xs font-bold text-slate-400">Total</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse($especiesResumen->take(5)->values() as $index => $item)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="flex min-w-0 items-center gap-2 font-bold text-slate-700"><span class="h-3 w-3 shrink-0 rounded-full" style="background: {{ $speciesColors[$index % count($speciesColors)] }}"></span><span class="truncate">{{ $item['nombre'] }}</span></span>
                                <span class="font-black text-slate-950">{{ $item['total'] }} <span class="font-bold text-slate-400">({{ $item['porcentaje'] }}%)</span></span>
                            </div>
                        @empty
                            <p class="text-sm font-semibold text-slate-500">Sin especies registradas.</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5.5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg></span>
                        <h2 class="text-lg font-black text-slate-950">Ingresos mensuales</h2>
                    </div>
                    <span class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500">Este año</span>
                </div>
                <div class="px-5 py-5">
                    <div class="flex h-56 items-end gap-2 rounded-[22px] bg-gradient-to-b from-emerald-50 to-white px-4 py-4 ring-1 ring-slate-100">
                        @foreach($ingresosMensuales as $month)
                            @php $height = max(8, round(($month['value'] / $maxIngresosMensuales) * 170)); @endphp
                            <div class="flex flex-1 flex-col items-center justify-end gap-2">
                                <span class="w-full rounded-t-xl bg-teal-400/70 transition hover:bg-teal-500" style="height: {{ $height }}px" title="S/ {{ number_format($month['value'], 2) }}"></span>
                                <span class="text-[10px] font-bold text-slate-400">{{ $month['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-5 xl:grid-cols-[1.1fr_.8fr_.9fr]">
            <article class="rounded-[26px] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-lg font-black text-slate-950">Proximas citas</h2>
                    <a href="{{ route('citas.index') }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-black text-blue-700 transition hover:bg-blue-100">Ver todas</a>
                </div>
                <div class="overflow-x-auto px-5 py-4">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                            <tr><th class="pb-3 pr-4">Hora</th><th class="pb-3 pr-4">Mascota</th><th class="pb-3 pr-4">Propietario</th><th class="pb-3 pr-4">Veterinario</th><th class="pb-3">Estado</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($agendaVisible as $cita)
                                @php
                                    $photo = optional($cita->mascota)->foto ? asset('storage/' . $cita->mascota->foto) : asset('storage/default.png');
                                    $statusClass = $cita->estado === 'completada' ? 'bg-emerald-100 text-emerald-700' : ($cita->estado === 'cancelada' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700');
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4 font-black text-slate-700">{{ Illuminate\Support\Str::of($cita->hora)->substr(0, 5)->toString() }}</td>
                                    <td class="py-3 pr-4"><div class="flex items-center gap-3"><img src="{{ $photo }}" class="h-9 w-9 rounded-full object-cover" onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';"><div><p class="font-black text-slate-950">{{ optional($cita->mascota)->nombre ?: 'Sin nombre' }}</p><p class="text-xs font-semibold text-slate-400">{{ optional($cita->mascota)->tipo_animal ?: 'Paciente' }}</p></div></div></td>
                                    <td class="py-3 pr-4 font-semibold text-slate-600">{{ optional(optional($cita->mascota)->cliente)->nombre ?: 'Sin propietario' }}</td>
                                    <td class="py-3 pr-4 font-semibold text-slate-600">{{ optional(optional($cita->veterinario)->user)->name ?: 'DRA ALFARO' }}</td>
                                    <td class="py-3"><span class="rounded-xl px-3 py-1.5 text-xs font-black {{ $statusClass }}">{{ ucfirst($cita->estado) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-10 text-center font-semibold text-slate-500">No hay próximas citas pendientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="text-lg font-black text-slate-950">Alertas</h2></div>
                <div class="space-y-3 px-5 py-5">
                    @foreach($alertItems as $alert)
                        @php $style = $toneMap[$alert['tone']]; @endphp
                        <a href="{{ $alert['url'] }}" class="flex items-center justify-between gap-4 rounded-[20px] border px-4 py-4 transition hover:-translate-y-0.5 {{ $style['soft'] }}">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white {{ $style['text'] }} shadow-sm"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 2.5 18a2 2 0 0 0 1.8 3h15.4a2 2 0 0 0 1.8-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg></span>
                                <span><span class="block text-sm font-black text-slate-900">{{ $alert['label'] }}</span><span class="mt-1 block text-xs font-semibold text-slate-500">{{ $alert['detail'] }}</span></span>
                            </div>
                            <span class="text-xl font-black {{ $style['text'] }}">{{ $alert['value'] }}</span>
                        </a>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="text-lg font-black text-slate-950">Actividad reciente</h2></div>
                <div class="space-y-1 px-5 py-4">
                    @forelse($ultimasAtenciones as $historia)
                        <div class="flex gap-3 border-b border-slate-100 py-3 last:border-0">
                            <span class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6M12 9v6M5 4h14v16H5z"/></svg></span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-slate-950">{{ optional($historia->mascota)->nombre ?: 'Mascota' }} atendido</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ Illuminate\Support\Str::limit($historia->diagnostico ?: $historia->observaciones ?: 'Atención registrada', 80) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm font-semibold text-slate-500">Sin actividad reciente.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</div>
</x-app-layout>

