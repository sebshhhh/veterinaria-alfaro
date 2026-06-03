@php
    $citaMascotas = $citaMascotas ?? collect();
    $citaMascotasRecientes = $citaMascotasRecientes ?? collect();
    $clientes = $clientes ?? collect();
    $veterinarios = $veterinarios ?? collect();
    $prefillMascotaId = $prefillMascotaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $selectedMascota = $selectedMascota ?? null;
    $accion = $accion ?? null;
    $hasCitaErrors = $errors->citaStore->any();
    $isEditCita = old('_method') === 'PUT' && old('editing_id');
    $oldEstadoCita = old('estado', 'pendiente');
@endphp

<div id="citaModal"
     data-open-on-load="{{ ($hasCitaErrors || $shouldOpenCreate) ? 'true' : 'false' }}"
     data-prefill-mascota="{{ old('mascota_id', $prefillMascotaId) }}"
     data-action-context="{{ $accion }}"
     class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6"
     aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="workspace-modal-surface w-full max-w-2xl rounded-[28px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Modulo de citas</p>
                        <h3 id="citaModalTitle" class="text-2xl font-bold text-slate-900">Agendar cita</h3>
                        <p id="citaMascotaSummary" class="mt-1 text-sm text-slate-500">
                            @if($selectedMascota && $accion === 'control')
                                El sistema preparó esta cita para programar el próximo control de {{ $selectedMascota->nombre }}.
                            @elseif($selectedMascota)
                                El sistema ya dejó seleccionada a {{ $selectedMascota->nombre }} para que sigas su agenda más rápido.
                            @else
                                Selecciona la mascota y completa la fecha de la cita.
                            @endif
                        </p>
                    </div>

                    <button type="button"
                            onclick="closeCitaModal()"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700"
                            aria-label="Cerrar modal">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="citaForm"
                  method="POST"
                  action="{{ route('citas.store') }}"
                  data-store-action="{{ route('citas.store') }}"
                  data-update-template="{{ url('citas/__ID__') }}"
                  data-default-time="09:00"
                  data-default-status="pendiente"
                  data-today="{{ now()->format('Y-m-d') }}"
                  class="space-y-5 px-6 py-6">
                @csrf
                @if($isEditCita)
                    @method('PUT')
                @endif
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">

                @if($hasCitaErrors)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach($errors->citaStore->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="cita_mascota_search" class="mb-2 block text-sm font-semibold text-slate-600">Paciente de la cita</label>

                        <div class="rounded-[24px] border border-blue-100 bg-gradient-to-br from-blue-50/80 via-white to-white p-4 shadow-sm">
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                </div>
                                <input id="cita_mascota_search"
                                       type="text"
                                       autocomplete="off"
                                       class="w-full rounded-2xl border border-slate-200 bg-white py-3.5 pl-12 pr-4 text-base text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                       placeholder="Buscar por mascota, propietario o DNI">
                                <div id="citaMascotaResults" class="absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                                    <div id="citaMascotaResultsList" class="max-h-72 overflow-y-auto p-2"></div>
                                </div>
                            </div>

                            <div id="citaMascotaSelected" class="mt-3 hidden items-center justify-between gap-3 rounded-2xl border border-blue-200 bg-white px-4 py-3 shadow-sm">
                                <div class="flex min-w-0 items-center gap-3">
                                    <img id="citaMascotaSelectedPhoto"
                                         src="{{ asset('storage/default.png') }}"
                                         alt="Mascota seleccionada"
                                         class="h-12 w-12 shrink-0 rounded-2xl object-cover"
                                         onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Paciente seleccionado</p>
                                        <p id="citaMascotaSelectedName" class="mt-1 truncate text-sm font-bold text-slate-900">--</p>
                                        <p id="citaMascotaSelectedMeta" class="truncate text-xs text-slate-500">--</p>
                                    </div>
                                </div>
                                <button type="button"
                                        id="citaMascotaClear"
                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-blue-200 bg-blue-50 text-blue-700 transition hover:border-blue-300 hover:bg-blue-100"
                                        aria-label="Quitar paciente">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                                    </svg>
                                </button>
                            </div>

                            <select id="cita_mascota_id" name="mascota_id" class="sr-only">
                                <option value="">Selecciona una mascota</option>
                                @foreach($citaMascotas as $mascotaOption)
                                    <option value="{{ $mascotaOption->id }}"
                                            data-label="{{ $mascotaOption->nombre }}"
                                            data-owner="{{ optional($mascotaOption->cliente)->nombre }}"
                                            data-dni="{{ optional($mascotaOption->cliente)->dni }}"
                                            data-phone="{{ optional($mascotaOption->cliente)->telefono }}"
                                            data-type="{{ $mascotaOption->tipo_animal }}"
                                            data-breed="{{ $mascotaOption->raza }}"
                                            data-color="{{ $mascotaOption->color }}"
                                            data-photo="{{ $mascotaOption->foto ? asset('storage/' . $mascotaOption->foto) : asset('storage/default.png') }}"
                                            @selected(old('mascota_id') == $mascotaOption->id)>
                                        {{ $mascotaOption->nombre }} - {{ optional($mascotaOption->cliente)->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="mt-2 text-xs leading-5 text-slate-500">Escribe unas letras y el sistema filtra por mascota, propietario o DNI para agendar sin perder tiempo.</p>

                            @if($citaMascotasRecientes->isNotEmpty())
                                <div class="mt-4">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Pacientes recientes</p>
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-600">Acceso rápido</span>
                                    </div>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        @foreach($citaMascotasRecientes as $mascotaReciente)
                                            <button type="button"
                                                    onclick="selectCitaMascotaForAppointment('{{ $mascotaReciente->id }}')"
                                                    class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-left transition hover:border-blue-200 hover:bg-blue-50">
                                                <img src="{{ $mascotaReciente->foto ? asset('storage/' . $mascotaReciente->foto) : asset('storage/default.png') }}"
                                                     alt="Foto de {{ $mascotaReciente->nombre }}"
                                                     class="h-10 w-10 shrink-0 rounded-xl object-cover"
                                                     onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';">
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-semibold text-slate-800">{{ $mascotaReciente->nombre }}</span>
                                                    <span class="block truncate text-xs text-slate-500">{{ optional($mascotaReciente->cliente)->nombre ?: 'Sin propietario' }}</span>
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 flex flex-wrap gap-2">
                                <button type="button" onclick="openCitaClienteModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                    </svg>
                                    Nuevo cliente
                                </button>
                                <button type="button" onclick="openCitaMascotaModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                    </svg>
                                    Nueva mascota
                                </button>
                            </div>
                            <p class="mt-2 text-xs leading-5 text-slate-500">Si el paciente aún no existe y solo llama o llega para agendar, regístralo aquí sin salir del flujo de citas.</p>
                        </div>
                    </div>

                    @if($veterinarios->count())
                        <div class="md:col-span-2">
                            <label for="cita_veterinario_id" class="mb-2 block text-sm font-semibold text-slate-600">Profesional responsable</label>
                            <select id="cita_veterinario_id" name="veterinario_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                @foreach($veterinarios as $veterinario)
                                    <option value="{{ $veterinario->id }}" @selected(old('veterinario_id') == $veterinario->id)>
                                        {{ $veterinario->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="veterinario_id" value="{{ old('veterinario_id') }}">
                        <div class="md:col-span-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            No hay veterinarios registrados todavía. La primera cita usará el usuario actual como profesional inicial para desbloquear el flujo de agendamiento.
                        </div>
                    @endif

                    <div>
                        <label for="cita_fecha" class="mb-2 block text-sm font-semibold text-slate-600">Fecha</label>
                        <input id="cita_fecha" type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="cita_hora" class="mb-2 block text-sm font-semibold text-slate-600">Hora</label>
                        <input id="cita_hora" type="time" name="hora" value="{{ old('hora', '09:00') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div class="md:col-span-2">
                        <label for="cita_estado" class="mb-2 block text-sm font-semibold text-slate-600">Estado</label>
                        <select id="cita_estado" name="estado" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="pendiente" @selected($oldEstadoCita === 'pendiente')>Pendiente</option>
                            <option value="cancelada" @selected($oldEstadoCita === 'cancelada')>Cancelada</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="closeCitaModal()"
                            class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75v2.5M15.75 3.75v2.5M4.5 8.25h15M5.25 5.75h13.5A1.75 1.75 0 0 1 20.5 7.5v10.75A1.75 1.75 0 0 1 18.75 20H5.25A1.75 1.75 0 0 1 3.5 18.25V7.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                        </svg>
                        <span id="citaSubmitLabel">{{ $isEditCita ? 'Actualizar cita' : 'Guardar cita' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($hasCitaErrors)
    <script>
        window.citaModalState = {
            hasErrors: true,
            isEdit: @json($isEditCita),
            editingId: @json(old('editing_id')),
        };
    </script>
@endif

