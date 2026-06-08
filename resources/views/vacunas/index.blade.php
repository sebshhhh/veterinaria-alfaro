<x-app-layout>
@php
    $stats = $stats ?? [];
    $prefillMascotaId = $prefillMascotaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $selectedMascota = $selectedMascota ?? null;
    $estadoDosisActual = request('estado_dosis');
    $baseFilters = array_filter([
        'search' => request('search'),
        'mascota_id' => request('mascota_id'),
    ], fn ($value) => filled($value));

    $kpiCards = [
        ['label' => 'Total', 'value' => $stats['total'] ?? 0, 'helper' => 'Controles registrados', 'tone' => 'blue', 'icon' => 'shield'],
        ['label' => 'Aplicadas', 'value' => $stats['aplicadas'] ?? 0, 'helper' => 'Dosis aplicadas', 'tone' => 'emerald', 'icon' => 'check'],
        ['label' => 'Programadas', 'value' => $stats['programadas'] ?? 0, 'helper' => 'Pendientes por aplicar', 'tone' => 'amber', 'icon' => 'clock'],
        ['label' => 'Proximas', 'value' => $stats['proximas'] ?? 0, 'helper' => 'Con seguimiento activo', 'tone' => 'violet', 'icon' => 'calendar'],
        ['label' => 'Vencidas', 'value' => $stats['vencidas'] ?? 0, 'helper' => 'Requieren revisar', 'tone' => 'rose', 'icon' => 'alert'],
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m6 2.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-700">Control preventivo</p>
                            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Vacunas</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                Aquí se gestiona el seguimiento preventivo. Puedes aplicar una vacuna hoy o dejarla programada para después sin duplicar el registro clínico.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3.5 py-2 text-sm font-semibold text-blue-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                            Flujo preventivo
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-3.5 py-2 text-sm font-semibold text-amber-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            {{ $stats['semana'] ?? 0 }} pendientes esta semana
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-3.5 py-2 text-sm font-semibold text-rose-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                            {{ $stats['vencidas'] ?? 0 }} vencidas
                        </span>
                        @if($selectedMascota)
                            <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3.5 py-2 text-sm font-semibold text-cyan-700 shadow-sm">
                                <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                                Filtrado: {{ $selectedMascota->nombre }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col items-stretch gap-3 xl:min-w-[340px] xl:max-w-[360px]">
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Resumen preventivo</p>
                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-3xl font-bold text-slate-950">{{ $stats['total'] ?? 0 }}</p>
                                <p class="mt-1 text-sm text-slate-500">vacunas registradas</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-sm">Preventivo</span>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button type="button"
                                onclick="openVacunaAppliedModal('{{ $prefillMascotaId ?? '' }}')"
                                class="inline-flex items-center justify-center gap-2 rounded-[22px] bg-gradient-to-r from-blue-600 to-cyan-500 px-4 py-4 text-sm font-semibold text-white shadow-[0_18px_34px_-18px_rgba(37,99,235,0.75)] transition hover:brightness-105">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 8.25 18.75l-3.75-3.75" />
                            </svg>
                            Aplicar hoy
                        </button>
                        <button type="button"
                                onclick="openProgramVacunaModal('{{ $prefillMascotaId ?? '' }}')"
                                class="inline-flex items-center justify-center gap-2 rounded-[22px] border border-amber-200 bg-white px-4 py-4 text-sm font-semibold text-amber-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12h7.5M12 8.25v7.5" />
                            </svg>
                            Programar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach($kpiCards as $card)
                @php
                    $toneClasses = match($card['tone']) {
                        'emerald' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white',
                        'amber' => 'border-amber-200 bg-gradient-to-br from-amber-50 to-white',
                        'violet' => 'border-violet-200 bg-gradient-to-br from-violet-50 to-white',
                        'rose' => 'border-rose-200 bg-gradient-to-br from-rose-50 to-white',
                        default => 'border-blue-200 bg-gradient-to-br from-blue-50 to-white',
                    };
                    $valueClasses = match($card['tone']) {
                        'emerald' => 'text-emerald-700',
                        'amber' => 'text-amber-700',
                        'violet' => 'text-violet-700',
                        'rose' => 'text-rose-700',
                        default => 'text-blue-700',
                    };
                    $iconWrapClasses = match($card['tone']) {
                        'emerald' => 'bg-emerald-600 text-white shadow-emerald-200',
                        'amber' => 'bg-amber-500 text-white shadow-amber-200',
                        'violet' => 'bg-violet-500 text-white shadow-violet-200',
                        'rose' => 'bg-rose-500 text-white shadow-rose-200',
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
                                @case('check')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    @break
                                @case('clock')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @break
                                @case('calendar')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3M16 2v3M4 7h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                    @break
                                @case('alert')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 0 0 3.66 18h16.68a1 1 0 0 0 .87-1.5l-7.5-13a1 1 0 0 0-1.74 0Z" /></svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" /></svg>
                            @endswitch
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="module-block px-5 py-5">
            <form method="GET" action="{{ route('vacunas.index') }}">
                <div class="grid gap-3 xl:grid-cols-[minmax(0,2fr)_240px_auto] xl:items-end">
                    <div>
                        <label for="search" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Buscar vacuna</label>
                        <div class="flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition focus-within:border-blue-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-100">
                            <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por vacuna, mascota, dueño o DNI..." class="ml-3 w-full border-0 bg-transparent p-0 text-base text-slate-700 placeholder:text-slate-400 focus:ring-0">
                        </div>
                    </div>
                    <div>
                        <label for="estado_dosis" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Seguimiento</label>
                        <select id="estado_dosis" name="estado_dosis" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">Todas las dosis</option>
                            <option value="aplicadas" @selected(request('estado_dosis') === 'aplicadas')>Ya aplicadas</option>
                            <option value="programadas" @selected(request('estado_dosis') === 'programadas')>Solo programadas</option>
                            <option value="proximas" @selected(request('estado_dosis') === 'proximas')>Proximas o pendientes</option>
                            <option value="vencidas" @selected(request('estado_dosis') === 'vencidas')>Vencidas</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-base font-semibold text-white shadow-[0_18px_34px_-22px_rgba(37,99,235,0.85)] transition hover:bg-blue-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            Buscar
                        </button>
                        <a href="{{ route('vacunas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50">
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
                    <a href="{{ route('vacunas.index', $baseFilters) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoDosisActual ? 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoDosisActual ? 'bg-slate-300' : 'bg-blue-500' }}"></span>
                        Todas ({{ $stats['total'] ?? 0 }})
                    </a>
                    <a href="{{ route('vacunas.index', array_merge($baseFilters, ['estado_dosis' => 'aplicadas'])) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoDosisActual === 'aplicadas' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoDosisActual === 'aplicadas' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                        Aplicadas ({{ $stats['aplicadas'] ?? 0 }})
                    </a>
                    <a href="{{ route('vacunas.index', array_merge($baseFilters, ['estado_dosis' => 'programadas'])) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoDosisActual === 'programadas' ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoDosisActual === 'programadas' ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
                        Programadas ({{ $stats['programadas'] ?? 0 }})
                    </a>
                    <a href="{{ route('vacunas.index', array_merge($baseFilters, ['estado_dosis' => 'proximas'])) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoDosisActual === 'proximas' ? 'border-violet-200 bg-violet-50 text-violet-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoDosisActual === 'proximas' ? 'bg-violet-500' : 'bg-slate-300' }}"></span>
                        Proximas ({{ $stats['proximas'] ?? 0 }})
                    </a>
                    <a href="{{ route('vacunas.index', array_merge($baseFilters, ['estado_dosis' => 'vencidas'])) }}" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $estadoDosisActual === 'vencidas' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $estadoDosisActual === 'vencidas' ? 'bg-rose-500' : 'bg-slate-300' }}"></span>
                        Vencidas ({{ $stats['vencidas'] ?? 0 }})
                    </a>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $vacunas->total() }} resultados</span>
            </div>
        </section>

        <section class="module-block overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-600">Prioridad preventiva</p>
                        <h3 class="mt-1 text-2xl font-bold text-slate-900">Vacunas que requieren seguimiento cercano</h3>
                        <p class="mt-1 text-sm text-slate-500">El sistema destaca primero lo que esta por aplicar, por vencer o ya quedó programado para los siguientes dias.</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">Siguientes 7 días</span>
                </div>
            </div>
            <div class="grid gap-4 px-5 py-5 sm:px-6 md:grid-cols-2 xl:grid-cols-4">
                @forelse($vacunasUrgentes as $vacunaUrgente)
                    <article class="rounded-[24px] border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-4 py-4 shadow-[0_18px_36px_-30px_rgba(180,83,9,0.22)]">
                        <p class="truncate text-base font-bold text-slate-900">{{ optional($vacunaUrgente->mascota)->nombre ?: 'Mascota' }}</p>
                        <p class="mt-1 truncate text-sm text-slate-500">{{ optional(optional($vacunaUrgente->mascota)->cliente)->nombre ?: 'Sin dueño registrado' }}</p>
                        <p class="mt-4 text-sm font-semibold text-amber-700">{{ $vacunaUrgente->nombre }}</p>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ $vacunaUrgente->estado_aplicacion === 'programada' ? 'Programada para' : 'Próximo refuerzo' }}
                            {{ optional($vacunaUrgente->fecha_programada)->format('d/m/Y') ?: optional($vacunaUrgente->proxima_dosis)->format('d/m/Y') }}
                        </p>
                    </article>
                @empty
                    <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500 md:col-span-2 xl:col-span-4">
                        No hay dosis proximas dentro de los siguientes 7 días.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="module-block overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Control preventivo</p>
                        <h3 class="mt-1 text-2xl font-bold text-slate-900">Listado de vacunas</h3>
                        <p class="mt-1 text-sm text-slate-500">Cada registro muestra si la dosis ya fue aplicada o si aun esta pendiente, junto con el control de retorno correspondiente.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Aplicadas y programadas</span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $vacunas->total() }} resultados</span>
                    </div>
                </div>
            </div>

            <div class="px-5 py-5 sm:px-6">
                @if($vacunas->count())
                    <div class="grid gap-4 xl:grid-cols-2">
                        @foreach($vacunas as $vacuna)
                            @php
                                $mascota = $vacuna->mascota;
                                $cliente = optional($mascota)->cliente;
                                $fotoMascota = optional($mascota)->foto ? \App\Support\PhotoUrl::make($mascota->foto) : \App\Support\PhotoUrl::make(null);
                                $isScheduled = $vacuna->estado_aplicacion === 'programada';
                                $isOverdue = $isScheduled
                                    ? (optional($vacuna->fecha_programada)->format('Y-m-d') && $vacuna->fecha_programada->isPast())
                                    : (optional($vacuna->proxima_dosis)->format('Y-m-d') && $vacuna->proxima_dosis->isPast());
                                $statusBadge = $isScheduled
                                    ? ($isOverdue
                                        ? ['label' => 'Programada vencida', 'class' => 'border-rose-200 bg-rose-50 text-rose-700']
                                        : ['label' => 'Pendiente por aplicar', 'class' => 'border-amber-200 bg-amber-50 text-amber-700'])
                                    : (filled(optional($vacuna->proxima_dosis)->format('Y-m-d'))
                                        ? ($isOverdue
                                            ? ['label' => 'Refuerzo vencido', 'class' => 'border-rose-200 bg-rose-50 text-rose-700']
                                            : ['label' => 'Seguimiento activo', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700'])
                                        : ['label' => 'Aplicada sin refuerzo', 'class' => 'border-slate-200 bg-slate-100 text-slate-700']);
                                $vacunaPayload = [
                                    'id' => $vacuna->id,
                                    'mascota_id' => $vacuna->mascota_id,
                                    'nombre' => $vacuna->nombre,
                                    'estado_aplicacion' => $vacuna->estado_aplicacion,
                                    'nombre_select' => $vacunaCatalogo->contains($vacuna->nombre) ? $vacuna->nombre : '__custom__',
                                    'nombre_custom' => $vacunaCatalogo->contains($vacuna->nombre) ? '' : $vacuna->nombre,
                                    'fecha_programada' => optional($vacuna->fecha_programada)->format('Y-m-d'),
                                    'fecha_aplicacion' => optional($vacuna->fecha_aplicacion)->format('Y-m-d'),
                                    'proxima_dosis' => optional($vacuna->proxima_dosis)->format('Y-m-d'),
                                ];
                            @endphp
                            <article class="group rounded-[28px] border border-slate-200 bg-white p-4 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.22)] transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                        <span class="h-2 w-2 rounded-full {{ $isScheduled ? 'bg-amber-500' : 'bg-blue-500' }}"></span>
                                        {{ $isScheduled ? 'Programacion preventiva' : 'Vacuna aplicada' }}
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusBadge['class'] }}">
                                        <span class="h-2 w-2 rounded-full {{ str_contains($statusBadge['class'], 'rose') ? 'bg-rose-500' : (str_contains($statusBadge['class'], 'amber') ? 'bg-amber-500' : (str_contains($statusBadge['class'], 'emerald') ? 'bg-emerald-500' : 'bg-slate-400')) }}"></span>
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-[112px_minmax(0,1fr)_220px] lg:items-start">
                                    <div class="relative w-28 shrink-0">
                                        <div class="absolute inset-0 rounded-[22px] bg-gradient-to-b from-blue-500/20 to-cyan-400/10"></div>
                                        <img src="{{ $fotoMascota }}" alt="Foto de {{ optional($mascota)->nombre }}" class="relative h-28 w-full rounded-[22px] object-cover shadow-sm" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">
                                        <div class="absolute -bottom-3 left-3 rounded-2xl border border-white bg-white px-3 py-1.5 shadow-md">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Fecha</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900">{{ optional($vacuna->fecha_aplicacion)->format('d/m/Y') ?: optional($vacuna->fecha_programada)->format('d/m/Y') }}</p>
                                        </div>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Vacuna</p>
                                                <h2 class="mt-1 truncate text-xl font-bold leading-tight text-slate-900">{{ $vacuna->nombre }}</h2>
                                                <p class="mt-1 truncate text-sm font-medium text-blue-700">{{ optional($mascota)->nombre ?: 'Mascota no disponible' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Dueño</p>
                                                <p class="mt-1 truncate text-sm font-semibold text-slate-700">{{ $cliente->nombre ?? 'Sin dueño' }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Seguimiento</p>
                                                <p class="mt-1 truncate text-sm font-semibold text-slate-700">
                                                    @if($isScheduled)
                                                        {{ optional($vacuna->fecha_programada)->format('d/m/Y') ?: 'Sin fecha' }}
                                                    @else
                                                        {{ optional($vacuna->proxima_dosis)->format('d/m/Y') ?: 'Sin refuerzo pendiente' }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if($cliente && filled($cliente->dni))
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                    DNI {{ $cliente->dni }}
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                                {{ $isScheduled ? 'Pendiente preventiva' : 'Control preventivo' }}
                                            </span>
                                            @if($vacuna->historiaClinica)
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    En historial
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Acciones</p>
                                        <div class="mt-3 grid grid-cols-1 gap-2">
                                            @if($isScheduled)
                                                <button type="button" onclick='openApplyVacunaModal(@json($vacunaPayload))' class="vacuna-action-button vacuna-action-button--primary">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Aplicar ahora
                                                </button>
                                                <button type="button" onclick='openEditVacunaModal(@json($vacunaPayload))' class="vacuna-action-button vacuna-action-button--secondary">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.651-1.652a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM15 6.349 17.651 9" />
                                                    </svg>
                                                    Reprogramar
                                                </button>
                                            @else
                                                <button type="button" onclick='openEditVacunaModal(@json($vacunaPayload))' class="vacuna-action-button vacuna-action-button--secondary">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.651-1.652a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM15 6.349 17.651 9" />
                                                    </svg>
                                                    Actualizar control
                                                </button>
                                            @endif

                                            @if($vacuna->historiaClinica)
                                                <a href="{{ route('historias-clinicas.index', ['mascota_id' => $vacuna->mascota_id]) }}" class="vacuna-action-button vacuna-action-button--success">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75a2.25 2.25 0 0 1 2.25 2.25v12A2.25 2.25 0 0 1 14.25 20.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6M9 12h6M9 15.75h3.75" />
                                                    </svg>
                                                    Historial clínico
                                                </a>
                                            @else
                                                <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center text-xs font-semibold text-slate-400">
                                                    Sin registro clínico aun
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ route('vacunas.destroy', $vacuna) }}" onsubmit="return confirm('&iquest;Eliminar esta vacuna?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="vacuna-action-button vacuna-action-button--danger">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-9.75 0V6A1.5 1.5 0 0 1 9.75 4.5h4.5A1.5 1.5 0 0 1 15.75 6v1.5m-8.25 0v10.125A1.875 1.875 0 0 0 9.375 19.5h5.25A1.875 1.875 0 0 0 16.5 17.625V7.5" />
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m6 2.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold text-slate-900">Todavía no hay vacunas registradas</h3>
                        <p class="mt-2 text-base text-slate-500">Puedes aplicar la primera vacuna desde aquí o dejar programado el siguiente control preventivo.</p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-center">
                            <button type="button" onclick="openVacunaAppliedModal('{{ $prefillMascotaId ?? '' }}')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-[0_18px_34px_-22px_rgba(37,99,235,0.85)] transition hover:bg-blue-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 8.25 18.75l-3.75-3.75" />
                                </svg>
                                Aplicar primera vacuna
                            </button>
                            <button type="button" onclick="openProgramVacunaModal('{{ $prefillMascotaId ?? '' }}')" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-white px-6 py-3 text-base font-semibold text-amber-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12h7.5M12 8.25v7.5" />
                                </svg>
                                Programar primera vacuna
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 bg-white px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-slate-500">Mostrando {{ $vacunas->firstItem() ?? 0 }} a {{ $vacunas->lastItem() ?? 0 }} de {{ $vacunas->total() }} vacunas</div>
                    <div class="vacunas-pagination flex justify-center md:justify-end">{{ $vacunas->links('pagination::tailwind') }}</div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('vacunas.modals.form', [
    'vacunaMascotas' => $vacunaMascotas,
    'vacunaCatalogo' => $vacunaCatalogo,
    'prefillMascotaId' => $prefillMascotaId,
    'shouldOpenCreate' => $shouldOpenCreate,
])
<script src="{{ asset('js/modules/vacunas.js') }}"></script>
</x-app-layout>




