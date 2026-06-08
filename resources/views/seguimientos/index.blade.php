<x-app-layout>
@php
    $stats = $stats ?? [];
    $selectedEstado = request('estado');
    $selectedTipo = request('tipo');
    $selectedMascota = $selectedMascota ?? null;
    $prefillHistoriaId = $prefillHistoriaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $defaultControlTime = $defaultControlTime ?? '09:00';
    $controlAlertDays = $controlAlertDays ?? 7;
    $baseFilters = array_filter([
        'search' => request('search'),
        'fecha' => request('fecha'),
        'mascota_id' => request('mascota_id'),
        'historia_clinica_id' => request('historia_clinica_id'),
        'tipo' => request('tipo'),
    ], fn ($value) => filled($value));
    $filterTabs = [
        ['key' => null, 'label' => 'Todos', 'value' => $stats['total'] ?? 0],
        ['key' => 'hoy', 'label' => 'Para hoy', 'value' => $stats['hoy'] ?? 0],
        ['key' => 'vencidos', 'label' => 'Vencidos', 'value' => $stats['vencidos'] ?? 0],
        ['key' => 'proximos', 'label' => 'Próximos', 'value' => $stats['proximos'] ?? 0],
        ['key' => 'pendientes', 'label' => 'Pendientes', 'value' => $stats['pendientes'] ?? 0],
        ['key' => 'cerrado', 'label' => 'Cerrados', 'value' => $stats['cerrados'] ?? 0],
    ];
    $summaryCards = [
        [
            'label' => 'Para hoy',
            'value' => $stats['hoy'] ?? 0,
            'hint' => 'controles listos para revisar',
            'icon' => 'calendar',
            'card' => 'border-amber-200 bg-gradient-to-br from-amber-50 via-white to-orange-50 shadow-amber-100/70',
            'iconBox' => 'bg-amber-500 text-white shadow-amber-200',
            'text' => 'text-amber-700',
        ],
        [
            'label' => 'Vencidos',
            'value' => $stats['vencidos'] ?? 0,
            'hint' => 'necesitan acción inmediata',
            'icon' => 'alert-triangle',
            'card' => 'border-rose-200 bg-gradient-to-br from-rose-50 via-white to-red-50 shadow-rose-100/70',
            'iconBox' => 'bg-rose-500 text-white shadow-rose-200',
            'text' => 'text-rose-700',
        ],
        [
            'label' => 'Próximos',
            'value' => $stats['proximos'] ?? 0,
            'hint' => 'dentro del rango de alerta',
            'icon' => 'clock',
            'card' => 'border-blue-200 bg-gradient-to-br from-blue-50 via-white to-cyan-50 shadow-blue-100/70',
            'iconBox' => 'bg-blue-600 text-white shadow-blue-200',
            'text' => 'text-blue-700',
        ],
        [
            'label' => 'Atendidos',
            'value' => $stats['controlados'] ?? 0,
            'hint' => 'con evolución registrada',
            'icon' => 'check-circle',
            'card' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-teal-50 shadow-emerald-100/70',
            'iconBox' => 'bg-emerald-500 text-white shadow-emerald-200',
            'text' => 'text-emerald-700',
        ],
    ];
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-5">
        <section class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-[0_24px_60px_-46px_rgba(15,23,42,0.28)] sm:p-6">
            <div class="grid gap-5 xl:grid-cols-[1fr_340px] xl:items-center">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-200">
                        <i data-feather="git-merge" class="h-6 w-6"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.26em] text-blue-600">Control clínico</p>
                        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">Controles de retorno</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                            Aquí se controla el retorno del paciente. El sistema crea la cita cuando una atención, vacuna o tratamiento necesita revisión, y habilita la acción clínica recién cuando llega su fecha y hora.
                        </p>
                        @if($selectedMascota)
                            <span class="mt-3 inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">Filtro: {{ $selectedMascota->nombre }}</span>
                        @endif
                    </div>
                </div>

                <div class="rounded-[24px] border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Flujo automático</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700">
                        Los retornos se alertan con {{ $controlAlertDays }} día{{ $controlAlertDays === 1 ? '' : 's' }} de anticipación y se agendan a las {{ $defaultControlTime }} si no se indica otra hora.
                    </p>
                    <div class="mt-3 grid gap-2 text-xs font-bold text-slate-600">
                        <div class="rounded-2xl bg-white px-3 py-2 shadow-sm">1. Atención, vacuna o tratamiento genera el control.</div>
                        <div class="rounded-2xl bg-white px-3 py-2 shadow-sm">2. El sistema crea la cita de retorno.</div>
                        <div class="rounded-2xl bg-white px-3 py-2 shadow-sm">3. En la fecha indicada se registra la evolución.</div>
                    </div>
                    <button type="button" onclick="openSeguimientoModal('{{ $prefillHistoriaId ?? '' }}')" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                        <i data-feather="plus" class="h-4 w-4"></i>
                        Agregar control manual
                    </button>
                </div>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($summaryCards as $card)
                <article class="group relative overflow-hidden rounded-[24px] border px-5 py-4 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl {{ $card['card'] }}">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-white/70 blur-2xl"></div>
                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.22em] {{ $card['text'] }}">{{ $card['label'] }}</p>
                            <p class="mt-2 text-4xl font-black tracking-tight text-slate-950">{{ $card['value'] }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-600">{{ $card['hint'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-lg transition group-hover:scale-105 {{ $card['iconBox'] }}">
                            <i data-feather="{{ $card['icon'] }}" class="h-5 w-5"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="module-block p-5">
            <form method="GET" action="{{ route('seguimientos.index') }}" class="grid gap-3 xl:grid-cols-[minmax(0,1.7fr)_210px_210px_190px_auto] xl:items-end">
                <div>
                    <label for="search" class="mb-2 block text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Buscar</label>
                    <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Mascota, dueño, DNI, motivo o evolución..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label for="estado" class="mb-2 block text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Estado</label>
                    <select id="estado" name="estado" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <option value="">Todos</option>
                        <option value="hoy" @selected(request('estado') === 'hoy')>Para hoy</option>
                        <option value="vencidos" @selected(request('estado') === 'vencidos')>Vencidos</option>
                        <option value="proximos" @selected(in_array(request('estado'), ['proximos', 'próximos'], true))>Próximos</option>
                        <option value="pendientes" @selected(request('estado') === 'pendientes')>Pendientes</option>
                        <option value="en_control" @selected(request('estado') === 'en_control')>Atendidos</option>
                        <option value="cerrado" @selected(request('estado') === 'cerrado')>Cerrados</option>
                    </select>
                </div>
                <div>
                    <label for="tipo" class="mb-2 block text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Tipo</label>
                    <select id="tipo" name="tipo" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <option value="">Todos</option>
                        <option value="clinico" @selected(request('tipo') === 'clinico')>Control clínico</option>
                        <option value="preventivo" @selected(request('tipo') === 'preventivo')>Vacuna pendiente</option>
                        <option value="terapeutico" @selected(request('tipo') === 'terapeutico')>Control de tratamiento</option>
                    </select>
                </div>
                <div>
                    <label for="fecha" class="mb-2 block text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Fecha</label>
                    <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </div>
                <div class="flex gap-2">
                    <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700">Buscar</button>
                    <a href="{{ route('seguimientos.index') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($filterTabs as $tab)
                    @php
                        $isActive = $tab['key'] ? $selectedEstado === $tab['key'] : blank($selectedEstado);
                        $params = $tab['key'] ? array_merge($baseFilters, ['estado' => $tab['key']]) : $baseFilters;
                    @endphp
                    <a href="{{ route('seguimientos.index', $params) }}" class="inline-flex items-center gap-2 rounded-full border px-3.5 py-2 text-xs font-bold transition {{ $isActive ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        {{ $tab['label'] }}
                        <span class="rounded-full bg-white px-2 py-0.5 text-[11px] shadow-sm">{{ $tab['value'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="module-block overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Controles programados</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-slate-950">Agenda de retornos</h2>
                        <p class="mt-1 text-sm text-slate-500">Prioriza vencidos y controles de hoy. Los retornos futuros quedan visibles, pero su atención se realiza cuando corresponde.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ $seguimientos->total() }} resultado{{ $seguimientos->total() === 1 ? '' : 's' }}</span>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                @if($seguimientos->count())
                    <div class="grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
                        @foreach($seguimientos as $seguimiento)
                            @php
                                $historia = $seguimiento->historiaClinica;
                                $mascota = $seguimiento->mascota;
                                $cliente = optional($mascota)->cliente;
                                $fotoMascota = optional($mascota)->foto ? \App\Support\PhotoUrl::make($mascota->foto) : \App\Support\PhotoUrl::make(null);
                                $tone = $seguimiento->ui_bucket_tone ?? 'violet';
                                $badgeClass = match($tone) {
                                    'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
                                    'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    'blue' => 'border-blue-200 bg-blue-50 text-blue-700',
                                    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'slate' => 'border-slate-300 bg-slate-100 text-slate-700',
                                    default => 'border-violet-200 bg-violet-50 text-violet-700',
                                };
                                $railClass = match($tone) {
                                    'rose' => 'bg-rose-500',
                                    'amber' => 'bg-amber-500',
                                    'blue' => 'bg-blue-500',
                                    'emerald' => 'bg-emerald-500',
                                    'slate' => 'bg-slate-400',
                                    default => 'bg-violet-500',
                                };
                                $cardShell = match($tone) {
                                    'rose' => 'border-rose-200 bg-gradient-to-br from-white via-rose-50/80 to-white shadow-rose-100/70',
                                    'amber' => 'border-amber-200 bg-gradient-to-br from-white via-amber-50/80 to-white shadow-amber-100/70',
                                    'blue' => 'border-blue-200 bg-gradient-to-br from-white via-blue-50/80 to-white shadow-blue-100/70',
                                    'emerald' => 'border-emerald-200 bg-gradient-to-br from-white via-emerald-50/80 to-white shadow-emerald-100/70',
                                    'slate' => 'border-slate-200 bg-gradient-to-br from-white via-slate-50 to-white shadow-slate-100',
                                    default => 'border-violet-200 bg-gradient-to-br from-white via-violet-50/80 to-white shadow-violet-100/70',
                                };
                                $statusIcon = match($tone) {
                                    'rose' => 'alert-triangle',
                                    'amber' => 'clock',
                                    'blue' => 'calendar',
                                    'emerald' => 'check-circle',
                                    'slate' => 'archive',
                                    default => 'activity',
                                };
                                $statusIconBox = match($tone) {
                                    'rose' => 'bg-rose-500 text-white shadow-rose-200',
                                    'amber' => 'bg-amber-500 text-white shadow-amber-200',
                                    'blue' => 'bg-blue-600 text-white shadow-blue-200',
                                    'emerald' => 'bg-emerald-500 text-white shadow-emerald-200',
                                    'slate' => 'bg-slate-500 text-white shadow-slate-200',
                                    default => 'bg-violet-600 text-white shadow-violet-200',
                                };
                                $typeIcon = match($seguimiento->tipo) {
                                    'preventivo' => 'shield',
                                    'terapeutico' => 'activity',
                                    default => 'heart',
                                };
                                $payload = [
                                    'id' => $seguimiento->id,
                                    'historia_clinica_id' => $seguimiento->historia_clinica_id,
                                    'veterinario_id' => $seguimiento->veterinario_id,
                                    'tipo' => $seguimiento->tipo,
                                    'origen' => $seguimiento->origen,
                                    'titulo' => $seguimiento->titulo,
                                    'estado' => $seguimiento->estado,
                                    'motivo' => $seguimiento->motivo,
                                    'notas' => $seguimiento->notas,
                                    'evolucion' => $seguimiento->evolucion,
                                    'fecha_inicio' => optional($seguimiento->fecha_inicio)->format('Y-m-d'),
                                    'dias_retorno' => $seguimiento->dias_retorno,
                                    'fecha_proximo_control' => optional($seguimiento->fecha_proximo_control)->format('Y-m-d'),
                                    'hora_proximo_control' => $seguimiento->hora_proximo_control ? \Illuminate\Support\Str::of($seguimiento->hora_proximo_control)->substr(0, 5)->toString() : $defaultControlTime,
                                ];
                                $canApplyVaccine = $seguimiento->tipo === 'preventivo'
                                    && $seguimiento->origen === 'vacuna'
                                    && $seguimiento->vacuna
                                    && $seguimiento->vacuna->estado_aplicacion === 'programada'
                                    && $seguimiento->estado !== 'cerrado'
                                    && ($seguimiento->ui_is_due ?? false);
                                $isPendingScheduledVaccine = $seguimiento->tipo === 'preventivo'
                                    && $seguimiento->origen === 'vacuna'
                                    && $seguimiento->vacuna
                                    && $seguimiento->vacuna->estado_aplicacion === 'programada'
                                    && $seguimiento->estado !== 'cerrado'
                                    && !($seguimiento->ui_is_due ?? false);
                                $applyVaccinePayload = [
                                    'id' => $seguimiento->id,
                                    'url' => route('seguimientos.aplicar-vacuna', $seguimiento),
                                    'mascota' => optional($mascota)->nombre ?: 'Paciente',
                                    'cliente' => $cliente->nombre ?? 'Sin propietario',
                                    'vacuna' => optional($seguimiento->vacuna)->nombre ?: 'Vacuna programada',
                                    'fecha_programada' => optional(optional($seguimiento->vacuna)->fecha_programada)->format('d/m/Y') ?: optional($seguimiento->fecha_proximo_control)->format('d/m/Y'),
                                    'fecha_aplicacion' => now()->toDateString(),
                                ];
                            @endphp
                            <article class="group relative overflow-hidden rounded-[28px] border shadow-lg transition hover:-translate-y-1 hover:shadow-2xl {{ $cardShell }}">
                                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-white/80 blur-2xl"></div>
                                <div class="absolute inset-y-0 left-0 w-1.5 {{ $railClass }}"></div>
                                <div class="p-4 pl-5">
                                    <div class="flex gap-4">
                                        <div class="relative shrink-0">
                                            <img src="{{ $fotoMascota }}" alt="Foto de {{ optional($mascota)->nombre }}" class="h-20 w-20 rounded-3xl border-4 border-white object-cover shadow-lg" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">
                                            <span class="absolute -bottom-2 -right-2 flex h-9 w-9 items-center justify-center rounded-2xl shadow-lg {{ $statusIconBox }}">
                                                <i data-feather="{{ $statusIcon }}" class="h-4 w-4"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-lg font-black tracking-tight text-slate-950">{{ $seguimiento->titulo }}</p>
                                                    <p class="mt-1 truncate text-sm font-semibold text-slate-500">{{ optional($mascota)->nombre ?: 'Paciente no disponible' }} · {{ $cliente->nombre ?? 'Sin propietario' }}</p>
                                                </div>
                                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-extrabold shadow-sm {{ $badgeClass }}">
                                                    <i data-feather="{{ $statusIcon }}" class="h-3.5 w-3.5"></i>
                                                    {{ $seguimiento->ui_bucket_label }}
                                                </span>
                                            </div>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-2.5 py-1 text-[11px] font-extrabold text-white shadow-sm shadow-blue-100">
                                                    <i data-feather="{{ $typeIcon }}" class="h-3.5 w-3.5"></i>
                                                    {{ $seguimiento->ui_type_label }}
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-600 shadow-sm ring-1 ring-slate-200">
                                                    <i data-feather="git-merge" class="h-3.5 w-3.5"></i>
                                                    {{ $seguimiento->ui_origin_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                        <div class="rounded-2xl border border-slate-200 bg-white/85 px-3 py-3 shadow-sm">
                                            <p class="flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-[0.18em] text-slate-400">
                                                <i data-feather="play-circle" class="h-3.5 w-3.5"></i>
                                                Inicio
                                            </p>
                                            <p class="mt-1 text-sm font-extrabold text-slate-900">{{ optional($seguimiento->fecha_inicio)->format('d/m/Y') ?: 'Sin fecha' }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-blue-100 bg-blue-50/80 px-3 py-3 shadow-sm">
                                            <p class="flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-[0.18em] text-blue-500">
                                                <i data-feather="repeat" class="h-3.5 w-3.5"></i>
                                                Retorno
                                            </p>
                                            <p class="mt-1 text-sm font-extrabold text-slate-900">{{ optional($seguimiento->fecha_proximo_control)->format('d/m/Y') ?: 'Sin fecha' }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-3 py-3 shadow-sm">
                                            <p class="flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-[0.18em] text-emerald-500">
                                                <i data-feather="calendar" class="h-3.5 w-3.5"></i>
                                                Cita
                                            </p>
                                            <p class="mt-1 text-sm font-extrabold text-slate-900">
                                                @if($seguimiento->cita)
                                                    {{ \Illuminate\Support\Str::of($seguimiento->cita->hora)->substr(0, 5) }}
                                                @else
                                                    Sin cita
                                                @endif
                                            </p>
                                            <p class="mt-1 text-[11px] font-bold text-emerald-700">{{ $seguimiento->ui_due_message }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-3">
                                        <div class="rounded-2xl border border-white/80 bg-white/75 px-3 py-3 shadow-sm">
                                            <p class="flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.2em] text-slate-500">
                                                <i data-feather="file-text" class="h-3.5 w-3.5"></i>
                                                Motivo
                                            </p>
                                            <p class="mt-1 text-sm leading-6 text-slate-700">{{ \Illuminate\Support\Str::limit($seguimiento->motivo, 150) }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white px-3 py-3 shadow-sm">
                                            <p class="flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-600">
                                                <i data-feather="trending-up" class="h-3.5 w-3.5"></i>
                                                Evolución
                                            </p>
                                            <p class="mt-1 text-sm leading-6 text-emerald-900">{{ \Illuminate\Support\Str::limit($seguimiento->evolucion ?: 'Aún no se registra evolución.', 135) }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                        @if($canApplyVaccine)
                                            <button type="button" onclick='openApplySeguimientoVacunaModal(@json($applyVaccinePayload))' class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 px-3 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-100 transition hover:from-emerald-700 hover:to-teal-600">
                                                <i data-feather="shield" class="h-4 w-4"></i>
                                                Aplicar vacuna
                                            </button>
                                        @elseif($isPendingScheduledVaccine)
                                            <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-extrabold text-slate-500">
                                                <i data-feather="lock" class="h-4 w-4"></i>
                                                Pendiente hasta su cita
                                            </button>
                                        @else
                                            <button type="button" onclick='openEditSeguimientoModal(@json($payload))' class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-500 px-3 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-blue-100 transition hover:from-blue-700 hover:to-cyan-600">
                                                <i data-feather="edit-3" class="h-4 w-4"></i>
                                                {{ $seguimiento->estado === 'cerrado' ? 'Ver detalle' : (($seguimiento->ui_is_due ?? false) ? 'Registrar evolución' : 'Reprogramar control') }}
                                            </button>
                                        @endif
                                        @if($seguimiento->cita)
                                            <a href="{{ route('citas.index', ['mascota_id' => optional($mascota)->id, 'fecha' => optional($seguimiento->cita->fecha)->format('Y-m-d')]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-white px-3 py-2.5 text-sm font-bold text-blue-700 shadow-sm transition hover:bg-blue-50">
                                                <i data-feather="calendar" class="h-4 w-4"></i>
                                                Ver cita
                                            </a>
                                        @else
                                            <a href="{{ route('historias-clinicas.index', ['mascota_id' => optional($mascota)->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                                <i data-feather="file-text" class="h-4 w-4"></i>
                                                Ver historial
                                            </a>
                                        @endif
                                        @if($seguimiento->estado !== 'cerrado')
                                            <form method="POST" action="{{ route('seguimientos.cerrar', $seguimiento) }}" onsubmit="return confirm('¿Cerrar este control? Si tiene una cita pendiente de retorno, se retirará de la agenda.');" class="sm:col-span-2">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm font-extrabold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                                                    <i data-feather="check-circle" class="h-4 w-4"></i>
                                                    Cerrar control
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                        <h3 class="text-xl font-extrabold text-slate-950">No hay controles registrados</h3>
                        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-500">Cuando una atención, vacuna o tratamiento necesite retorno, el sistema creará aquí el control y su cita automáticamente.</p>
                        <button type="button" onclick="openSeguimientoModal('{{ $prefillHistoriaId ?? '' }}')" class="mt-5 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700">Agregar control manual</button>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 bg-white px-6 py-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm text-slate-500">Mostrando {{ $seguimientos->firstItem() ?? 0 }} a {{ $seguimientos->lastItem() ?? 0 }} de {{ $seguimientos->total() }} controles</p>
                    <div>{{ $seguimientos->links('pagination::tailwind') }}</div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('seguimientos.modals.form', [
    'historiaCatalogo' => $historiaCatalogo,
    'veterinarios' => $veterinarios,
    'prefillHistoriaId' => $prefillHistoriaId,
    'shouldOpenCreate' => $shouldOpenCreate,
    'defaultControlTime' => $defaultControlTime,
])
@include('seguimientos.modals.apply-vaccine')
<script src="{{ asset('js/modules/seguimientos.js') }}?v={{ filemtime(public_path('js/modules/seguimientos.js')) }}"></script>
</x-app-layout>
