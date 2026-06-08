<x-app-layout>
@php
    $stats = $stats ?? [];
    $selectedTipo = request('tipo');
    $uiState = session('atencion_rapida_ui', []);
    $rutaAtencion = $rutaAtencion ?? [];
    $citasPendientesHoy = $citasPendientesHoy ?? collect();
    $vacunasPrioritarias = $vacunasPrioritarias ?? collect();
    $controlesPendientes = $controlesPendientes ?? collect();
    $prefillMascotaId = $prefillMascotaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;

    if ($shouldOpenCreate) {
        $uiState = array_merge([
            'open_main' => true,
            'selected_mascota_id' => $prefillMascotaId,
        ], $uiState);
    }

    $kpiCards = [
        ['label' => 'Atenciones directas', 'value' => $stats['total'] ?? 0, 'helper' => 'Sin cita programada', 'tone' => 'emerald', 'icon' => 'spark'],
        ['label' => 'Atenciones hoy', 'value' => $stats['hoy'] ?? 0, 'helper' => 'Registradas hoy', 'tone' => 'blue', 'icon' => 'today'],
        ['label' => 'Consultas', 'value' => $stats['consulta'] ?? 0, 'helper' => 'Consultas directas', 'tone' => 'slate', 'icon' => 'stethoscope'],
        ['label' => 'Vacunaciones', 'value' => $stats['vacunacion'] ?? 0, 'helper' => 'Atenciones preventivas', 'tone' => 'amber', 'icon' => 'shield'],
        ['label' => 'Controles', 'value' => $stats['control'] ?? 0, 'helper' => 'Atenciones de control', 'tone' => 'violet', 'icon' => 'pulse'],
    ];

    $routeCards = [
        [
            'tone' => 'blue',
            'eyebrow' => 'Con cita',
            'title' => 'Agenda programada',
            'description' => 'Cuando el paciente ya estaba agendado, el flujo correcto sigue desde Citas.',
            'value' => $rutaAtencion['citas_pendientes_hoy'] ?? 0,
            'caption' => 'Pendientes hoy',
            'action' => 'Abrir agenda',
            'href' => route('citas.index', ['fecha' => now()->format('Y-m-d')]),
        ],
        [
            'tone' => 'emerald',
            'eyebrow' => 'Sin cita',
            'title' => 'Atención directa',
            'description' => 'Usa este flujo cuando el paciente llega sin cita y necesita atención inmediata.',
            'value' => $rutaAtencion['atenciones_directas_hoy'] ?? 0,
            'caption' => 'Directas hoy',
            'action' => 'Registrar ahora',
            'button' => true,
        ],
        [
            'tone' => 'amber',
            'eyebrow' => 'Preventiva',
            'title' => 'Vacunación y refuerzo',
            'description' => 'Si la visita es solo preventiva, deriva el flujo al módulo Vacunas.',
            'value' => $rutaAtencion['vacunas_hoy'] ?? 0,
            'caption' => 'Pendientes hoy',
            'action' => 'Abrir vacunas',
            'href' => route('vacunas.index', ['open_create' => 1, 'mascota_id' => $prefillMascotaId]),
        ],
    ];
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-5">
        <section class="rounded-[30px] border border-slate-200 bg-white px-5 py-5 shadow-[0_24px_60px_-42px_rgba(15,23,42,0.24)] sm:px-6 xl:px-7">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-emerald-600 to-teal-500 text-white shadow-[0_18px_32px_-18px_rgba(5,150,105,0.85)]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14M5.25 5.25h.008v.008H5.25V5.25Zm13.5 0h.008v.008h-.008V5.25ZM5.25 18.75h.008v.008H5.25v-.008Zm13.5 0h.008v.008h-.008v-.008Z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Atención clínica</p>
                            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Nueva atención</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                Este módulo resuelve al paciente que llega sin cita. Desde aquí registras la consulta, y luego todo queda disponible para ficha, historial, vacunas, tratamientos y recetas.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3.5 py-2 text-sm font-semibold text-emerald-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            Flujo sin cita
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3.5 py-2 text-sm font-semibold text-blue-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                            {{ $stats['hoy'] ?? 0 }} atenciones hoy
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-3.5 py-2 text-sm font-semibold text-amber-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            {{ ($rutaAtencion['vacunas_semana'] ?? 0) + ($rutaAtencion['controles_semana'] ?? 0) }} controles cercanos
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-stretch gap-3 xl:min-w-[340px] xl:max-w-[360px]">
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Resumen del módulo</p>
                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-3xl font-bold text-slate-950">{{ $stats['total'] ?? 0 }}</p>
                                <p class="mt-1 text-sm text-slate-500">atenciones registradas</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm">Sin cita</span>
                        </div>
                    </div>
                    <button type="button"
                            onclick="openAtencionRapidaModal('{{ $prefillMascotaId ?? '' }}')"
                            class="inline-flex items-center justify-center gap-2 rounded-[22px] bg-gradient-to-r from-emerald-600 to-teal-500 px-4 py-4 text-sm font-semibold text-white shadow-[0_18px_34px_-18px_rgba(5,150,105,0.75)] transition hover:brightness-105">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Nueva atención
                    </button>
                </div>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach($kpiCards as $card)
                @php
                    $toneClasses = match($card['tone']) {
                        'amber' => 'border-amber-200 bg-gradient-to-br from-amber-50 to-white',
                        'violet' => 'border-violet-200 bg-gradient-to-br from-violet-50 to-white',
                        'slate' => 'border-slate-200 bg-gradient-to-br from-slate-100 to-white',
                        'blue' => 'border-blue-200 bg-gradient-to-br from-blue-50 to-white',
                        default => 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white',
                    };
                    $valueClasses = match($card['tone']) {
                        'amber' => 'text-amber-700',
                        'violet' => 'text-violet-700',
                        'slate' => 'text-slate-700',
                        'blue' => 'text-blue-700',
                        default => 'text-emerald-700',
                    };
                    $iconWrapClasses = match($card['tone']) {
                        'amber' => 'bg-amber-500 text-white shadow-amber-200',
                        'violet' => 'bg-violet-500 text-white shadow-violet-200',
                        'slate' => 'bg-slate-700 text-white shadow-slate-200',
                        'blue' => 'bg-blue-600 text-white shadow-blue-200',
                        default => 'bg-emerald-600 text-white shadow-emerald-200',
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
                                @case('today')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3M16 2v3M4 7h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                    @break
                                @case('stethoscope')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3v5a4 4 0 1 0 8 0V3M10 14v2a4 4 0 1 0 8 0v-1a2 2 0 1 0-4 0v1a1 1 0 1 1-2 0v-2" /></svg>
                                    @break
                                @case('shield')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" /></svg>
                                    @break
                                @case('pulse')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2.5-5 4 10 2.5-5H21" /></svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" /></svg>
                            @endswitch
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            @foreach($routeCards as $card)
                @php
                    $routeTone = match($card['tone']) {
                        'amber' => 'border-amber-200 bg-gradient-to-br from-amber-50 via-white to-amber-50/60',
                        'blue' => 'border-blue-200 bg-gradient-to-br from-blue-50 via-white to-blue-50/60',
                        default => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50/60',
                    };
                    $routeText = match($card['tone']) {
                        'amber' => 'text-amber-700',
                        'blue' => 'text-blue-700',
                        default => 'text-emerald-700',
                    };
                @endphp
                @if(!empty($card['button']))
                    <button type="button"
                            onclick="openAtencionRapidaModal('{{ $prefillMascotaId ?? '' }}')"
                            class="group rounded-[26px] border p-5 text-left shadow-[0_18px_40px_-32px_rgba(15,23,42,0.22)] transition duration-200 hover:-translate-y-0.5 hover:shadow-lg {{ $routeTone }}">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] {{ $routeText }}">{{ $card['eyebrow'] }}</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $card['title'] }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $card['description'] }}</p>
                        <div class="mt-5 flex items-end justify-between gap-3">
                            <div>
                                <p class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                                <p class="mt-1 text-sm font-medium {{ $routeText }}">{{ $card['caption'] }}</p>
                            </div>
                            <span class="text-sm font-semibold {{ $routeText }} transition group-hover:translate-x-0.5">{{ $card['action'] }}</span>
                        </div>
                    </button>
                @else
                    <a href="{{ $card['href'] }}"
                       class="group rounded-[26px] border p-5 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.22)] transition duration-200 hover:-translate-y-0.5 hover:shadow-lg {{ $routeTone }}">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] {{ $routeText }}">{{ $card['eyebrow'] }}</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $card['title'] }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $card['description'] }}</p>
                        <div class="mt-5 flex items-end justify-between gap-3">
                            <div>
                                <p class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                                <p class="mt-1 text-sm font-medium {{ $routeText }}">{{ $card['caption'] }}</p>
                            </div>
                            <span class="text-sm font-semibold {{ $routeText }} transition group-hover:translate-x-0.5">{{ $card['action'] }}</span>
                        </div>
                    </a>
                @endif
            @endforeach
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
            <article class="module-block px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Agenda prioritaria</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Citas que deberian resolverse primero</h3>
                    </div>
                    <a href="{{ route('citas.index', ['fecha' => now()->format('Y-m-d')]) }}" class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">Ir a Citas</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($citasPendientesHoy as $cita)
                        <div class="rounded-[22px] border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-slate-900">{{ optional($cita->mascota)->nombre ?: 'Mascota' }}</p>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ optional(optional($cita->mascota)->cliente)->nombre ?: 'Sin dueño registrado' }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-sm">{{ \Illuminate\Support\Str::of($cita->hora)->substr(0, 5)->toString() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                            No hay citas pendientes hoy. Puedes usar este módulo para atenciones directas sin cita.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="module-block px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-600">Seguimiento prioritario</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Vacunas y controles cercanos</h3>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                        {{ ($rutaAtencion['vacunas_semana'] ?? 0) + ($rutaAtencion['controles_semana'] ?? 0) }} pendientes
                    </span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($vacunasPrioritarias as $vacuna)
                        <div class="rounded-[22px] border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-slate-900">{{ optional($vacuna->mascota)->nombre ?: 'Mascota' }}</p>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ $vacuna->nombre }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm">
                                    {{ optional($vacuna->fecha_programada)->format('d/m/Y') ?: optional($vacuna->proxima_dosis)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        @forelse($controlesPendientes as $seguimiento)
                            <div class="rounded-[22px] border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-bold text-slate-900">{{ optional($seguimiento->mascota)->nombre ?: 'Mascota' }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($seguimiento->motivo ?: $seguimiento->titulo, 70) }}</p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm">
                                        {{ optional($seguimiento->fecha_proximo_control)->format('d/m/Y') }}
                                    </span>
                                </div>
                                @if($seguimiento->cita)
                                    <p class="mt-2 text-xs font-semibold text-blue-700">Cita sincronizada {{ \Illuminate\Support\Str::of($seguimiento->cita->hora)->substr(0, 5)->toString() }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                No hay vacunas ni controles próximos en este momento.
                            </div>
                        @endforelse
                    @endforelse
                </div>
            </article>
        </section>

        <section class="module-block px-5 py-5">
            <form method="GET" action="{{ route('atencion-rapida.index') }}">
                <div class="grid gap-3 xl:grid-cols-[minmax(0,2fr)_220px_220px_auto] xl:items-end">
                    <div>
                        <label for="search" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Buscar atención</label>
                        <div class="flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition focus-within:border-emerald-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-100">
                            <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por mascota, dueño, DNI o diagnóstico..." class="ml-3 w-full border-0 bg-transparent p-0 text-base text-slate-700 placeholder:text-slate-400 focus:ring-0">
                        </div>
                    </div>
                    <div>
                        <label for="tipo" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Tipo</label>
                        <select id="tipo" name="tipo" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                            <option value="">Todos</option>
                            <option value="consulta" @selected($selectedTipo === 'consulta')>Consulta</option>
                            <option value="vacunacion" @selected($selectedTipo === 'vacunacion')>Vacunación</option>
                            <option value="control" @selected($selectedTipo === 'control')>Control</option>
                            <option value="desparasitacion" @selected($selectedTipo === 'desparasitacion')>Desparasitación</option>
                            <option value="otro" @selected($selectedTipo === 'otro')>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label for="fecha" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Fecha</label>
                        <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-base font-semibold text-white shadow-[0_18px_34px_-22px_rgba(5,150,105,0.85)] transition hover:bg-emerald-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            Buscar
                        </button>
                        <a href="{{ route('atencion-rapida.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356m-1.636 1.635a9 9 0 1 0 2.339 9.34" />
                            </svg>
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </section>

        <section class="module-block overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-600">Registro clinico</p>
                        <h3 class="mt-1 text-2xl font-bold text-slate-900">Atenciones sin cita registradas</h3>
                        <p class="mt-1 text-sm text-slate-500">Consulta rapidamente lo que ya fue atendido y abre la ficha del paciente sin salir del módulo.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">Manual</span>
                        <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Sin cita</span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $atenciones->total() }} resultados</span>
                    </div>
                </div>
            </div>

            <div class="px-5 py-5 sm:px-6">
                @if($atenciones->count())
                    <div class="grid gap-4 xl:grid-cols-2">
                        @foreach($atenciones as $atencion)
                            @php
                                $mascota = $atencion->mascota;
                                $cliente = optional($mascota)->cliente;
                                $foto = optional($mascota)->foto ? \App\Support\PhotoUrl::make($mascota->foto) : \App\Support\PhotoUrl::make(null);
                                $vacunasAtencion = collect(optional($mascota)->vacunas)->filter(function ($vacuna) use ($atencion) {
                                    return $vacuna->fecha_aplicacion && $atencion->fecha && $vacuna->fecha_aplicacion->isSameDay($atencion->fecha);
                                })->take(2);
                                $proximoControl = $atencion->seguimientos
                                    ->filter(fn ($seguimiento) => $seguimiento->estado !== 'cerrado' && filled(optional($seguimiento->fecha_proximo_control)->format('Y-m-d')))
                                    ->sortBy('fecha_proximo_control')
                                    ->first();
                                $tipoLabel = match ($atencion->tipo_atencion) {
                                    'vacunacion' => 'Vacunación',
                                    'control' => 'Control',
                                    'desparasitacion' => 'Desparasitación',
                                    'otro' => 'Otro',
                                    default => 'Consulta',
                                };
                                $tipoClass = match ($atencion->tipo_atencion) {
                                    'vacunacion' => 'border-blue-200 bg-blue-50 text-blue-700',
                                    'control' => 'border-violet-200 bg-violet-50 text-violet-700',
                                    'desparasitacion' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    'otro' => 'border-slate-200 bg-slate-100 text-slate-700',
                                    default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                };
                            @endphp
                            <article class="group rounded-[28px] border border-slate-200 bg-white p-4 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.22)] transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        {{ optional($atencion->fecha)->isToday() ? 'Atención de hoy' : 'Atención registrada' }}
                                    </div>
                                    <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-semibold {{ $tipoClass }}">
                                        {{ $tipoLabel }}
                                    </span>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-[112px_minmax(0,1fr)_190px] lg:items-start">
                                    <div class="relative w-28 shrink-0">
                                        <div class="absolute inset-0 rounded-[22px] bg-gradient-to-b from-emerald-500/20 to-teal-400/10"></div>
                                        <img src="{{ $foto }}" alt="Foto de {{ optional($mascota)->nombre }}" class="relative h-28 w-full rounded-[22px] object-cover shadow-sm" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">
                                        <div class="absolute -bottom-3 left-3 rounded-2xl border border-white bg-white px-3 py-1.5 shadow-md">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Fecha</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900">{{ optional($atencion->fecha)->format('d/m/Y') }}</p>
                                        </div>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Paciente</p>
                                                <h2 class="mt-1 truncate text-xl font-bold leading-tight text-slate-900">{{ optional($mascota)->nombre ?: 'Mascota no disponible' }}</h2>
                                                <p class="mt-1 truncate text-sm font-medium text-emerald-700">{{ optional($cliente)->nombre ?: 'Sin dueño registrado' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 rounded-[22px] bg-slate-50 px-4 py-4">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Resumen clinico</p>
                                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                                {{ \Illuminate\Support\Str::limit($atencion->diagnostico ?: $atencion->observaciones ?: 'Atención clínica directa registrada.', 160) }}
                                            </p>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if($atencion->tratamientos->count())
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                    {{ $atencion->tratamientos->count() }} tratamiento(s)
                                                </span>
                                            @endif
                                            @if($atencion->recetas->count())
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-semibold text-violet-700">
                                                    <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                                                    {{ $atencion->recetas->count() }} receta(s)
                                                </span>
                                            @endif
                                            @if($vacunasAtencion->count())
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                                                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                                    {{ $vacunasAtencion->count() }} vacuna(s)
                                                </span>
                                            @endif
                                            @if($proximoControl)
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">
                                                    <span class="h-2 w-2 rounded-full bg-slate-500"></span>
                                                    Control {{ optional($proximoControl->fecha_proximo_control)->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Acciones</p>
                                        <div class="mt-3 grid grid-cols-1 gap-2">
                                            <a href="{{ route('historias-clinicas.index', ['mascota_id' => optional($mascota)->id]) }}" class="directa-action-button directa-action-button--secondary">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75a2.25 2.25 0 0 1 2.25 2.25v12A2.25 2.25 0 0 1 14.25 20.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6M9 12h6M9 15.75h3.75" />
                                                </svg>
                                                Historial clínico
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold text-slate-900">Todavía no hay atenciones sin cita registradas</h3>
                        <p class="mt-2 text-base text-slate-500">Cuando llegue un paciente directamente a la clínica, podrás resolver su atención desde aquí sin pasar por agenda.</p>
                        <button type="button" onclick="openAtencionRapidaModal()" class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-base font-semibold text-white shadow-[0_18px_34px_-22px_rgba(5,150,105,0.85)] transition hover:bg-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Registrar atención directa
                        </button>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 bg-white px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-slate-500">
                        Mostrando {{ $atenciones->firstItem() ?? 0 }} a {{ $atenciones->lastItem() ?? 0 }} de {{ $atenciones->total() }} atenciones
                    </div>
                    <div class="atencion-rapida-pagination flex justify-center md:justify-end">
                        {{ $atenciones->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('atencion-rapida.modals.create', [
    'mascotas' => $mascotas,
    'veterinarios' => $veterinarios,
    'vacunaCatalogo' => $vacunaCatalogo,
    'serviciosCatalogo' => $serviciosCatalogo,
    'mascotasRecientes' => $mascotasRecientes,
])
@include('atencion-rapida.modals.create-cliente')
@include('atencion-rapida.modals.create-mascota', ['clientes' => $clientes])

@if(!empty($uiState))
    <script>
        window.atencionRapidaUiState = @json($uiState);
    </script>
@endif


<script src="{{ asset('js/modules/atencion-rapida.js') }}?v={{ filemtime(public_path('js/modules/atencion-rapida.js')) }}"></script>
</x-app-layout>



