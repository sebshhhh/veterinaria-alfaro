<x-app-layout>
@php
    $stats = $stats ?? [];
    $selectedPeriodo = request('periodo');
    $selectedMascota = $selectedMascota ?? null;
    $prefillHistoriaId = $prefillHistoriaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
@endphp
<div class="module-page">
    <div class="module-page__inner">
        <section class="module-shell">
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h7.5L19.5 8.75v11.5A1.75 1.75 0 0 1 17.75 22H7.25A1.75 1.75 0 0 1 5.5 20.25V5.5A1.75 1.75 0 0 1 7.25 3.75Zm2 7.25h6m-6 4h6" /></svg>
                        </div>
                        <div>
                            <p class="text-base font-semibold uppercase tracking-[0.22em] text-blue-600">Modulo de recetas</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Recetas</h1>
                            <p class="mt-1 text-base text-slate-500">Centraliza medicamentos e indicaciones emitidas en consulta para que el seguimiento sea claro.</p>
                            @if(!empty($selectedMascota))
                                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">Recetas filtradas: {{ $selectedMascota->nombre }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-500"><span class="font-semibold text-slate-700">{{ $stats['total'] ?? 0 }}</span> recetas registradas</div>
                        <button type="button" onclick="openRecetaModal('{{ $prefillHistoriaId ?? '' }}')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">Nueva receta</button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 sm:px-8 space-y-5">
                <div class="rounded-3xl border border-blue-100 bg-blue-50/70 px-5 py-4 text-sm leading-6 text-blue-900 shadow-sm">
                    Usa este módulo para consultar, corregir o imprimir recetas ya emitidas desde la atención clínica, sin volver a registrar toda la consulta.
                </div>

                <div class="grid gap-3 xl:grid-cols-5">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Total</p><p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Hoy</p><p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['hoy'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-sky-100 bg-sky-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-600">Semana</p><p class="mt-2 text-2xl font-bold text-sky-700">{{ $stats['semana'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-600">Este mes</p><p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['mes'] ?? 0 }}</p></div>
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 shadow-sm"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">Mascotas</p><p class="mt-2 text-2xl font-bold text-indigo-700">{{ $stats['mascotas'] ?? 0 }}</p></div>
                </div>

                <form method="GET" action="{{ route('recetas.index') }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(220px,0.9fr)_auto] xl:items-end">
                        <div>
                            <label for="search" class="mb-2 block text-base font-semibold text-slate-600">Buscar receta</label>
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por medicamentos, mascota, propietario o DNI..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div>
                            <label for="fecha" class="mb-2 block text-base font-semibold text-slate-600">Fecha de registro</label>
                            <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row xl:justify-end"><button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">Buscar</button><a href="{{ route('recetas.index') }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a></div>
                    </div>
                </form>

                <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('recetas.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id')])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedPeriodo ? 'border-slate-200 bg-white text-slate-600' : 'border-blue-200 bg-blue-50 text-blue-700' }}">Todas</a>
                            <a href="{{ route('recetas.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id'), 'periodo' => 'hoy'])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedPeriodo === 'hoy' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600' }}">Hoy</a>
                            <a href="{{ route('recetas.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id'), 'periodo' => 'semana'])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedPeriodo === 'semana' ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-slate-200 bg-white text-slate-600' }}">Semana</a>
                            <a href="{{ route('recetas.index', array_filter(['search' => request('search'), 'fecha' => request('fecha'), 'mascota_id' => request('mascota_id'), 'historia_clinica_id' => request('historia_clinica_id'), 'periodo' => 'mes'])) }}" class="rounded-full border px-4 py-2 text-base font-semibold transition {{ $selectedPeriodo === 'mes' ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-600' }}">Este mes</a>
                        </div>
                        <div class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700">{{ $recetas->total() }} resultados</div>
                    </div>
                    <div class="px-6 py-6">
@if($recetas->count())
    <div class="grid gap-4 xl:grid-cols-3">
        @foreach($recetas as $receta)
            @php
                $historia = $receta->historiaClinica;
                $mascota = optional($historia)->mascota;
                $cliente = optional($mascota)->cliente;
                $fotoMascota = optional($mascota)->foto ? asset('storage/' . $mascota->foto) : asset('storage/default.png');
                $medicamentosList = collect(preg_split('/[\n,;]+/', (string) $receta->medicamentos))->map(fn ($item) => trim($item))->filter()->take(4);
                $indicacionesList = collect(preg_split('/[\n;]+/', (string) $receta->indicaciones))->map(fn ($item) => trim($item))->filter()->take(4);
                $recetaPayload = ['id' => $receta->id, 'historia_clinica_id' => $receta->historia_clinica_id, 'medicamentos' => $receta->medicamentos, 'indicaciones' => $receta->indicaciones];
                $tipoHistoria = match ($historia?->tipo_atencion) {
                    'vacunacion' => 'Vacunación',
                    'control' => 'Control',
                    'desparasitacion' => 'Desparasitación',
                    'servicio' => 'Servicio',
                    'otro' => 'Otra atencion',
                    default => 'Consulta',
                };
                $printPayload = [
                    'logo' => asset('img/logo.png'),
                    'petName' => optional($mascota)->nombre,
                    'petType' => optional($mascota)->tipo_animal,
                    'petBreed' => optional($mascota)->raza,
                    'petColor' => optional($mascota)->color,
                    'petSex' => optional($mascota)->sexo,
                    'owner' => optional($cliente)->nombre,
                    'ownerDni' => optional($cliente)->dni,
                    'ownerPhone' => optional($cliente)->telefono,
                    'ownerAddress' => optional($cliente)->direccion,
                    'date' => optional($historia->fecha)->format('d/m/Y') ?: optional($receta->created_at)->format('d/m/Y'),
                    'diagnosis' => optional($historia)->diagnostico,
                    'historyType' => $tipoHistoria,
                    'serviceName' => optional($historia?->servicioProducto)->nombre,
                    'weight' => optional($historia)->peso,
                    'temperature' => optional($historia)->temperatura,
                    'medicamentos' => $receta->medicamentos,
                    'indicaciones' => $receta->indicaciones,
                ];
            @endphp
            <article class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/70">
                <div class="flex gap-4">
                    <img src="{{ $fotoMascota }}" alt="Foto de {{ optional($mascota)->nombre }}" class="h-28 w-28 rounded-[18px] object-cover" onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate text-xl font-bold text-slate-900">{{ optional($mascota)->nombre ?: 'Mascota no disponible' }}</h2>
                                <p class="mt-1 truncate text-sm font-medium text-slate-500">{{ $cliente->nombre ?? 'Sin propietario registrado' }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                {{ optional($historia->fecha)->format('d/m/Y') ?: optional($receta->created_at)->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="mt-3 rounded-2xl bg-blue-50 px-3 py-3 text-sm leading-6 text-blue-900">
                            <div class="mb-3 flex flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ optional($historia->fecha)->format('d/m/Y') ?: optional($receta->created_at)->format('d/m/Y') }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $tipoHistoria }}
                                </span>
                                @if(optional($mascota)->raza)
                                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ $mascota->raza }}
                                    </span>
                                @endif
                                @if(!is_null(optional($historia)->peso))
                                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ $historia->peso }} kg
                                    </span>
                                @endif
                                @if(!is_null(optional($historia)->temperatura))
                                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ $historia->temperatura }} C
                                    </span>
                                @endif
                            </div>
                            <p class="font-semibold text-blue-700">Medicamentos prescritos</p>
                            <div class="mt-2 space-y-2">
                                @forelse($medicamentosList as $medicamento)
                                    <div class="flex items-start gap-2"><span class="mt-2 h-2 w-2 rounded-full bg-blue-500"></span><span>{{ $medicamento }}</span></div>
                                @empty
                                    <p class="text-sm text-blue-700/70">Sin medicamentos detallados.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="mt-3 rounded-2xl bg-slate-100 px-3 py-3 text-sm leading-6 text-slate-700">
                            <p class="font-semibold text-slate-700">Indicaciones clinicas</p>
                            <div class="mt-2 space-y-2">
                                @forelse($indicacionesList as $indicacion)
                                    <div class="flex items-start gap-2"><span class="mt-2 h-2 w-2 rounded-full bg-slate-500"></span><span>{{ $indicacion }}</span></div>
                                @empty
                                    <p class="text-sm text-slate-500">Sin indicaciones detalladas.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="mt-3 rounded-2xl bg-amber-50 px-3 py-3 text-sm leading-6 text-amber-900">
                            <p class="font-semibold text-amber-700">Base clinica</p>
                            <p class="mt-1">{{ \Illuminate\Support\Str::limit(optional($historia)->diagnostico ?: optional($historia)->observaciones ?: 'Sin diagnóstico asociado.', 120) }}</p>
                        </div>
                        @if(optional($historia?->servicioProducto)->nombre)
                            <div class="mt-3 rounded-2xl bg-emerald-50 px-3 py-3 text-sm leading-6 text-emerald-900">
                                <p class="font-semibold text-emerald-700">Servicio asociado</p>
                                <p class="mt-1">{{ $historia->servicioProducto->nombre }}</p>
                            </div>
                        @endif
                        <div class="mt-3 grid grid-cols-2 gap-2 xl:grid-cols-4">
                            <button type="button" onclick='openEditRecetaModal(@json($recetaPayload))' class="w-full rounded-2xl border border-blue-200 bg-white px-3 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">Editar receta</button>
                            <a href="{{ route('historias-clinicas.index', ['mascota_id' => optional($mascota)->id]) }}" class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50">Historial clínico</a>
                            <button type="button" onclick='printRecetaCard(@json($printPayload))' class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2.5 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-50">Imprimir</button>
                            <form method="POST" action="{{ route('recetas.destroy', $receta) }}" onsubmit="return confirm('&iquest;Eliminar esta receta?');">@csrf @method('DELETE')<button type="submit" class="w-full rounded-2xl border border-rose-200 bg-white px-3 py-2.5 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-50">Eliminar</button></form>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@else
    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm"><h3 class="text-xl font-semibold text-slate-900">Todavía no hay recetas registradas</h3><p class="mt-2 text-base text-slate-500">Puedes registrar la primera receta desde aquí o dejar que se cree desde la atención clínica.</p><button type="button" onclick="openRecetaModal('{{ $prefillHistoriaId ?? '' }}')" class="mt-5 rounded-2xl bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">Registrar receta</button></div>
@endif
                    </div>
                    <div class="border-t border-slate-100 bg-white px-6 py-4"><div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"><div class="text-base text-slate-500">Mostrando {{ $recetas->firstItem() ?? 0 }} a {{ $recetas->lastItem() ?? 0 }} de {{ $recetas->total() }} recetas</div><div class="flex justify-center md:justify-end">{{ $recetas->links('pagination::tailwind') }}</div></div></div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('recetas.modals.form', ['historiaCatalogo' => $historiaCatalogo, 'prefillHistoriaId' => $prefillHistoriaId, 'shouldOpenCreate' => $shouldOpenCreate])
<script src="{{ asset('js/modules/recetas.js') }}?v={{ filemtime(public_path('js/modules/recetas.js')) }}"></script>
</x-app-layout>



