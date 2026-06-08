<x-app-layout>
@php
    $stats = $stats ?? [];
    $selectedEstado = request('estado');
    $selectedMascota = $selectedMascota ?? null;
    $prefillHistoriaId = $prefillHistoriaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $today = now()->startOfDay();
@endphp
<div class="module-page">
    <div class="module-page__inner">
        <section class="module-shell">
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25h6l1.5 2.25h2.25A1.75 1.75 0 0 1 20.5 9.25v8A1.75 1.75 0 0 1 18.75 19H5.25A1.75 1.75 0 0 1 3.5 17.25v-8A1.75 1.75 0 0 1 5.25 7.5H7.5L9 5.25Zm3 4.5v4.5m-2.25-2.25h4.5" /></svg>
                        </div>
                        <div>
                            <p class="text-base font-semibold uppercase tracking-[0.22em] text-amber-600">Modulo de tratamientos</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Tratamientos</h1>
                            <p class="mt-1 text-base text-slate-500">Gestiona tratamientos activos, seguimiento y costos desde una sola pantalla.</p>
                            @if(!empty($selectedMascota))
                                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">Tratamientos filtrados: {{ $selectedMascota->nombre }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-500"><span class="font-semibold text-slate-700">{{ $stats['total'] ?? 0 }}</span> tratamientos registrados</div>
                        <button type="button" onclick="openTratamientoModal('{{ $prefillHistoriaId ?? '' }}')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-amber-200 transition hover:bg-amber-600">Nuevo tratamiento</button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 sm:px-8 space-y-5">
                <div class="rounded-3xl border border-amber-100 bg-amber-50/70 px-5 py-4 text-sm leading-6 text-amber-900 shadow-sm">
                    Usa este módulo para revisar el seguimiento del tratamiento, actualizarlo y generar la venta cuando corresponda, sin repetir la atención clínica.
                </div>

                <div class="grid gap-3 xl:grid-cols-5">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Total</p><p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Activos</p><p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['activos'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-600">Por vencer</p><p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['por_vencer'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">Programados</p><p class="mt-2 text-2xl font-bold text-blue-700">{{ $stats['programados'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-600">Finalizados</p><p class="mt-2 text-2xl font-bold text-rose-700">{{ $stats['finalizados'] ?? 0 }}</p></div>
                </div>

                <form method="GET" action="{{ route('tratamientos.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(220px,1fr)_minmax(200px,0.9fr)_auto] xl:items-end">
                        <div>
                            <label for="search" class="mb-2 block text-base font-semibold text-slate-600">Buscar tratamiento</label>
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por tratamiento, mascota, due&ntilde;o o DNI..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                        </div>
                        <div>
                            <label for="estado" class="mb-2 block text-base font-semibold text-slate-600">Estado</label>
                            <select id="estado" name="estado" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                <option value="">Todos</option>
                                <option value="activos" @selected(request('estado') === 'activos')>Activos</option>
                                <option value="por_vencer" @selected(request('estado') === 'por_vencer')>Por vencer</option>
                                <option value="programados" @selected(request('estado') === 'programados')>Programados</option>
                                <option value="finalizados" @selected(request('estado') === 'finalizados')>Finalizados</option>
                            </select>
                        </div>
                        <div>
                            <label for="fecha" class="mb-2 block text-base font-semibold text-slate-600">Fecha de inicio</label>
                            <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                            <button type="submit" class="rounded-2xl bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-amber-200 transition hover:bg-amber-600">Buscar</button>
                            <a href="{{ route('tratamientos.index') }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a>
                        </div>
                    </div>
                </form>

                <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('tratamientos.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id')])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedEstado ? 'border-slate-200 bg-white text-slate-600' : 'border-amber-200 bg-amber-50 text-amber-700' }}">Todos</a>
                            <a href="{{ route('tratamientos.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id'), 'estado' => 'activos'])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedEstado === 'activos' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600' }}">Activos</a>
                            <a href="{{ route('tratamientos.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id'), 'estado' => 'por_vencer'])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedEstado === 'por_vencer' ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-600' }}">Por vencer</a>
                            <a href="{{ route('tratamientos.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id'), 'estado' => 'programados'])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedEstado === 'programados' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600' }}">Programados</a>
                            <a href="{{ route('tratamientos.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id'), 'estado' => 'finalizados'])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedEstado === 'finalizados' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-200 bg-white text-slate-600' }}">Finalizados</a>
                        </div>
                        <div class="rounded-full bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700">{{ $tratamientos->total() }} resultados</div>
                    </div>
                    <div class="px-6 py-6">
@if($tratamientos->count())
    <div class="grid gap-4 xl:grid-cols-3">
        @foreach($tratamientos as $tratamiento)
            @php
                $historia = $tratamiento->historiaClinica;
                $mascota = optional($historia)->mascota;
                $cliente = optional($mascota)->cliente;
                $fotoMascota = optional($mascota)->foto ? \App\Support\PhotoUrl::make($mascota->foto) : \App\Support\PhotoUrl::make(null);
                $fechaInicio = $tratamiento->fecha_inicio;
                $fechaFin = $tratamiento->fecha_fin;
                $isProgramado = $fechaInicio && $fechaInicio->gt($today);
                $isFinalizado = $fechaFin && $fechaFin->lt($today);
                $isPorVencer = !$isProgramado && !$isFinalizado && $fechaFin && $fechaFin->between($today, $today->copy()->addDays(3));
                $estadoBadge = $isProgramado
                    ? ['label' => 'Programado', 'class' => 'border-blue-200 bg-blue-50 text-blue-700']
                    : ($isFinalizado
                        ? ['label' => 'Finalizado', 'class' => 'border-rose-200 bg-rose-50 text-rose-700']
                        : ($isPorVencer
                            ? ['label' => 'Por vencer', 'class' => 'border-amber-200 bg-amber-50 text-amber-700']
                            : ['label' => 'Activo', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700']));
                $duracionDias = ($fechaInicio && $fechaFin) ? $fechaInicio->diffInDays($fechaFin) + 1 : null;
                $diasRestantes = (!$isProgramado && !$isFinalizado && $fechaFin) ? $today->diffInDays($fechaFin, false) : null;
                $proximoControl = optional($tratamiento->proximo_control)->format('d/m/Y');
                $alertaSeguimiento = $isProgramado
                    ? 'Inicia el ' . $fechaInicio->format('d/m/Y')
                    : ($isFinalizado
                        ? 'Finalizo el ' . $fechaFin->format('d/m/Y')
                        : ($fechaFin
                            ? ($diasRestantes <= 0 ? 'Cierra hoy' : 'Restan ' . $diasRestantes . ' días')
                            : 'Tratamiento abierto sin fecha fin'));
                $tratamientoPayload = [
                    'id' => $tratamiento->id,
                    'historia_clinica_id' => $tratamiento->historia_clinica_id,
                    'veterinario_id' => $tratamiento->veterinario_id,
                    'descripcion' => $tratamiento->descripcion,
                    'costo' => (string) $tratamiento->costo,
                    'fecha_inicio' => optional($tratamiento->fecha_inicio)->format('Y-m-d'),
                    'fecha_fin' => optional($tratamiento->fecha_fin)->format('Y-m-d'),
                    'proximo_control' => optional($tratamiento->proximo_control)->format('Y-m-d'),
                    'productos' => $tratamiento->productos->map(fn ($producto) => [
                        'id' => $producto->id,
                        'cantidad' => (int) ($producto->pivot->cantidad ?? 1),
                    ])->values()->all(),
                ];
            @endphp
            <article class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/70">
                <div class="flex gap-4">
                    <img src="{{ $fotoMascota }}" alt="Foto de {{ optional($mascota)->nombre }}" class="h-28 w-28 rounded-[18px] object-cover" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate text-xl font-bold text-slate-900">{{ optional($mascota)->nombre ?: 'Mascota no disponible' }}</h2>
                                <p class="mt-1 truncate text-sm font-medium text-slate-500">{{ $cliente->nombre ?? 'Sin due&ntilde;o registrado' }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-semibold {{ $estadoBadge['class'] }}">{{ $estadoBadge['label'] }}</span>
                        </div>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                <p class="font-semibold text-slate-500">Profesional</p>
                                <p class="mt-1">{{ optional($tratamiento->veterinario)->nombre }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                <p class="font-semibold text-slate-500">Duracion</p>
                                <p class="mt-1">{{ $duracionDias ? $duracionDias . ' dias' : 'Abierto' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                <p class="font-semibold text-slate-500">Costo</p>
                                <p class="mt-1">S/ {{ number_format((float) $tratamiento->costo, 2) }}</p>
                            </div>
                        </div>
                        <div class="mt-3 rounded-2xl bg-amber-50 px-3 py-3 text-sm leading-6 text-amber-900">
                            <p class="font-semibold text-amber-700">Plan terapeutico</p>
                            <p class="mt-1">{{ \Illuminate\Support\Str::limit($tratamiento->descripcion, 150) }}</p>
                        </div>
                        <div class="mt-3 grid gap-2 md:grid-cols-2">
                            <div class="rounded-2xl bg-emerald-50 px-3 py-3 text-sm leading-6 text-emerald-900">
                                <p class="font-semibold text-emerald-700">Seguimiento</p>
                                <p class="mt-1">{{ $alertaSeguimiento }}</p>
                            </div>
                            <div class="rounded-2xl bg-blue-50 px-3 py-3 text-sm leading-6 text-blue-900">
                                <p class="font-semibold text-blue-700">Atencion origen</p>
                                <p class="mt-1">{{ \Illuminate\Support\Str::limit(optional($historia)->diagnostico ?: optional($historia)->observaciones ?: 'Sin diagnóstico asociado.', 120) }}</p>
                            </div>
                        </div>
                        <div class="mt-3 grid gap-2 md:grid-cols-2">
                            <div class="rounded-2xl bg-violet-50 px-3 py-3 text-sm leading-6 text-violet-900">
                                <p class="font-semibold text-violet-700">Próximo control</p>
                                <p class="mt-1">{{ $proximoControl ?: 'Se calcula automáticamente con la fecha de cierre.' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-100 px-3 py-3 text-sm leading-6 text-slate-700">
                                <p class="font-semibold text-slate-700">Insumos vinculados</p>
                                <p class="mt-1">{{ $tratamiento->productos->count() ? $tratamiento->productos->map(fn ($producto) => $producto->nombre . ' x' . (int) ($producto->pivot->cantidad ?? 1))->implode(' | ') : 'Aún no se agregaron productos o servicios.' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 xl:grid-cols-3">
                            <button type="button" onclick='openEditTratamientoModal(@json($tratamientoPayload))' class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2.5 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-50">Actualizar seguimiento</button>
                            <a href="{{ route('historias-clinicas.index', ['mascota_id' => optional($mascota)->id]) }}" class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-white px-3 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">Historial clínico</a>
                            <a href="{{ route('ventas.index', ['tratamiento_id' => $tratamiento->id, 'open_create' => 1]) }}" class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50">Generar venta</a>
                            <form method="POST" action="{{ route('tratamientos.destroy', $tratamiento) }}" onsubmit="return confirm('&iquest;Eliminar este tratamiento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-2xl border border-rose-200 bg-white px-3 py-2.5 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-50">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@else
    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
        <h3 class="text-xl font-semibold text-slate-900">Todavía no hay tratamientos registrados</h3>
        <p class="mt-2 text-base text-slate-500">Puedes registrar el primero desde aquí o dejar que nazca automáticamente desde la atención clínica.</p>
        <button type="button" onclick="openTratamientoModal('{{ $prefillHistoriaId ?? '' }}')" class="mt-5 rounded-2xl bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-amber-200 transition hover:bg-amber-600">Registrar tratamiento</button>
    </div>
@endif
                    </div>
                    <div class="border-t border-slate-100 bg-white px-6 py-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="text-base text-slate-500">Mostrando {{ $tratamientos->firstItem() ?? 0 }} a {{ $tratamientos->lastItem() ?? 0 }} de {{ $tratamientos->total() }} tratamientos</div>
                            <div class="flex justify-center md:justify-end">{{ $tratamientos->links('pagination::tailwind') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('tratamientos.modals.form', [
    'historiaCatalogo' => $historiaCatalogo,
    'veterinarios' => $veterinarios,
    'productos' => $productos,
    'prefillHistoriaId' => $prefillHistoriaId,
    'shouldOpenCreate' => $shouldOpenCreate,
])
<script src="{{ asset('js/modules/tratamientos.js') }}?v={{ filemtime(public_path('js/modules/tratamientos.js')) }}"></script>
</x-app-layout>




