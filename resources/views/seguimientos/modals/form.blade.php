@php
    $historiaCatalogo = $historiaCatalogo ?? collect();
    $veterinarios = $veterinarios ?? collect();
    $prefillHistoriaId = $prefillHistoriaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $defaultControlTime = $defaultControlTime ?? '09:00';
    $seguimientoErrors = isset($errors) ? $errors->seguimientoStore : new \Illuminate\Support\ViewErrorBag();
    $hasSeguimientoErrors = $seguimientoErrors->any();
    $isEditSeguimiento = old('_method') === 'PUT' && old('editing_id');
@endphp

<div id="seguimientoModal"
     data-open-on-load="{{ ($hasSeguimientoErrors || $shouldOpenCreate) ? 'true' : 'false' }}"
     class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6"
     aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="modal-card flex max-h-[calc(100vh-3rem)] w-full max-w-6xl scale-95 flex-col overflow-hidden rounded-[30px] border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 ease-out">
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5 sm:px-7">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-100">
                            <i data-feather="clipboard" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Control de retorno</p>
                            <h3 id="seguimientoModalTitle" class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Agregar control de retorno</h3>
                            <p id="seguimientoModalSummary" class="mt-1 text-sm leading-6 text-slate-500">Selecciona la atención de origen y programa la cita de retorno.</p>
                        </div>
                    </div>
                    <button type="button"
                            onclick="closeSeguimientoModal()"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="seguimientoForm"
                  method="POST"
                  action="{{ route('seguimientos.store') }}"
                  data-store-action="{{ route('seguimientos.store') }}"
                  data-update-template="{{ url('seguimientos/__ID__') }}"
                  data-today="{{ now()->format('Y-m-d') }}"
                  data-default-hour="{{ $defaultControlTime }}"
                  class="flex min-h-0 flex-1 flex-col">
                @csrf
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">
                <input type="hidden" name="origen" value="{{ old('origen', 'manual') }}">

                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6 sm:px-7">
                    <div class="space-y-5">
                        @if($hasSeguimientoErrors)
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                <p class="font-extrabold">Revisa estos campos:</p>
                                <div class="mt-2 space-y-1">
                                    @foreach($seguimientoErrors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-5 xl:grid-cols-[340px_minmax(0,1fr)]">
                            <aside class="rounded-[26px] border border-slate-200 bg-slate-50 p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Paciente seleccionado</p>
                                <div class="mt-4 rounded-[22px] bg-white p-4 shadow-sm">
                                    <p id="seguimientoMascotaName" class="text-xl font-extrabold text-slate-950">Paciente por seleccionar</p>
                                    <p id="seguimientoMascotaOwner" class="mt-1 text-sm text-slate-500">La atención relacionada cargará el paciente.</p>
                                </div>
                                <div class="mt-4 space-y-3">
                                    <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Atención base</p>
                                        <p id="seguimientoHistoriaDate" class="mt-1 text-sm font-bold text-blue-900">--/--/----</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Resumen clínico</p>
                                        <p id="seguimientoDiagnostico" class="mt-1 text-sm leading-6 text-slate-700">Selecciona una atención para ver el resumen.</p>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Responsable</p>
                                        <p id="seguimientoProfesional" class="mt-1 text-sm font-bold text-emerald-900">Se asigna al guardar</p>
                                    </div>
                                </div>
                            </aside>

                            <div class="space-y-5">
                                <section class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm font-extrabold text-blue-700">1</span>
                                        <div>
                                            <h4 class="text-base font-extrabold text-slate-950">Origen del control</h4>
                                            <p class="mt-1 text-sm text-slate-500">Elige la atención que originó este retorno.</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        <label class="space-y-2 lg:col-span-2">
                                            <span class="text-sm font-bold text-slate-700">Atención relacionada</span>
                                            <select id="seguimiento_historia_id" name="historia_clinica_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                                <option value="">Selecciona una atención</option>
                                                @foreach($historiaCatalogo as $historia)
                                                    <option value="{{ $historia->id }}"
                                                            data-pet="{{ optional($historia->mascota)->nombre }}"
                                                            data-owner="{{ optional(optional($historia->mascota)->cliente)->nombre }}"
                                                            data-date="{{ optional($historia->fecha)->format('Y-m-d') }}"
                                                            data-diagnosis="{{ $historia->diagnostico ?: $historia->observaciones }}"
                                                            @selected(old('historia_clinica_id', $prefillHistoriaId) == $historia->id)>
                                                        {{ optional($historia->mascota)->nombre }} | {{ optional(optional($historia->mascota)->cliente)->nombre }} | {{ optional($historia->fecha)->format('d/m/Y') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Profesional responsable</span>
                                            <select id="seguimiento_veterinario_id" name="veterinario_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                                <option value="">Asignar automáticamente</option>
                                                @foreach($veterinarios as $veterinario)
                                                    <option value="{{ $veterinario->id }}" data-name="{{ $veterinario->nombre }}" @selected(old('veterinario_id') == $veterinario->id)>{{ $veterinario->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Tipo de control</span>
                                            <select id="seguimiento_tipo" name="tipo" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                                <option value="clinico" @selected(old('tipo', 'clinico') === 'clinico')>Control clínico</option>
                                                <option value="preventivo" @selected(old('tipo') === 'preventivo')>Vacuna pendiente</option>
                                                <option value="terapeutico" @selected(old('tipo') === 'terapeutico')>Control de tratamiento</option>
                                            </select>
                                        </label>
                                    </div>
                                </section>

                                <section class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-sm font-extrabold text-amber-700">2</span>
                                        <div>
                                            <h4 class="text-base font-extrabold text-slate-950">Motivo y fecha de retorno</h4>
                                            <p class="mt-1 text-sm text-slate-500">Define qué se revisará y cuándo debe volver la mascota.</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid gap-4 lg:grid-cols-4">
                                        <label class="space-y-2 lg:col-span-2">
                                            <span class="text-sm font-bold text-slate-700">Título del control <span class="font-normal text-slate-400">(opcional)</span></span>
                                            <input id="seguimiento_titulo" type="text" name="titulo" value="{{ old('titulo') }}" placeholder="Ej. Control por diarrea, próxima vacuna..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Estado</span>
                                            <select id="seguimiento_estado" name="estado" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                                <option value="activo" @selected(old('estado', 'activo') === 'activo')>Activo</option>
                                                <option value="controlado" @selected(old('estado') === 'controlado')>Atendido</option>
                                                <option value="cerrado" @selected(old('estado') === 'cerrado')>Cerrado</option>
                                            </select>
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Fecha de inicio</span>
                                            <input id="seguimiento_fecha_inicio" type="date" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                        </label>
                                        <label class="space-y-2 lg:col-span-4">
                                            <span class="text-sm font-bold text-slate-700">Motivo del control</span>
                                            <textarea id="seguimiento_motivo" name="motivo" rows="3" placeholder="Ej. Revisar evolución de vómitos, control de herida, vacuna pendiente o respuesta al tratamiento..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('motivo') }}</textarea>
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Días para volver</span>
                                            <input id="seguimiento_dias_retorno" type="number" min="1" max="365" name="dias_retorno" value="{{ old('dias_retorno') }}" placeholder="Ej. 3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Fecha exacta</span>
                                            <input id="seguimiento_fecha_proximo_control" type="date" name="fecha_proximo_control" value="{{ old('fecha_proximo_control') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Hora</span>
                                            <input id="seguimiento_hora_proximo_control" type="time" name="hora_proximo_control" value="{{ old('hora_proximo_control', $defaultControlTime) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                        </label>
                                        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-800">
                                            Si ingresas días, el sistema calcula la fecha automáticamente.
                                        </div>
                                    </div>
                                </section>

                                <section class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-sm font-extrabold text-emerald-700">3</span>
                                        <div>
                                            <h4 class="text-base font-extrabold text-slate-950">Notas y evolución</h4>
                                            <p class="mt-1 text-sm text-slate-500">Usa evolución cuando el paciente ya regresó o hubo una actualización.</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Indicaciones para el retorno</span>
                                            <textarea id="seguimiento_notas" name="notas" rows="4" placeholder="Indicaciones que el personal debe revisar cuando vuelva..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('notas') }}</textarea>
                                        </label>
                                        <label class="space-y-2">
                                            <span class="text-sm font-bold text-slate-700">Evolución del paciente</span>
                                            <textarea id="seguimiento_evolucion" name="evolucion" rows="4" placeholder="Ej. Mejoró, sigue estable, presenta nuevos síntomas..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('evolucion') }}</textarea>
                                        </label>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 border-t border-slate-100 bg-white px-6 py-5 sm:px-7">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">Al guardar, el sistema crea o actualiza la cita de retorno según la fecha y hora indicadas.</p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button" onclick="closeSeguimientoModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">Cancelar</button>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                                <i data-feather="check" class="h-5 w-5"></i>
                                <span id="seguimientoSubmitLabel">{{ $isEditSeguimiento ? 'Actualizar control' : 'Guardar control' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if($hasSeguimientoErrors)
    <script>
        window.seguimientoModalState = {
            hasErrors: true,
            editingId: @json(old('editing_id')),
        };
    </script>
@endif
