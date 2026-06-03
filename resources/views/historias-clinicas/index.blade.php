<x-app-layout>
@php
    $stats = $stats ?? [];
    $prefillMascotaId = $prefillMascotaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $selectedMascota = $selectedMascota ?? null;

    $baseFilters = array_filter([
        'search' => request('search'),
        'mascota_id' => request('mascota_id'),
    ], fn ($value) => filled($value));

    $kpiCards = [
        ['label' => 'Eventos', 'value' => $stats['total'] ?? 0, 'helper' => 'Atenciones en historial', 'tone' => 'blue', 'icon' => 'file'],
        ['label' => 'Hoy', 'value' => $stats['hoy'] ?? 0, 'helper' => 'Atenciones registradas hoy', 'tone' => 'emerald', 'icon' => 'today'],
        ['label' => 'Mes', 'value' => $stats['mes'] ?? 0, 'helper' => 'Actividad clínica del mes', 'tone' => 'amber', 'icon' => 'calendar'],
        ['label' => 'Pacientes', 'value' => $stats['mascotas'] ?? 0, 'helper' => 'Con historial registrado', 'tone' => 'violet', 'icon' => 'paw'],
    ];
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-5">
        <section class="rounded-[30px] border border-slate-200 bg-white px-5 py-5 shadow-[0_24px_60px_-42px_rgba(15,23,42,0.24)] sm:px-6 xl:px-7">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-[0_18px_32px_-18px_rgba(37,99,235,0.85)]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75a2.25 2.25 0 0 1 2.25 2.25v12A2.25 2.25 0 0 1 14.25 20.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6M9 12h6M9 15.75h3.75" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-700">Historial clínico</p>
                            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Historial de atenciones</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                Aquí se consulta la línea de tiempo del paciente: consultas, vacunas, controles, servicios, recetas y tratamientos. La ficha del paciente sigue estándo en Mascotas.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3.5 py-2 text-sm font-semibold text-blue-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                            Línea de tiempo
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3.5 py-2 text-sm font-semibold text-emerald-700 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            {{ $stats['hoy'] ?? 0 }} registros hoy
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
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Resumen del historial</p>
                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-3xl font-bold text-slate-950">{{ $stats['total'] ?? 0 }}</p>
                                <p class="mt-1 text-sm text-slate-500">eventos clínicos registrados</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-sm">Cronológico</span>
                        </div>
                    </div>
                    <a href="{{ route('atencion-rapida.index', array_filter(['mascota_id' => $prefillMascotaId, 'open_create' => 1])) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-[22px] bg-gradient-to-r from-blue-600 to-sky-500 px-4 py-4 text-sm font-semibold text-white shadow-[0_18px_34px_-18px_rgba(37,99,235,0.75)] transition hover:brightness-105">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Nueva atención
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($kpiCards as $card)
                @php
                    $toneClasses = match($card['tone']) {
                        'emerald' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white',
                        'amber' => 'border-amber-200 bg-gradient-to-br from-amber-50 to-white',
                        'violet' => 'border-violet-200 bg-gradient-to-br from-violet-50 to-white',
                        default => 'border-blue-200 bg-gradient-to-br from-blue-50 to-white',
                    };
                    $valueClasses = match($card['tone']) {
                        'emerald' => 'text-emerald-700',
                        'amber' => 'text-amber-700',
                        'violet' => 'text-violet-700',
                        default => 'text-blue-700',
                    };
                    $iconWrapClasses = match($card['tone']) {
                        'emerald' => 'bg-emerald-600 text-white shadow-emerald-200',
                        'amber' => 'bg-amber-500 text-white shadow-amber-200',
                        'violet' => 'bg-violet-500 text-white shadow-violet-200',
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
                                @case('today')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3M16 2v3M4 7h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                    @break
                                @case('calendar')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3M16 2v3M4 7h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                    @break
                                @case('paw')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 11.5c.8-1.5-.2-3.8-1.6-4.4-1.4-.6-2.9 1-3.3 2.5-.3 1.3.2 2.8 1.5 3.2 1.1.4 2.7 0 3.4-1.3Zm7 0c-.8-1.5.2-3.8 1.6-4.4 1.4-.6 2.9 1 3.3 2.5.3 1.3-.2 2.8-1.5 3.2-1.1.4-2.7 0-3.4-1.3ZM9 17c.7-1.8 2.2-2.8 3-2.8s2.3 1 3 2.8c.7 1.7-.2 3-3 3s-3.7-1.3-3-3Zm.1-8.8c0-1.6-1-3.2-2.3-3.2s-2.3 1.6-2.3 3.2c0 1.6 1 2.8 2.3 2.8s2.3-1.2 2.3-2.8Zm10.2 0c0-1.6-1-3.2-2.3-3.2s-2.3 1.6-2.3 3.2c0 1.6 1 2.8 2.3 2.8s2.3-1.2 2.3-2.8Z" /></svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75a2.25 2.25 0 0 1 2.25 2.25v12A2.25 2.25 0 0 1 14.25 20.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6M9 12h6M9 15.75h3.75" /></svg>
                            @endswitch
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="module-block px-5 py-5">
            <form method="GET" action="{{ route('historias-clinicas.index') }}">
                <div class="grid gap-3 xl:grid-cols-[minmax(0,2fr)_220px_220px_220px_auto] xl:items-end">
                    <div>
                        <label for="search" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Buscar atención</label>
                        <div class="flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition focus-within:border-blue-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-100">
                            <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por mascota, dueño, DNI o diagnóstico..." class="ml-3 w-full border-0 bg-transparent p-0 text-base text-slate-700 placeholder:text-slate-400 focus:ring-0">
                        </div>
                    </div>
                    <div>
                        <label for="origen" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Origen</label>
                        <select id="origen" name="origen" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">Todos</option>
                            <option value="programada" @selected(request('origen') === 'programada')>Atención programada</option>
                            <option value="manual" @selected(request('origen') === 'manual')>Sin cita</option>
                            <option value="preventiva" @selected(request('origen') === 'preventiva')>Preventiva</option>
                        </select>
                    </div>
                    <div>
                        <label for="tipo" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Tipo</label>
                        <select id="tipo" name="tipo" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">Todos</option>
                            <option value="consulta" @selected(request('tipo') === 'consulta')>Consulta</option>
                            <option value="vacunacion" @selected(request('tipo') === 'vacunacion')>Vacunación</option>
                            <option value="control" @selected(request('tipo') === 'control')>Control</option>
                            <option value="desparasitacion" @selected(request('tipo') === 'desparasitacion')>Desparasitación</option>
                            <option value="servicio" @selected(request('tipo') === 'servicio')>Servicio</option>
                            <option value="otro" @selected(request('tipo') === 'otro')>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label for="fecha" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Fecha</label>
                        <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-base font-semibold text-white shadow-[0_18px_34px_-22px_rgba(37,99,235,0.85)] transition hover:bg-blue-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                            Buscar
                        </button>
                        <a href="{{ route('historias-clinicas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50">
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
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Línea de tiempo clínica</p>
                        <h3 class="mt-1 text-2xl font-bold text-slate-900">Eventos del paciente</h3>
                        <p class="mt-1 text-sm text-slate-500">Cada tarjeta representa una atención ya realizada. Para datos generales, vacunas activas o propietario, abre la ficha desde Mascotas.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">{{ $historias->total() }} resultados</span>
                </div>
            </div>

            <div class="px-5 py-5 sm:px-6">
                @if($historias->count())
                    <div class="grid gap-4 xl:grid-cols-3">
                        @foreach($historias as $historia)
                            @php
                                $mascota = $historia->mascota;
                                $cliente = optional($mascota)->cliente;
                                $fotoMascota = optional($mascota)->foto ? asset('storage/' . $mascota->foto) : asset('storage/default.png');
                                $diagnosticoResumen = trim((string) $historia->diagnostico) !== ''
                                    ? \Illuminate\Support\Str::limit($historia->diagnostico, 150)
                                    : 'Sin diagnóstico detallado en este registro.';
                                $observacionesResumen = trim((string) $historia->observaciones) !== ''
                                    ? \Illuminate\Support\Str::limit($historia->observaciones, 160)
                                    : 'Sin observaciones adicionales registradas.';
                                $tratamientoActual = $historia->tratamientos->first();
                                $recetaActual = $historia->recetas->first();
                                $hasClinicalLinks = $historia->tratamientos->isNotEmpty()
                                    || $historia->recetas->isNotEmpty()
                                    || $historia->vacunas->isNotEmpty()
                                    || $historia->seguimientos->isNotEmpty()
                                    || $historia->ventas->isNotEmpty();
                                $origenBadge = match (true) {
                                    $historia->origen_atencion === 'programada' => ['label' => 'Programada', 'class' => 'border-blue-200 bg-blue-50 text-blue-700'],
                                    in_array($historia->tipo_atencion, ['vacunacion', 'desparasitacion'], true) => ['label' => 'Preventiva', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                                    default => ['label' => 'Sin cita', 'class' => 'border-amber-200 bg-amber-50 text-amber-700'],
                                };
                                $tipoBadge = match ($historia->tipo_atencion) {
                                    'vacunacion' => ['label' => 'Vacunación', 'class' => 'border-cyan-200 bg-cyan-50 text-cyan-700'],
                                    'control' => ['label' => 'Control', 'class' => 'border-violet-200 bg-violet-50 text-violet-700'],
                                    'desparasitacion' => ['label' => 'Desparasitación', 'class' => 'border-lime-200 bg-lime-50 text-lime-700'],
                                    'servicio' => ['label' => 'Servicio', 'class' => 'border-cyan-200 bg-cyan-50 text-cyan-700'],
                                    'otro' => ['label' => 'Otro', 'class' => 'border-slate-200 bg-slate-100 text-slate-700'],
                                    default => ['label' => 'Consulta', 'class' => 'border-slate-200 bg-slate-100 text-slate-700'],
                                };
                                $seguimientoActual = $historia->seguimientos
                                    ->reject(fn ($seguimiento) => $seguimiento->estado === 'cerrado')
                                    ->sortBy(fn ($seguimiento) => ($seguimiento->tipo === 'clinico' ? 0 : ($seguimiento->tipo === 'terapeutico' ? 1 : 2)))
                                    ->first();
                                $historiaPayload = [
                                    'id' => $historia->id,
                                    'mascota_id' => $historia->mascota_id,
                                    'fecha' => optional($historia->fecha)->format('Y-m-d'),
                                    'diagnostico' => $historia->diagnostico ?? '',
                                    'observaciones' => $historia->observaciones ?? '',
                                    'peso' => $historia->peso,
                                    'temperatura' => $historia->temperatura,
                                ];
                            @endphp
                            <article class="group rounded-[28px] border border-slate-200 bg-white p-4 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.22)] transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                        Evento clínico
                                    </div>
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $origenBadge['class'] }}">
                                            <span class="h-2 w-2 rounded-full bg-current opacity-70"></span>
                                            {{ $origenBadge['label'] }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold {{ $tipoBadge['class'] }}">
                                            {{ $tipoBadge['label'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-[112px_minmax(0,1fr)_220px] lg:items-start">
                                    <div class="relative w-28 shrink-0">
                                        <div class="absolute inset-0 rounded-[22px] bg-gradient-to-b from-blue-500/20 to-sky-400/10"></div>
                                        <img src="{{ $fotoMascota }}" alt="Foto de {{ optional($mascota)->nombre }}" class="relative h-28 w-full rounded-[22px] object-cover shadow-sm" onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';">
                                        <div class="absolute -bottom-3 left-3 rounded-2xl border border-white bg-white px-3 py-1.5 shadow-md">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Fecha</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900">{{ optional($historia->fecha)->format('d/m/Y') }}</p>
                                        </div>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Paciente</p>
                                                <h2 class="mt-1 truncate text-xl font-bold leading-tight text-slate-900">{{ optional($mascota)->nombre ?: 'Mascota no disponible' }}</h2>
                                                <p class="mt-1 truncate text-sm font-medium text-blue-700">{{ $cliente->nombre ?? 'Sin dueño registrado' }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-2">
                                            <div class="rounded-[22px] bg-slate-50 px-4 py-4">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Diagnostico</p>
                                                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $diagnosticoResumen }}</p>
                                            </div>
                                            <div class="rounded-[22px] bg-slate-50 px-4 py-4">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Observaciones</p>
                                                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $observacionesResumen }}</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if(filled($historia->peso))
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700">
                                                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                                    Peso {{ number_format((float) $historia->peso, 2) }} kg
                                                </span>
                                            @endif
                                            @if(filled($historia->temperatura))
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">
                                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                                    Temperatura {{ number_format((float) $historia->temperatura, 1) }} &deg;C
                                                </span>
                                            @endif
                                            @if($historia->servicioProducto)
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-700">
                                                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                                    Servicio {{ $historia->servicioProducto->nombre }}
                                                </span>
                                            @endif
                                            @if($cliente && filled($cliente->dni))
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                    DNI {{ $cliente->dni }}
                                                </span>
                                            @endif
                                            @if($tratamientoActual)
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                    Tratamiento activo
                                                </span>
                                            @endif
                                            @if($recetaActual)
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    Receta registrada
                                                </span>
                                            @endif
                                            @if($seguimientoActual)
                                                <span class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-semibold text-violet-700">
                                                    <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                                                    Seguimiento activo
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Acciones</p>
                                        <div class="mt-3 grid grid-cols-1 gap-2">
                                            <button type="button" onclick='openEditHistoriaModal(@json($historiaPayload))' class="historia-action-button historia-action-button--secondary">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.651-1.652a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM15 6.349 17.651 9" />
                                                </svg>
                                                Actualizar evento
                                            </button>

                                            <a href="{{ route('mascotas.index', array_filter(['open_ficha' => optional($mascota)->id])) }}" class="historia-action-button historia-action-button--success">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 5.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM17.25 8.25a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM8.25 9.75a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM20.25 13.5a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM14.856 20.523c-.64.473-1.664.727-2.856.727-1.192 0-2.216-.254-2.856-.727-.657-.486-1.03-1.159-1.03-1.898 0-.82.456-1.578 1.258-2.122.802-.545 1.92-.878 3.128-.878 1.208 0 2.326.333 3.128.878.802.544 1.258 1.302 1.258 2.122 0 .739-.373 1.412-1.03 1.898Z" />
                                                </svg>
                                                Abrir ficha del paciente
                                            </a>

                                            @if($hasClinicalLinks)
                                                <div class="historia-action-button border-slate-200 bg-white text-slate-400">
                                                    Registro protegido
                                                </div>
                                            @else
                                                <form method="POST" action="{{ route('historias-clinicas.destroy', $historia) }}" onsubmit="return confirm('&iquest;Eliminar este registro clínico?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="historia-action-button historia-action-button--danger">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-9.75 0V6A1.5 1.5 0 0 1 9.75 4.5h4.5A1.5 1.5 0 0 1 15.75 6v1.5m-8.25 0v10.125A1.875 1.875 0 0 0 9.375 19.5h5.25A1.875 1.875 0 0 0 16.5 17.625V7.5" />
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @endif
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75a2.25 2.25 0 0 1 2.25 2.25v12A2.25 2.25 0 0 1 14.25 20.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6M9 12h6M9 15.75h3.75" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold text-slate-900">Todavía no hay eventos clínicos registrados</h3>
                        <p class="mt-2 text-base text-slate-500">Registra una nueva atención para que el historial del paciente empiece a construirse automáticamente.</p>
                        <a href="{{ route('atencion-rapida.index', array_filter(['mascota_id' => $prefillMascotaId, 'open_create' => 1])) }}" class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-[0_18px_34px_-22px_rgba(37,99,235,0.85)] transition hover:bg-blue-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Nueva atención
                        </a>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 bg-white px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-slate-500">Mostrando {{ $historias->firstItem() ?? 0 }} a {{ $historias->lastItem() ?? 0 }} de {{ $historias->total() }} registros</div>
                    <div class="historias-pagination flex justify-center md:justify-end">{{ $historias->links('pagination::tailwind') }}</div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('historias-clinicas.modals.form', [
    'historiaMascotas' => $historiaMascotas,
    'prefillMascotaId' => $prefillMascotaId,
    'shouldOpenCreate' => $shouldOpenCreate,
])
<script src="{{ asset('js/modules/historias-clinicas.js') }}"></script>
</x-app-layout>



