@php
    $historiaCatalogo = $historiaCatalogo ?? collect();
    $prefillHistoriaId = $prefillHistoriaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $hasRecetaErrors = $errors->recetaStore->any();
    $isEditReceta = old('_method') === 'PUT' && old('editing_id');
@endphp

<div id="recetaModal" data-open-on-load="{{ ($hasRecetaErrors || $shouldOpenCreate) ? 'true' : 'false' }}" data-prefill-historia="{{ old('historia_clinica_id', $prefillHistoriaId) }}" class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="modal-card flex max-h-[calc(100vh-3rem)] w-full max-w-4xl flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="shrink-0 border-b border-slate-100 px-6 py-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Modulo de recetas</p>
                    <h3 id="recetaModalTitle" class="text-2xl font-bold text-slate-900">Nueva receta</h3>
                    <p id="recetaModalSummary" class="mt-1 text-sm text-slate-500">Selecciona la atención clínica de origen y organiza la receta con claridad profesional.</p>
                </div>
                <button type="button" onclick="closeRecetaModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">&times;</button>
            </div>

            <form id="recetaForm" method="POST" action="{{ route('recetas.store') }}" data-store-action="{{ route('recetas.store') }}" data-update-template="{{ url('recetas/__ID__') }}" class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
                @csrf
                @if($isEditReceta)
                    @method('PUT')
                @endif
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">

                @if($hasRecetaErrors)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach($errors->recetaStore->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.95fr)]">
                    <div class="space-y-5">
                        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.3fr)_minmax(260px,0.9fr)]">
                            <div>
                                <label for="receta_historia_search" class="mb-2 block text-sm font-semibold text-slate-600">Atención clínica de origen</label>
                                <div class="mb-3 flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition focus-within:border-blue-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-blue-100">
                                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                    <input id="receta_historia_search" type="text" autocomplete="off" placeholder="Buscar por paciente, propietario, DNI o diagnóstico..." class="ml-3 w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0">
                                </div>
                                <select id="receta_historia_id" name="historia_clinica_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                    <option value="">Selecciona una atención clínica</option>
                                    @foreach($historiaCatalogo as $historiaOption)
                                        @php
                                            $mascotaOption = $historiaOption->mascota;
                                            $clienteOption = optional($mascotaOption)->cliente;
                                            $fotoOption = optional($mascotaOption)->foto ? \App\Support\PhotoUrl::make($mascotaOption->foto) : \App\Support\PhotoUrl::make(null);
                                        @endphp
                                        <option value="{{ $historiaOption->id }}" data-mascota="{{ optional($mascotaOption)->nombre }}" data-cliente="{{ optional($clienteOption)->nombre }}" data-dni="{{ optional($clienteOption)->dni }}" data-tipo="{{ optional($mascotaOption)->tipo_animal }}" data-raza="{{ optional($mascotaOption)->raza }}" data-sexo="{{ optional($mascotaOption)->sexo }}" data-color="{{ optional($mascotaOption)->color }}" data-fecha="{{ optional($historiaOption->fecha)->format('d/m/Y') }}" data-diagnostico="{{ $historiaOption->diagnostico }}" data-servicio="{{ optional($historiaOption->servicioProducto)->nombre }}" data-foto="{{ $fotoOption }}" data-peso="{{ $historiaOption->peso }}" data-temperatura="{{ $historiaOption->temperatura }}" @selected(old('historia_clinica_id', $prefillHistoriaId) == $historiaOption->id)>{{ optional($mascotaOption)->nombre }} - {{ optional($clienteOption)->nombre }} | {{ optional($historiaOption->fecha)->format('d/m/Y') }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs leading-5 text-slate-500">Filtra primero y luego selecciona la atención exacta para evitar recetas en pacientes equivocados.</p>
                            </div>
                            <div>
                                <label for="receta_template" class="mb-2 block text-sm font-semibold text-slate-600">Receta sugerida</label>
                                <select id="receta_template" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                    <option value="">Selecciona una receta sugerida</option>
                                    <option value="dermatitis">Dermatitis</option>
                                    <option value="otitis">Otitis</option>
                                    <option value="gastrointestinal">Gastrointestinal</option>
                                    <option value="postoperatorio">Postoperatorio</option>
                                    <option value="desparasitacion">Desparasitación</option>
                                    <option value="control">Control general</option>
                                </select>
                                <p class="mt-2 text-xs leading-5 text-slate-500">Esto llena una receta base para ahorrar tiempo. Luego puedes ajustarla antes de guardar.</p>
                            </div>
                        </div>
                        <div>
                            <label for="receta_medicamentos" class="mb-2 block text-sm font-semibold text-slate-600">Medicamentos</label>
                            <textarea id="receta_medicamentos" name="medicamentos" rows="5" placeholder="Ejemplo: Amoxicilina, gotas oticas, jarabe..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-6 text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('medicamentos') }}</textarea>
                        </div>
                        <div>
                            <label for="receta_indicaciones" class="mb-2 block text-sm font-semibold text-slate-600">Indicaciones</label>
                            <textarea id="receta_indicaciones" name="indicaciones" rows="6" placeholder="Detalla dosis, frecuencia, vía de administración y duración..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-6 text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('indicaciones') }}</textarea>
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-900">
                            <p class="font-semibold text-blue-700">Idea de uso</p>
                            <p class="mt-1">Este módulo sirve para dejar prescripción clara, imprimible y reutilizable, de modo que el seguimiento no dependa de recordar lo que se recetó.</p>
                        </div>
                    </div>
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-5 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Resumen del paciente</p>
                        <div class="mt-4 rounded-[22px] bg-white p-4 shadow-sm">
                            <div class="flex items-start gap-4">
                                <img id="recetaMascotaPhoto" src="{{ \App\Support\PhotoUrl::make(null) }}" alt="Vista de mascota" class="h-24 w-24 rounded-[20px] object-cover" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">
                                <div class="min-w-0 flex-1">
                                    <p id="recetaMascotaName" class="text-xl font-bold text-slate-900">Selecciona una atención</p>
                                    <p id="recetaMascotaOwner" class="mt-1 text-sm text-slate-500">El propietario aparecerá aquí.</p>
                                    <div class="mt-3 flex flex-wrap gap-2"><span id="recetaMascotaType" class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Paciente veterinario</span><span id="recetaMascotaBreed" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">Raza pendiente</span><span id="recetaMascotaColor" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">Color pendiente</span><span id="recetaMascotaSex" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">Sexo pendiente</span><span id="recetaHistoriaDate" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">Sin fecha</span></div>
                                </div>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                                    <p class="font-semibold text-slate-500">Peso actual</p>
                                    <p id="recetaHistoriaPeso" class="mt-1 text-lg font-bold text-slate-900">Sin dato</p>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                                    <p class="font-semibold text-slate-500">Temperatura</p>
                                    <p id="recetaHistoriaTemperatura" class="mt-1 text-lg font-bold text-slate-900">Sin dato</p>
                                </div>
                            </div>
                            <div class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900"><p class="font-semibold text-amber-700">Diagnóstico base</p><p id="recetaDiagnostico" class="mt-1 leading-6">Se mostrará el diagnóstico asociado.</p></div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-900">
                                    <p class="font-semibold text-blue-700">Vista previa de medicamentos</p>
                                    <div id="recetaPreviewMeds" class="mt-2 space-y-2 text-slate-700">
                                        <p class="text-sm text-slate-500">Cuando escribas los medicamentos, aqui se organizaran mejor.</p>
                                    </div>
                                </div>
                                <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-700">
                                    <p class="font-semibold text-slate-700">Vista previa de indicaciones</p>
                                    <div id="recetaPreviewNotes" class="mt-2 space-y-2 text-slate-700">
                                        <p class="text-sm text-slate-500">Aqui veras una lectura mas clara de las instrucciones para el paciente.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeRecetaModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700"><span id="recetaSubmitLabel">Guardar receta</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($hasRecetaErrors)
<script>window.recetaModalState = { hasErrors: true, isEdit: @json($isEditReceta), editingId: @json(old('editing_id')) };</script>
@endif

