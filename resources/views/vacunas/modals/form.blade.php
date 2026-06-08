@php
    $vacunaMascotas = $vacunaMascotas ?? collect();
    $vacunaCatalogo = $vacunaCatalogo ?? collect();
    $prefillMascotaId = $prefillMascotaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $vacunaMascotasRecientes = $vacunaMascotasRecientes ?? collect();
    $hasVacunaErrors = $errors->vacunaStore->any();
    $isEditVacuna = old('_method') === 'PUT' && old('editing_id');
    $estadoAplicacion = old('estado_aplicacion', 'aplicada');
    $initialFlow = old('flow_mode', $isEditVacuna ? ($estadoAplicacion === 'programada' ? 'edit_scheduled' : 'edit_applied') : ($estadoAplicacion === 'programada' ? 'create_scheduled' : 'create_applied'));
    $selectedVacunaValue = old('vacuna_nombre_select');
    $customVacunaValue = old('vacuna_nombre_custom');

    if (!$selectedVacunaValue && !$customVacunaValue && old('nombre')) {
        if ($vacunaCatalogo->contains(old('nombre'))) {
            $selectedVacunaValue = old('nombre');
        } else {
            $selectedVacunaValue = '__custom__';
            $customVacunaValue = old('nombre');
        }
    }
@endphp

<div id="vacunaModal"
     data-open-on-load="{{ ($hasVacunaErrors || $shouldOpenCreate) ? 'true' : 'false' }}"
     data-prefill-mascota="{{ old('mascota_id', $prefillMascotaId) }}"
     class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6"
     aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-3xl rounded-[28px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Vacunación preventiva</p>
                        <h3 id="vacunaModalTitle" class="text-2xl font-bold text-slate-900">Registrar vacunacion</h3>
                        <p id="vacunaMascotaSummary" class="mt-1 text-sm text-slate-500">Selecciona la mascota y decide si la vacuna ya fue aplicada o si quedará pendiente para aplicarse después.</p>
                    </div>

                    <button type="button"
                            onclick="closeVacunaModal()"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700"
                            aria-label="Cerrar modal">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="vacunaForm"
                  method="POST"
                  action="{{ route('vacunas.store') }}"
                  data-store-action="{{ route('vacunas.store') }}"
                  data-update-template="{{ url('vacunas/__ID__') }}"
                  data-today="{{ now()->format('Y-m-d') }}"
                  data-initial-flow="{{ $initialFlow }}"
                  class="space-y-5 px-6 py-6">
                @csrf
                @if($isEditVacuna)
                    @method('PUT')
                @endif
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">
                <input type="hidden" name="flow_mode" value="{{ $initialFlow }}">

                @if($hasVacunaErrors)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach($errors->vacunaStore->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.95fr)]">
                    <div class="space-y-5">
                        <div id="vacuna_state_selector_wrap">
                            <label class="mb-2 block text-sm font-semibold text-slate-600">Como quieres registrar este control</label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="flex items-start gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-slate-700">
                                    <input type="radio" name="estado_aplicacion" value="aplicada" class="mt-1" @checked($estadoAplicacion === 'aplicada')>
                                    <span>
                                        <span class="block font-semibold text-slate-900">Vacuna ya aplicada</span>
                                        <span class="mt-1 block text-xs leading-5 text-slate-500">Guarda la dosis aplicada y deja el historial preventivo listo.</span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-slate-700">
                                    <input type="radio" name="estado_aplicacion" value="programada" class="mt-1" @checked($estadoAplicacion === 'programada')>
                                    <span>
                                        <span class="block font-semibold text-slate-900">Vacuna solo programada</span>
                                        <span class="mt-1 block text-xs leading-5 text-slate-500">Sirve para dejar una vacuna pendiente y luego marcarla como aplicada desde este mismo modulo.</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="vacuna_mascota_search" class="mb-2 block text-sm font-semibold text-slate-600">Paciente a vacunar</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                </div>
                                <input id="vacuna_mascota_search"
                                       type="text"
                                       autocomplete="off"
                                       class="w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                       placeholder="Busca por mascota, propietario o DNI">
                                <div id="vacunaMascotaResults" class="absolute left-0 right-0 z-20 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                                    <div id="vacunaMascotaResultsList" class="max-h-72 overflow-y-auto p-2"></div>
                                </div>
                            </div>

                            <div id="vacunaSelectedMascota" class="mt-3 hidden items-center justify-between gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">Paciente seleccionado</p>
                                    <p id="vacunaSelectedMascotaName" class="mt-1 truncate text-sm font-semibold text-slate-900">--</p>
                                    <p id="vacunaSelectedMascotaMeta" class="truncate text-xs text-slate-500">--</p>
                                </div>
                                <button type="button" id="vacunaClearMascota" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-blue-200 bg-white text-blue-700 transition hover:border-blue-300 hover:bg-blue-100" aria-label="Quitar paciente">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                                    </svg>
                                </button>
                            </div>

                            <select id="vacuna_mascota_id" name="mascota_id" class="sr-only">
                                <option value="">Selecciona una mascota</option>
                                @foreach($vacunaMascotas as $mascotaOption)
                                    @php
                                        $fotoOption = $mascotaOption->foto ? \App\Support\PhotoUrl::make($mascotaOption->foto) : \App\Support\PhotoUrl::make(null);
                                    @endphp
                                    <option value="{{ $mascotaOption->id }}"
                                            data-cliente="{{ optional($mascotaOption->cliente)->nombre }}"
                                            data-dni="{{ optional($mascotaOption->cliente)->dni }}"
                                            data-tipo="{{ $mascotaOption->tipo_animal }}"
                                            data-foto="{{ $fotoOption }}"
                                            data-label="{{ $mascotaOption->nombre }}"
                                            @selected(old('mascota_id', $prefillMascotaId) == $mascotaOption->id)>
                                        {{ $mascotaOption->nombre }} - {{ optional($mascotaOption->cliente)->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs leading-5 text-slate-500">Escribe el nombre de la mascota, del propietario o el DNI para encontrar rápido al paciente.</p>
                            @if($vacunaMascotasRecientes->isNotEmpty())
                                <div class="mt-3">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Pacientes recientes</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($vacunaMascotasRecientes as $mascotaReciente)
                                            <button type="button"
                                                    onclick="selectVacunaMascota('{{ $mascotaReciente->id }}')"
                                                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                                <span class="truncate">{{ $mascotaReciente->nombre }}</span>
                                                <span class="text-slate-400">-</span>
                                                <span class="truncate">{{ optional($mascotaReciente->cliente)->nombre }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label id="vacuna_nombre_label" for="vacuna_nombre_select" class="mb-2 block text-sm font-semibold text-slate-600">Que vacuna se aplicó</label>
                            <select id="vacuna_nombre_select" name="vacuna_nombre_select" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="">Selecciona una vacuna</option>
                                @foreach($vacunaCatalogo as $vacunaNombre)
                                    <option value="{{ $vacunaNombre }}" @selected($selectedVacunaValue === $vacunaNombre)>{{ $vacunaNombre }}</option>
                                @endforeach
                                <option value="__custom__" @selected($selectedVacunaValue === '__custom__')>Otra vacuna</option>
                            </select>
                        </div>

                        <div id="vacuna_nombre_custom_wrap" class="{{ $selectedVacunaValue === '__custom__' ? '' : 'hidden' }}">
                            <label for="vacuna_nombre_custom" class="mb-2 block text-sm font-semibold text-slate-600">Escribe el nombre de la vacuna</label>
                            <input id="vacuna_nombre_custom" type="text" name="vacuna_nombre_custom" value="{{ $customVacunaValue }}" placeholder="Escribe el nombre de la vacuna" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div id="vacuna_fecha_aplicacion_wrap">
                                <label id="vacuna_fecha_aplicacion_label" for="vacuna_fecha_aplicacion" class="mb-2 block text-sm font-semibold text-slate-600">Cuando se aplicó</label>
                                <input id="vacuna_fecha_aplicacion" type="date" name="fecha_aplicacion" value="{{ old('fecha_aplicacion', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            </div>
                            <div id="vacuna_fecha_programada_wrap" class="{{ $estadoAplicacion === 'programada' ? '' : 'hidden' }}">
                                <label id="vacuna_fecha_programada_label" for="vacuna_fecha_programada" class="mb-2 block text-sm font-semibold text-slate-600">Cuando quedó programada</label>
                                <input id="vacuna_fecha_programada" type="date" name="fecha_programada" value="{{ old('fecha_programada', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            </div>
                            <div id="vacuna_proxima_dosis_wrap" class="{{ $estadoAplicacion === 'aplicada' ? '' : 'hidden' }}">
                                <label id="vacuna_proxima_dosis_label" for="vacuna_proxima_dosis" class="mb-2 block text-sm font-semibold text-slate-600">Programar siguiente vacuna</label>
                                <input id="vacuna_proxima_dosis" type="date" name="proxima_dosis" value="{{ old('proxima_dosis') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <p id="vacuna_proxima_dosis_help" class="mt-2 text-xs leading-5 text-slate-500">Si la llenas, el sistema dejará la siguiente dosis como pendiente en este mismo modulo.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-5 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Resumen visual</p>

                        <div class="mt-4 rounded-[22px] bg-white p-4 shadow-sm">
                            <img id="vacunaMascotaPhoto" src="{{ \App\Support\PhotoUrl::make(null) }}" alt="Vista de mascota" class="h-48 w-full rounded-[18px] object-cover" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">

                            <div class="mt-4">
                                <p id="vacunaMascotaName" class="text-xl font-bold text-slate-900">Selecciona una mascota</p>
                                <p id="vacunaMascotaOwner" class="mt-1 text-sm text-slate-500">El propietario aparecerá aquí.</p>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span id="vacunaMascotaType" class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Control preventivo</span>
                                <span id="vacunaEstadoBadge" class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">Vacuna aplicada</span>
                            </div>
                        </div>

                        <div id="vacunaHelperCard" class="mt-4 rounded-[22px] border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                            Usa esta pantalla cuando el paciente viene solo por su vacuna o cuando necesitas dejar una próxima dosis pendiente para después marcarla como aplicada.
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="closeVacunaModal()"
                            class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 8.25 18.75l-3.75-3.75" />
                        </svg>
                        <span id="vacunaSubmitLabel">Guardar vacunacion</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($hasVacunaErrors)
    <script>
        window.vacunaModalState = {
            hasErrors: true,
            isEdit: @json($isEditVacuna),
            editingId: @json(old('editing_id')),
        };
    </script>
@endif

