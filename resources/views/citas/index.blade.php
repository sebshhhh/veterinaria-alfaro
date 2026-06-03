<x-app-layout>
@php
    $stats = $stats ?? [];
    $estadoActual = request('estado');
    $prefillMascotaId = $prefillMascotaId ?? null;
    $selectedMascota = $selectedMascota ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $accion = $accion ?? null;
    $attendPresets = [];
    $vacunaCatalogo = collect($vacunaCatalogo ?? []);
    $baseFilters = array_filter([
        'search' => request('search'),
        'fecha' => request('fecha'),
        'mascota_id' => request('mascota_id'),
    ], fn ($value) => filled($value));
    $totalCitas = $stats['total'] ?? 0;
    $pendientesCitas = $stats['pendientes'] ?? 0;
    $citasHoy = $stats['hoy'] ?? 0;
    $citasCompletadas = $stats['completadas'] ?? 0;
    $citasCanceladas = $stats['canceladas'] ?? 0;
    $citasMascotas = $stats['mascotas'] ?? 0;
    $agendaPercent = $totalCitas > 0 ? round(($citasCompletadas / $totalCitas) * 100) : 0;
    $kpiCards = [
        ['label' => 'Total citas', 'value' => $totalCitas, 'helper' => 'Agenda general', 'tone' => 'blue', 'icon' => 'calendar'],
        ['label' => 'Pendientes', 'value' => $pendientesCitas, 'helper' => 'Por atender', 'tone' => 'amber', 'icon' => 'clock'],
        ['label' => 'Citas hoy', 'value' => $citasHoy, 'helper' => 'Para hoy', 'tone' => 'emerald', 'icon' => 'today'],
        ['label' => 'Completadas', 'value' => $citasCompletadas, 'helper' => 'Atendidas', 'tone' => 'rose', 'icon' => 'check'],
    ];
    $miniStats = [
        ['label' => 'Pacientes en agenda', 'value' => $citasMascotas, 'tone' => 'sky'],
        ['label' => 'Canceladas', 'value' => $citasCanceladas, 'tone' => 'slate'],
    ];
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-5">
        <section class="rounded-[30px] border border-slate-200 bg-white px-5 py-5 shadow-[0_24px_60px_-42px_rgba(15,23,42,0.24)] sm:px-6 xl:px-7">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-blue-600 to-cyan-500 text-white shadow-[0_18px_32px_-18px_rgba(37,99,235,0.85)]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-700">Operación diaria</p>
                            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Citas</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                Gestiona la agenda con una vista más ágil: resumen claro, filtros rápidos y tarjetas listas para atender, editar o reprogramar.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-3.5 py-2 text-sm font-semibold text-amber-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            {{ $pendientesCitas }} pendientes activas
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3.5 py-2 text-sm font-semibold text-emerald-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            {{ $citasHoy }} citas para hoy
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-white px-3.5 py-2 text-sm font-semibold text-sky-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                            {{ $citasMascotas }} pacientes en agenda
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-white px-3.5 py-2 text-sm font-semibold text-violet-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span>
                            {{ $agendaPercent }}% de avance
                        </span>
                    </div>

                    @if($selectedMascota)
                        <div class="mt-4 rounded-2xl border border-sky-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                            <p class="font-semibold text-sky-700">{{ $accion === 'control' ? 'Seguimiento sugerido' : 'Agenda filtrada por mascota' }}</p>
                            <p class="mt-1 leading-6">
                                {{ $accion === 'control'
                                    ? 'La agenda de ' . $selectedMascota->nombre . ' ya quedó lista para registrar su próximo control.'
                                    : 'Estas viendo solo las citas de ' . $selectedMascota->nombre . ' para continuar el flujo sin salir del módulo.' }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col items-stretch gap-3 xl:min-w-[360px] xl:max-w-[380px]">
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Agenda general</p>
                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-3xl font-bold text-slate-950">{{ $totalCitas }}</p>
                                <p class="mt-1 text-sm text-slate-500">citas registradas</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm">{{ $agendaPercent }}% avance</span>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('citas.index', ['fecha' => now()->format('Y-m-d')]) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-[22px] border border-sky-200 bg-white px-4 py-4 text-sm font-semibold text-sky-700 shadow-sm transition hover:border-sky-300 hover:bg-sky-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                            </svg>
                            Agenda de hoy
                        </a>
                        <button type="button"
                                onclick="openCitaModal('{{ $prefillMascotaId ?? '' }}')"
                                class="inline-flex items-center justify-center gap-2 rounded-[22px] bg-gradient-to-r from-blue-600 to-cyan-500 px-4 py-4 text-sm font-semibold text-white shadow-[0_18px_34px_-18px_rgba(37,99,235,0.75)] transition hover:brightness-105">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Nueva cita
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($kpiCards as $card)
                @php
                    $toneClasses = match($card['tone']) {
                        'amber' => 'border-amber-200 bg-gradient-to-br from-amber-50 to-white',
                        'emerald' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white',
                        'rose' => 'border-rose-200 bg-gradient-to-br from-rose-50 to-white',
                        'sky' => 'border-sky-200 bg-gradient-to-br from-sky-50 to-white',
                        'slate' => 'border-slate-200 bg-gradient-to-br from-slate-100 to-white',
                        default => 'border-blue-200 bg-gradient-to-br from-blue-50 to-white',
                    };
                    $valueClasses = match($card['tone']) {
                        'amber' => 'text-amber-700',
                        'emerald' => 'text-emerald-700',
                        'rose' => 'text-rose-700',
                        'sky' => 'text-sky-700',
                        'slate' => 'text-slate-700',
                        default => 'text-blue-700',
                    };
                    $iconWrapClasses = match($card['tone']) {
                        'amber' => 'bg-amber-500 text-white shadow-amber-200',
                        'emerald' => 'bg-emerald-500 text-white shadow-emerald-200',
                        'rose' => 'bg-rose-500 text-white shadow-rose-200',
                        'sky' => 'bg-sky-500 text-white shadow-sky-200',
                        'slate' => 'bg-slate-700 text-white shadow-slate-200',
                        default => 'bg-blue-600 text-white shadow-blue-200',
                    };
                @endphp
                <article class="rounded-[24px] border px-4 py-4 shadow-[0_20px_42px_-34px_rgba(15,23,42,0.2)] {{ $toneClasses }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-bold {{ $valueClasses }}">{{ $card['value'] }}</p>
                            <p class="mt-2 text-sm font-medium text-slate-500">{{ $card['helper'] }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl shadow-lg {{ $iconWrapClasses }}">
                            @switch($card['icon'])
                                @case('clock')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @break
                                @case('today')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3M16 2v3M4 7h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                    @break
                                @case('check')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                                    @break
                                @case('paw')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 11.5c.8-1.5-.2-3.8-1.6-4.4-1.4-.6-2.9 1-3.3 2.5-.3 1.3.2 2.8 1.5 3.2 1.1.4 2.7 0 3.4-1.3Zm7 0c-.8-1.5.2-3.8 1.6-4.4 1.4-.6 2.9 1 3.3 2.5.3 1.3-.2 2.8-1.5 3.2-1.1.4-2.7 0-3.4-1.3ZM9 17c.7-1.8 2.2-2.8 3-2.8s2.3 1 3 2.8c.7 1.7-.2 3-3 3s-3.7-1.3-3-3Zm.1-8.8c0-1.6-1-3.2-2.3-3.2s-2.3 1.6-2.3 3.2c0 1.6 1 2.8 2.3 2.8s2.3-1.2 2.3-2.8Zm10.2 0c0-1.6-1-3.2-2.3-3.2s-2.3 1.6-2.3 3.2c0 1.6 1 2.8 2.3 2.8s2.3-1.2 2.3-2.8Z" /></svg>
                                    @break
                                @case('x')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" /></svg>
                            @endswitch
                        </div>
                    </div>
                </article>
            @endforeach
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                @foreach($miniStats as $mini)
                    @php
                        $miniTone = $mini['tone'] === 'sky'
                            ? 'border-sky-200 bg-gradient-to-r from-sky-50 to-white text-sky-700'
                            : 'border-slate-200 bg-gradient-to-r from-slate-100 to-white text-slate-700';
                    @endphp
                    <article class="rounded-[24px] border px-4 py-4 shadow-[0_18px_36px_-34px_rgba(15,23,42,0.18)] {{ $miniTone }}">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $mini['label'] }}</p>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <p class="text-3xl font-bold">{{ $mini['value'] }}</p>
                            <span class="rounded-full bg-white/80 px-2.5 py-1 text-xs font-semibold text-slate-500">{{ $mini['tone'] === 'sky' ? 'Seguimiento' : 'Atencion' }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="module-block px-4 py-4 sm:px-5">
            <form method="GET" action="{{ route('citas.index') }}">
                <div class="grid gap-3 xl:grid-cols-[minmax(0,2fr)_220px_220px_auto] xl:items-end">
                    <div>
                        <label for="search" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Buscar cita</label>
                        <div class="flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition focus-within:border-blue-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-100">
                            <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por mascota, dueño o profesional..." class="ml-3 w-full border-0 bg-transparent p-0 text-base text-slate-700 placeholder:text-slate-400 focus:ring-0">
                        </div>
                    </div>
                    <div>
                        <label for="estado" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Estado</label>
                        <select id="estado" name="estado" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente</option>
                            <option value="completada" @selected(request('estado') === 'completada')>Completada</option>
                            <option value="cancelada" @selected(request('estado') === 'cancelada')>Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label for="fecha" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Fecha</label>
                        <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-base font-semibold text-white shadow-[0_18px_34px_-22px_rgba(37,99,235,0.9)] transition hover:bg-blue-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            Buscar
                        </button>
                        <a href="{{ route('citas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356m-1.636 1.635a9 9 0 1 0 2.339 9.34" />
                            </svg>
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="mt-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('citas.index', $baseFilters) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoActual ? 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoActual ? 'bg-slate-300' : 'bg-blue-500' }}"></span>
                        Todas ({{ $totalCitas }})
                    </a>
                    <a href="{{ route('citas.index', array_merge($baseFilters, ['estado' => 'pendiente'])) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoActual === 'pendiente' ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoActual === 'pendiente' ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
                        Pendientes ({{ $pendientesCitas }})
                    </a>
                    <a href="{{ route('citas.index', array_merge($baseFilters, ['estado' => 'completada'])) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoActual === 'completada' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoActual === 'completada' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        Completadas ({{ $citasCompletadas }})
                    </a>
                    <a href="{{ route('citas.index', array_merge($baseFilters, ['estado' => 'cancelada'])) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoActual === 'cancelada' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoActual === 'cancelada' ? 'bg-rose-500' : 'bg-slate-300' }}"></span>
                        Canceladas ({{ $citasCanceladas }})
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <a href="{{ route('citas.index', array_merge($baseFilters, ['fecha' => now()->format('Y-m-d')])) }}" class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 font-semibold text-sky-700 transition hover:bg-sky-100">
                        Ver hoy
                    </a>
                    <a href="{{ route('citas.index', array_merge($baseFilters, ['estado' => 'pendiente'])) }}" class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 font-semibold text-amber-700 transition hover:bg-amber-100">
                        Priorizar pendientes
                    </a>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 font-semibold text-slate-600">Mas recientes primero</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1.5 font-semibold text-blue-700">{{ $citas->total() }} resultados</span>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Listado operativo</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-950">Citas registradas</h2>
                </div>
                <div class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm">
                    Mostrando {{ $citas->firstItem() ?? 0 }} a {{ $citas->lastItem() ?? 0 }} de {{ $citas->total() }}
                </div>
            </div>

                @if($citas->count())
                    <div class="grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
                        @foreach($citas as $cita)
                            @php
                                $historia = $cita->historiaClinica;
                                $tratamiento = $historia?->tratamientos?->first();
                                $receta = $historia?->recetas?->first();
                                $seguimiento = $historia?->seguimientos?->first(function ($item) {
                                    return $item->tipo === 'clinico' && $item->origen === 'atencion';
                                });
                                $controlesDeControl = $cita->seguimientos ?? collect();
                                $controlPendiente = $controlesDeControl->first();
                                $isControlCita = $controlesDeControl->isNotEmpty();
                                $ultimoServicio = $ultimoServicioPorMascota->get($cita->mascota_id, []);
                                $vacunaAsociada = optional($cita->mascota)->vacunas?->first(function ($vacuna) use ($historia, $cita) {
                                    $fechaObjetivo = optional($historia?->fecha)->format('Y-m-d') ?: optional($cita->fecha)->format('Y-m-d');

                                    return optional($vacuna->fecha_aplicacion)->format('Y-m-d') === $fechaObjetivo;
                                }) ?? optional($cita->mascota)->vacunas?->first();
                                $vacunaNombre = optional($vacunaAsociada)->nombre;
                                $vacunaEsCatalogo = filled($vacunaNombre) && $vacunaCatalogo->contains($vacunaNombre);
                                $estadoClass = match($cita->estado) {
                                    'completada' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'cancelada' => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-amber-200 bg-amber-50 text-amber-700',
                                };
                                $cardShellClass = match($cita->estado) {
                                    'completada' => 'border-emerald-200 shadow-emerald-100/60',
                                    'cancelada' => 'border-rose-200 shadow-rose-100/60',
                                    default => 'border-amber-200 shadow-amber-100/60',
                                };
                                $isCompleted = $cita->estado === 'completada';
                                $isCancelled = $cita->estado === 'cancelada';
                                $isOpenCita = ! $isCompleted && ! $isCancelled;
                                $horaTexto = \Illuminate\Support\Str::of($cita->hora)->substr(0, 5)->toString();
                                $scheduledAt = optional($cita->fecha)->format('Y-m-d')
                                    ? \Illuminate\Support\Carbon::parse(optional($cita->fecha)->format('Y-m-d') . ' ' . $horaTexto)
                                    : null;
                                $canAttendCita = $isOpenCita && (!$scheduledAt || $scheduledAt->lte(now()));
                                $isFutureCita = $isOpenCita && $scheduledAt && $scheduledAt->gt(now());
                                $fotoMascota = optional($cita->mascota)->foto ? asset('storage/' . $cita->mascota->foto) : asset('storage/default.png');
                                $citaPayload = [
                                    'id' => $cita->id,
                                    'mascota_id' => $cita->mascota_id,
                                    'veterinario_id' => $cita->veterinario_id,
                                    'fecha' => optional($cita->fecha)->format('Y-m-d'),
                                    'hora' => $horaTexto,
                                    'estado' => $cita->estado,
                                ];
                                $attendPayload = [
                                    'id' => $cita->id,
                                    'mascota' => optional($cita->mascota)->nombre ?: 'Mascota no disponible',
                                    'owner' => optional(optional($cita->mascota)->cliente)->nombre ?: 'Sin dueño',
                                    'photo' => $fotoMascota,
                                    'fecha' => optional($cita->fecha)->format('Y-m-d'),
                                    'fecha_legible' => optional($cita->fecha)->format('d/m/Y'),
                                    'hora' => $horaTexto,
                                    'veterinario' => optional($cita->veterinario)->nombre ?: auth()->user()->name,
                                    'tipo_atencion' => $historia?->tipo_atencion ?: 'consulta',
                                    'historia_fecha' => optional($historia?->fecha)->format('Y-m-d') ?: optional($cita->fecha)->format('Y-m-d'),
                                    'diagnostico' => $historia?->diagnostico ?? '',
                                    'observaciones' => $historia?->observaciones ?? '',
                                    'peso' => $historia?->peso,
                                    'temperatura' => $historia?->temperatura,
                                    'servicio_producto_id' => $historia?->servicio_producto_id ?? ($ultimoServicio['id'] ?? null),
                                    'precio_servicio' => $historia?->precio_servicio ?? ($ultimoServicio['precio'] ?? null),
                                    'servicio_nombre' => $historia?->servicioProducto?->nombre ?? ($ultimoServicio['nombre'] ?? null),
                                    'vacuna_nombre_select' => $vacunaNombre ? ($vacunaEsCatalogo ? $vacunaNombre : '__custom__') : '',
                                    'vacuna_nombre_custom' => $vacunaNombre && !$vacunaEsCatalogo ? $vacunaNombre : '',
                                    'vacuna_fecha_aplicacion' => optional($vacunaAsociada?->fecha_aplicacion)->format('Y-m-d') ?: optional($cita->fecha)->format('Y-m-d'),
                                    'vacuna_proxima_dosis' => optional($vacunaAsociada?->proxima_dosis)->format('Y-m-d'),
                                    'tratamiento_descripcion' => $tratamiento?->descripcion ?? '',
                                    'tratamiento_costo' => $tratamiento?->costo ?? 0,
                                    'tratamiento_fecha_inicio' => optional($tratamiento?->fecha_inicio)->format('Y-m-d') ?: optional($cita->fecha)->format('Y-m-d'),
                                    'tratamiento_fecha_fin' => optional($tratamiento?->fecha_fin)->format('Y-m-d'),
                                    'receta_medicamentos' => $receta?->medicamentos ?? '',
                                    'receta_indicaciones' => $receta?->indicaciones ?? '',
                                    'seguimiento_motivo' => $seguimiento?->motivo ?? '',
                                    'seguimiento_notas' => $seguimiento?->notas ?? '',
                                    'seguimiento_dias_retorno' => $seguimiento?->dias_retorno,
                                    'seguimiento_fecha_proximo_control' => optional($seguimiento?->fecha_proximo_control)->format('Y-m-d'),
                                    'seguimiento_hora_proximo_control' => $seguimiento?->hora_proximo_control ? \Illuminate\Support\Str::of($seguimiento->hora_proximo_control)->substr(0, 5)->toString() : '09:00',
                                ];
                                $attendPresets[$cita->id] = $attendPayload;
                            @endphp
                            <article class="group rounded-[28px] border bg-white p-4 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.22)] transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg {{ $cardShellClass }}">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                        <span class="h-2 w-2 rounded-full {{ $cita->estado === 'completada' ? 'bg-emerald-500' : ($cita->estado === 'cancelada' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                        {{ $isControlCita ? 'Control de seguimiento' : (optional($cita->fecha)->isToday() ? 'Agenda de hoy' : 'Turno programado') }}
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $estadoClass }}">
                                        <span class="h-2 w-2 rounded-full {{ $cita->estado === 'completada' ? 'bg-emerald-500' : ($cita->estado === 'cancelada' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                        {{ ucfirst($cita->estado) }}
                                    </span>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-[112px_minmax(0,1fr)_190px] lg:items-start">
                                    <div class="relative w-28 shrink-0">
                                        <div class="absolute inset-0 rounded-[22px] bg-gradient-to-b from-blue-500/20 to-cyan-400/10"></div>
                                        <img src="{{ $fotoMascota }}" alt="Foto de {{ optional($cita->mascota)->nombre }}" class="relative h-28 w-full rounded-[22px] object-cover shadow-sm" onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';">
                                        <div class="absolute -bottom-3 left-3 rounded-2xl border border-white bg-white px-3 py-1.5 shadow-md">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Hora</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $horaTexto }}</p>
                                        </div>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Paciente</p>
                                                <h2 class="mt-1 truncate text-xl font-bold leading-tight text-slate-900">{{ optional($cita->mascota)->nombre ?: 'Mascota no disponible' }}</h2>
                                                <p class="mt-1 truncate text-sm font-medium text-sky-700">{{ optional($cita->mascota)->tipo_animal ?: 'Especie sin dato' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Propietario</p>
                                                <p class="mt-1 truncate text-sm font-semibold text-slate-700">{{ optional(optional($cita->mascota)->cliente)->nombre ?: 'Sin propietario registrado' }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Veterinario</p>
                                                <p class="mt-1 truncate text-sm font-semibold text-slate-700">{{ optional($cita->veterinario)->nombre ?: auth()->user()->name }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                                                <svg class="h-3.5 w-3.5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                                                </svg>
                                                {{ optional($cita->fecha)->format('d/m/Y') }}
                                            </div>
                                            @if($isOpenCita)
                                                <div class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.5 2" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                    {{ $isFutureCita ? 'Programada' : ($isControlCita ? 'Control listo para atender' : 'Atencion pendiente') }}
                                                </div>
                                            @elseif($isCompleted)
                                                <div class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                                    </svg>
                                                    Atención registrada
                                                </div>
                                            @else
                                                <div class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                    </svg>
                                                    Sin atención
                                                </div>
                                            @endif
                                        </div>

                                        @if($isControlCita)
                                            <div class="mt-3 rounded-2xl border border-indigo-100 bg-indigo-50 px-3 py-3 text-sm leading-6 text-indigo-900">
                                                <p class="font-semibold text-indigo-700">Retorno automatizado</p>
                                                <p class="mt-1">{{ \Illuminate\Support\Str::limit($controlPendiente?->motivo ?: $controlPendiente?->titulo ?: 'Control generado desde control de retorno.', 120) }}</p>
                                            </div>
                                        @endif

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @if($historia)
                                                <a href="{{ route('historias-clinicas.index', ['mascota_id' => $cita->mascota_id]) }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75a2.25 2.25 0 0 1 2.25 2.25v12A2.25 2.25 0 0 1 14.25 20.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6M9 12h6M9 15.75h3.75" />
                                                    </svg>
                                                    Historial clínico
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Acciones</p>
                                        <div class="mt-3 grid grid-cols-1 gap-2">
                                            @if($isOpenCita)
                                                <button type="button" onclick='openEditCitaModal(@json($citaPayload))' class="cita-action-button cita-action-button--secondary">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.651-1.652a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM15 6.349 17.651 9" />
                                                    </svg>
                                                    Editar
                                                </button>

                                                @if($canAttendCita)
                                                    <button type="button" onclick='openAttendCitaModal(@json($attendPayload))' class="cita-action-button cita-action-button--primary">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        {{ $historia ? 'Editar atención' : ($isControlCita ? 'Atender control' : 'Atender') }}
                                                    </button>
                                                @else
                                                    <div class="rounded-2xl border border-slate-200 bg-slate-100 px-3 py-3 text-center text-sm font-semibold text-slate-500">
                                                        Atender desde {{ optional($cita->fecha)->format('d/m/Y') }} {{ $horaTexto }}
                                                    </div>
                                                @endif
                                                <form method="POST" action="{{ route('citas.estado', $cita) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="cancelada">
                                                    <button type="submit" class="cita-action-button cita-action-button--warning">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                        Cancelar
                                                    </button>
                                                </form>
                                            @elseif($isCompleted)
                                                <div class="rounded-2xl border border-emerald-200 bg-white px-3 py-4 text-center">
                                                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <p class="mt-3 text-sm font-semibold text-emerald-700">Atencion cerrada</p>
                                                    <p class="mt-1 text-xs leading-5 text-slate-500">Esta cita ya fue atendida y no requiere mas acciones desde agenda.</p>
                                                </div>
                                            @else
                                                <div class="rounded-2xl border border-rose-200 bg-white px-3 py-4 text-center">
                                                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                        </svg>
                                                    </div>
                                                    <p class="mt-3 text-sm font-semibold text-rose-700">Cita cancelada</p>
                                                    <p class="mt-1 text-xs leading-5 text-slate-500">La cita quedó cerrada y no muestra acciones operativas en este módulo.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="module-block px-6 py-14 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold text-slate-900">Todavía no hay citas registradas</h3>
                        <p class="mt-2 text-base text-slate-500">Puedes crear la primera cita desde aquí o seguir el flujo desde el módulo de mascotas.</p>
                        <button type="button" onclick="openCitaModal('{{ $prefillMascotaId ?? '' }}')" class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Crear primera cita
                        </button>
                    </div>
                @endif

                <div class="module-block px-5 py-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="text-base text-slate-500">
                            Sigue desplazandote para revisar mas citas y usa los filtros para reducir pasos.
                        </div>
                        <div class="citas-pagination flex justify-center md:justify-end">
                            {{ $citas->links('pagination::tailwind') }}
                        </div>
                    </div>
                </div>
        </section>
    </div>
</div>

@include('citas.modals.create', [
    'citaMascotas' => $citaMascotas,
    'citaMascotasRecientes' => $citaMascotasRecientes,
    'veterinarios' => $veterinarios,
    'prefillMascotaId' => $prefillMascotaId,
    'shouldOpenCreate' => $shouldOpenCreate,
    'selectedMascota' => $selectedMascota,
    'accion' => $accion,
])
@include('citas.modals.create-cliente')
@include('citas.modals.create-mascota', ['clientes' => $clientes])
@include('citas.modals.attend', [
    'vacunaCatalogo' => $vacunaCatalogo,
    'serviciosCatalogo' => $serviciosCatalogo,
])
@if(session()->has('cita_ui'))
    <script>
        window.citaUiState = @json(session('cita_ui', []));
    </script>
@endif
<script>
    window.attendCitaPresets = @json($attendPresets);
</script>
<script src="{{ asset('js/modules/citas.js') }}"></script>
</x-app-layout>






