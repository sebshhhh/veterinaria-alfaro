<x-app-layout>
@php
    $stats = $stats ?? [];
    $speciesCounts = $speciesCounts ?? collect();
    $proximasCitas = $proximasCitas ?? collect();
    $ultimasVisitas = $ultimasVisitas ?? collect();
    $ordenActual = $orden ?? request('orden', 'recientes');
    $baseFilters = array_filter([
        'search' => request('search'),
        'orden' => $ordenActual,
    ], fn ($value) => filled($value));
    $petColorPalette = [
        'negro' => '#111827',
        'blanco' => '#e5e7eb',
        'gris' => '#6b7280',
        'marron' => '#8b5e3c',
        'cafe' => '#8b5e3c',
        'caramelo' => '#d97706',
        'dorado' => '#f59e0b',
        'amarillo' => '#eab308',
        'crema' => '#f3e8c8',
        'naranja' => '#f97316',
        'tricolor' => '#7c3aed',
        'manchado' => '#0f766e',
        'atigrado' => '#b45309',
    ];
@endphp

<div class="module-page">
    <div class="module-page__inner">
        <section class="module-shell">
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 shadow-inner shadow-blue-100/80">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 5.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM17.25 8.25a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM8.25 9.75a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM20.25 13.5a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM14.856 20.523c-.64.473-1.664.727-2.856.727-1.192 0-2.216-.254-2.856-.727-.657-.486-1.03-1.159-1.03-1.898 0-.82.456-1.578 1.258-2.122.802-.545 1.92-.878 3.128-.878 1.208 0 2.326.333 3.128.878.802.544 1.258 1.302 1.258 2.122 0 .739-.373 1.412-1.03 1.898Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-semibold uppercase tracking-[0.22em] text-blue-600">Pacientes registrados</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Mascotas</h1>
                            <p class="mt-1 text-base text-slate-500">Consulta fichas, organiza pacientes y registra nuevas mascotas con su dueño correspondiente.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">
                            <span class="font-semibold text-slate-700">{{ $stats['total'] ?? $mascotas->total() }}</span>
                            mascotas registradas
                        </div>
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">
                            <span class="font-semibold">{{ $stats['activas'] ?? 0 }}</span>
                            con seguimiento activo
                        </div>
                        <button type="button" onclick="openCreateModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Nuevo cliente
                        </button>
                        <button type="button" onclick="openMascotaCreateModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Nueva mascota
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 sm:px-8">
                <div class="space-y-5">
                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <span class="font-semibold">{{ $stats['especies'] ?? 0 }}</span>
                            especies registradas
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            <span class="font-semibold">{{ $stats['citas_hoy'] ?? 0 }}</span>
                            citas para hoy
                        </div>
                    </div>

                    <form method="GET" action="{{ route('mascotas.index') }}" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                        <input type="hidden" name="orden" value="{{ $ordenActual }}">
                        <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(240px,1fr)_auto] xl:items-end">
                            <div>
                                <label for="search" class="mb-2 block text-base font-semibold text-slate-600">Buscar mascota o due&ntilde;o</label>
                                <div class="flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm transition focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100">
                                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                    <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre de mascota o DNI del due&ntilde;o..." class="ml-3 w-full border-0 p-0 text-base text-slate-700 placeholder:text-slate-400 focus:ring-0">
                                </div>
                            </div>

                            <div>
                                <label for="especie" class="mb-2 block text-base font-semibold text-slate-600">Especie</label>
                                <select id="especie" name="especie" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-base text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                    <option value="">Todas las especies</option>
                                    @foreach($speciesCounts->keys()->sort() as $species)
                                        <option value="{{ $species }}" @selected(request('especie') === $species)>{{ $species }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-2.5 text-base font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                    Buscar
                                </button>
                                <a href="{{ route('mascotas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-2.5 text-center text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356m-1.636 1.635a9 9 0 1 0 2.339 9.34" />
                                    </svg>
                                    Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col">
                            <div class="border-b border-slate-100 px-6 py-4">
                                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('mascotas.index', $baseFilters) }}" class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2 text-base font-semibold transition {{ request('especie') ? 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                                            <span class="h-2.5 w-2.5 rounded-full {{ request('especie') ? 'bg-slate-300' : 'bg-blue-500' }}"></span>
                                            Todas ({{ $stats['total'] ?? $mascotas->total() }})
                                        </a>
                                        @foreach($speciesCounts as $species => $total)
                                            <a href="{{ route('mascotas.index', array_merge($baseFilters, ['especie' => $species])) }}" class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2 text-base font-semibold transition {{ request('especie') === $species ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                                                <span class="h-2.5 w-2.5 rounded-full {{ request('especie') === $species ? 'bg-blue-500' : 'bg-slate-300' }}"></span>
                                                {{ $species }} ({{ $total }})
                                            </a>
                                        @endforeach
                                    </div>

                                    <form method="GET" action="{{ route('mascotas.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        @if(filled(request('search')))
                                            <input type="hidden" name="search" value="{{ request('search') }}">
                                        @endif
                                        @if(filled(request('especie')))
                                            <input type="hidden" name="especie" value="{{ request('especie') }}">
                                        @endif

                                        <div class="flex items-center gap-3 text-base text-slate-500">
                                            <span class="font-medium text-slate-500">Ordenar por:</span>
                                            <select name="orden" onchange="this.form.submit()" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-base font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                                <option value="recientes" @selected($ordenActual === 'recientes')>Mas recientes</option>
                                                <option value="nombre_asc" @selected($ordenActual === 'nombre_asc')>Nombre A - Z</option>
                                                <option value="nombre_desc" @selected($ordenActual === 'nombre_desc')>Nombre Z - A</option>
                                                <option value="edad_asc" @selected($ordenActual === 'edad_asc')>Edad menor a mayor</option>
                                                <option value="edad_desc" @selected($ordenActual === 'edad_desc')>Edad mayor a menor</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="px-6 py-6">
                                @if($mascotas->count())
                                    <div class="grid gap-4 xl:grid-cols-3">
                                        @foreach($mascotas as $mascota)
                                            @php
                                                $proximaCita = $proximasCitas[$mascota->id] ?? null;
                                                $ultimaVisita = $ultimasVisitas[$mascota->id] ?? null;
                                                $proximaCitaTexto = $proximaCita ? \Illuminate\Support\Carbon::parse($proximaCita)->format('d/m/Y') : 'Sin cita';
                                                $ultimaVisitaTexto = $ultimaVisita ? \Illuminate\Support\Carbon::parse($ultimaVisita)->format('d/m/Y') : '--/--/----';
                                                $sexoEsMacho = $mascota->sexo === 'Macho';
                                                $foto = $mascota->foto ? asset('storage/' . $mascota->foto) : asset('storage/default.png');
                                                $normalizedColor = \Illuminate\Support\Str::of($mascota->color ?? '')->ascii()->lower()->value();
                                                $petColorHex = '#94a3b8';
                                                foreach ($petColorPalette as $keyword => $hex) {
                                                    if (str_contains($normalizedColor, $keyword)) {
                                                        $petColorHex = $hex;
                                                        break;
                                                    }
                                                }
                                            @endphp

                                            <article class="group rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/70">
                                                <div class="flex gap-4">
                                                    <div class="relative w-32 shrink-0">
                                                        <img src="{{ $foto }}" alt="Foto de {{ $mascota->nombre }}" class="h-28 w-full rounded-[18px] object-cover" onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';">
                                                        <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 shadow-sm">
                                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                            Activo
                                                        </span>
                                                    </div>

                                                    <div class="min-w-0 flex-1 flex flex-col">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <div class="flex items-center gap-2">
                                                                    <h2 class="truncate text-lg font-bold leading-snug text-slate-900">{{ $mascota->nombre }}</h2>
                                                                    <span class="text-sm {{ $sexoEsMacho ? 'text-blue-500' : 'text-pink-500' }}">{!! $sexoEsMacho ? '&male;' : '&female;' !!}</span>
                                                                </div>
                                                                <p class="mt-0.5 truncate text-sm font-medium text-slate-500">{{ $mascota->raza ?: ($mascota->tipo_animal ?: 'Sin raza registrada') }}</p>
                                                            </div>
                                                            <span class="inline-flex shrink-0 items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                                                {{ $mascota->tipo_animal ?: 'Especie sin dato' }}
                                                            </span>
                                                        </div>

                                                        <div class="mt-2 space-y-1 text-sm text-slate-600">
                                                            <p><span class="font-medium text-slate-400">Edad:</span> {{ $mascota->edad }} {!! $mascota->edad == 1 ? 'a&ntilde;o' : 'a&ntilde;os' !!}</p>
                                                            <p class="truncate"><span class="font-medium text-slate-400">Due&ntilde;o:</span> {{ optional($mascota->cliente)->nombre ?: 'Sin cliente asignado' }}</p>
                                                            <p><span class="font-medium text-slate-400">Color:</span> {{ $mascota->color ?: 'Sin color registrado' }}</p>
                                                            <p><span class="font-medium text-slate-400">Ult. visita:</span> {{ $ultimaVisitaTexto }}</p>
                                                        </div>

                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            <div class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 border border-slate-200">
                                                                <span class="h-3 w-3 rounded-full border border-white shadow-sm" style="background-color: {{ $petColorHex }};"></span>
                                                                {{ $mascota->color ?: 'Color pendiente' }}
                                                            </div>
                                                            <div class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                                                                <svg class="h-3.5 w-3.5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 5.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM17.25 8.25a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM8.25 9.75a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM20.25 13.5a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM14.856 20.523c-.64.473-1.664.727-2.856.727-1.192 0-2.216-.254-2.856-.727-.657-.486-1.03-1.159-1.03-1.898 0-.82.456-1.578 1.258-2.122.802-.545 1.92-.878 3.128-.878 1.208 0 2.326.333 3.128.878.802.544 1.258 1.302 1.258 2.122 0 .739-.373 1.412-1.03 1.898Z" />
                                                                </svg>
                                                                {{ $mascota->tipo_animal ?: 'Mascota' }}
                                                            </div>
                                                            <div class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                                                                </svg>
                                                                Prox. cita: {{ $proximaCitaTexto }}
                                                            </div>
                                                        </div>

                                                        <div class="mt-3 grid grid-cols-3 gap-2">
                                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-center">
                                                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Atenciones</p>
                                                                <p class="mt-1 text-lg font-bold text-slate-900">{{ $mascota->historias_clinicas_count ?? 0 }}</p>
                                                            </div>
                                                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 px-3 py-2.5 text-center">
                                                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-600">Vacunas</p>
                                                                <p class="mt-1 text-lg font-bold text-emerald-700">{{ $mascota->vacunas_count ?? 0 }}</p>
                                                            </div>
                                                            <div class="rounded-2xl border border-amber-200 bg-amber-50/70 px-3 py-2.5 text-center">
                                                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-600">Pendientes</p>
                                                                <p class="mt-1 text-lg font-bold text-amber-700">{{ $mascota->citas_pendientes_count ?? 0 }}</p>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3 grid grid-cols-3 gap-2">
                                                            <button type="button" title="Ver ficha" onclick="openMascotaModal({{ $mascota->id }})" class="pet-action-button border-blue-200 bg-white text-blue-700 hover:border-blue-300 hover:bg-blue-50">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3.75h7.5L19.5 8.75v11.5A1.75 1.75 0 0 1 17.75 22H7.25A1.75 1.75 0 0 1 5.5 20.25V5.5A1.75 1.75 0 0 1 7.25 3.75Z" />
                                                                </svg>
                                                                Ver ficha
                                                            </button>

                                                            <button type="button" title="Agendar cita" onclick="openCitaModal('{{ $mascota->id }}')" class="pet-action-button border-emerald-200 bg-white text-emerald-700 hover:border-emerald-300 hover:bg-emerald-50">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                                                                </svg>
                                                                Cita
                                                            </button>

                                                            <a href="{{ route('mascotas.edit', $mascota) }}" class="pet-action-button border-amber-200 bg-white text-amber-700 hover:border-amber-300 hover:bg-amber-50">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.651-1.652a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM15 6.349 17.651 9" />
                                                                </svg>
                                                                Actualizar perfil
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center justify-center py-10">
                                        <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 5.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM17.25 8.25a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM8.25 9.75a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM20.25 13.5a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM14.856 20.523c-.64.473-1.664.727-2.856.727-1.192 0-2.216-.254-2.856-.727-.657-.486-1.03-1.159-1.03-1.898 0-.82.456-1.578 1.258-2.122.802-.545 1.92-.878 3.128-.878 1.208 0 2.326.333 3.128.878.802.544 1.258 1.302 1.258 2.122 0 .739-.373 1.412-1.03 1.898Z" />
                                                </svg>
                                            </div>
                                            <h3 class="mt-5 text-xl font-semibold text-slate-900">No encontramos mascotas con esos filtros</h3>
                                            <p class="mt-2 text-sm text-slate-500">Prueba ajustando la búsqueda o limpiando los filtros para volver a ver el listado completo.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="border-t border-slate-100 bg-white px-6 py-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div class="text-sm text-slate-500">
                                        Mostrando {{ $mascotas->firstItem() ?? 0 }} a {{ $mascotas->lastItem() ?? 0 }} de {{ $mascotas->total() }} mascotas
                                    </div>
                                    <div class="mascotas-pagination flex justify-center md:justify-end">
                                        {{ $mascotas->links('pagination::tailwind') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('mascotas.modals.show')
@include('citas.modals.create', ['citaMascotas' => $citaMascotas, 'veterinarios' => $veterinarios])
@include('clientes.modals.create', ['redirectTo' => 'mascotas.index'])
@include('clientes.modals.create-mascota', ['redirectTo' => 'mascotas.index', 'clientes' => $clientes, 'allowClienteSelection' => true])
<script src="{{ asset('js/modules/clientes.js') }}?v={{ filemtime(public_path('js/modules/clientes.js')) }}"></script>
<script src="{{ asset('js/modules/mascotas.js') }}?v={{ filemtime(public_path('js/modules/mascotas.js')) }}"></script>
@if(!empty($openFichaId))
    <script>
        (() => {
            const openFicha = () => window.openMascotaModal?.(@json($openFichaId));

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', openFicha, { once: true });
                return;
            }

            openFicha();
        })();
    </script>
@endif
<script src="{{ asset('js/modules/citas.js') }}?v={{ filemtime(public_path('js/modules/citas.js')) }}"></script>
</x-app-layout>




