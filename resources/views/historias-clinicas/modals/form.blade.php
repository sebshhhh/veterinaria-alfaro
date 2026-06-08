@php
    $historiaMascotas = $historiaMascotas ?? collect();
    $prefillMascotaId = $prefillMascotaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $hasHistoriaErrors = $errors->historiaStore->any();
    $isEditHistoria = old('_method') === 'PUT' && old('editing_id');
@endphp

<div id="historiaModal"
     data-open-on-load="{{ ($hasHistoriaErrors || $shouldOpenCreate) ? 'true' : 'false' }}"
     data-prefill-mascota="{{ old('mascota_id', $prefillMascotaId) }}"
     class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6"
     aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="workspace-modal-surface w-full max-w-3xl rounded-[28px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Historial clínico del paciente</p>
                        <h3 id="historiaModalTitle" class="text-2xl font-bold text-slate-900">Registrar evento clínico</h3>
                        <p id="historiaMascotaSummary" class="mt-1 text-sm text-slate-500">Selecciona la mascota y registra solo el detalle médico de esta atención.</p>
                    </div>

                    <button type="button"
                            onclick="closeHistoriaModal()"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700"
                            aria-label="Cerrar modal">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="historiaForm"
                  method="POST"
                  action="{{ route('historias-clinicas.store') }}"
                  data-store-action="{{ route('historias-clinicas.store') }}"
                  data-update-template="{{ url('historias-clinicas/__ID__') }}"
                  data-today="{{ now()->format('Y-m-d') }}"
                  class="space-y-5 px-6 py-6">
                @csrf
                @if($isEditHistoria)
                    @method('PUT')
                @endif
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">

                @if($hasHistoriaErrors)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach($errors->historiaStore->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(280px,0.9fr)]">
                    <div class="space-y-5">
                        <div>
                            <label for="historia_mascota_search" class="mb-2 block text-sm font-semibold text-slate-600">Paciente del historial</label>
                            <div class="mb-3 flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition focus-within:border-blue-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-100">
                                <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                </svg>
                                <input id="historia_mascota_search" type="text" autocomplete="off" placeholder="Buscar por mascota o propietario..." class="ml-3 w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0">
                            </div>
                            <select id="historia_mascota_id" name="mascota_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="">Selecciona una mascota</option>
                                @foreach($historiaMascotas as $mascotaOption)
                                    @php
                                        $fotoOption = $mascotaOption->foto ? \App\Support\PhotoUrl::make($mascotaOption->foto) : \App\Support\PhotoUrl::make(null);
                                    @endphp
                                    <option value="{{ $mascotaOption->id }}"
                                            data-cliente="{{ optional($mascotaOption->cliente)->nombre }}"
                                            data-tipo="{{ $mascotaOption->tipo_animal }}"
                                            data-foto="{{ $fotoOption }}"
                                            @selected(old('mascota_id', $prefillMascotaId) == $mascotaOption->id)>
                                        {{ $mascotaOption->nombre }} - {{ optional($mascotaOption->cliente)->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs leading-5 text-slate-500">Escribe el nombre del paciente o propietario para encontrarlo rápido.</p>
                        </div>

                        <div>
                            <label for="historia_fecha" class="mb-2 block text-sm font-semibold text-slate-600">Fecha del registro</label>
                            <input id="historia_fecha" type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="historia_peso" class="mb-2 block text-sm font-semibold text-slate-600">Peso</label>
                                <div class="flex rounded-2xl border border-slate-200 bg-white shadow-sm focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100">
                                    <input id="historia_peso" type="number" name="peso" min="0" max="999.99" step="0.01" value="{{ old('peso') }}" class="min-w-0 flex-1 rounded-l-2xl border-0 px-4 py-3 text-sm text-slate-700 focus:ring-0" placeholder="Ej. 12.50">
                                    <span class="inline-flex items-center rounded-r-2xl border-l border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-500">kg</span>
                                </div>
                            </div>

                            <div>
                                <label for="historia_temperatura" class="mb-2 block text-sm font-semibold text-slate-600">Temperatura</label>
                                <div class="flex rounded-2xl border border-slate-200 bg-white shadow-sm focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100">
                                    <input id="historia_temperatura" type="number" name="temperatura" min="30" max="45" step="0.1" value="{{ old('temperatura') }}" class="min-w-0 flex-1 rounded-l-2xl border-0 px-4 py-3 text-sm text-slate-700 focus:ring-0" placeholder="Ej. 38.5">
                                    <span class="inline-flex items-center rounded-r-2xl border-l border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-500">&deg;C</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="historia_diagnostico" class="mb-2 block text-sm font-semibold text-slate-600">Diagnostico</label>
                            <textarea id="historia_diagnostico" name="diagnostico" rows="5" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Ej. Dermatitis alérgica, control postoperatorio, evaluación general...">{{ old('diagnostico') }}</textarea>
                        </div>

                        <div>
                            <label for="historia_observaciones" class="mb-2 block text-sm font-semibold text-slate-600">Observaciones</label>
                            <textarea id="historia_observaciones" name="observaciones" rows="5" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Describe hallazgos, indicaciones o detalles relevantes del caso...">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-5 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Resumen visual</p>

                        <div class="mt-4 rounded-[22px] bg-white p-4 shadow-sm">
                            <img id="historiaMascotaPhoto" src="{{ \App\Support\PhotoUrl::make(null) }}" alt="Vista de mascota" class="h-48 w-full rounded-[18px] object-cover" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">

                            <div class="mt-4">
                                <p id="historiaMascotaName" class="text-xl font-bold text-slate-900">Selecciona una mascota</p>
                                <p id="historiaMascotaOwner" class="mt-1 text-sm text-slate-500">El due&ntilde;o aparecerá aquí.</p>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span id="historiaMascotaType" class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Paciente pendiente</span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Evento del historial</span>
                            </div>
                        </div>

                        <div class="mt-4 rounded-[22px] border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800">
                            Este registro no crea una ficha nueva. Solo agrega un evento a la línea de tiempo clínica del paciente.
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="closeHistoriaModal()"
                            class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6.75a2.25 2.25 0 0 1 2.25 2.25v12A2.25 2.25 0 0 1 14.25 20.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6M9 12h6M9 15.75h3.75" />
                        </svg>
                        <span id="historiaSubmitLabel">{{ $isEditHistoria ? 'Actualizar evento' : 'Guardar evento' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($hasHistoriaErrors)
    <script>
        window.historiaModalState = {
            hasErrors: true,
            isEdit: @json($isEditHistoria),
            editingId: @json(old('editing_id')),
        };
    </script>
@endif

