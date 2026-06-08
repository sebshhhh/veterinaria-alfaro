@php
    $historiaCatalogo = $historiaCatalogo ?? collect();
    $veterinarios = $veterinarios ?? collect();
    $productos = $productos ?? collect();
    $prefillHistoriaId = $prefillHistoriaId ?? null;
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $hasTratamientoErrors = $errors->tratamientoStore->any();
    $isEditTratamiento = old('_method') === 'PUT' && old('editing_id');
    $oldProductos = old('productos', []);
@endphp

<div id="tratamientoModal" data-open-on-load="{{ ($hasTratamientoErrors || $shouldOpenCreate) ? 'true' : 'false' }}" data-prefill-historia="{{ old('historia_clinica_id', $prefillHistoriaId) }}" class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="flex max-h-[calc(100vh-3rem)] w-full max-w-4xl flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="shrink-0 border-b border-slate-100 px-6 py-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Modulo de tratamientos</p>
                    <h3 id="tratamientoModalTitle" class="text-2xl font-bold text-slate-900">Nuevo tratamiento</h3>
                    <p id="tratamientoModalSummary" class="mt-1 text-sm text-slate-500">Selecciona la atención clínica de origen y deja el tratamiento conectado al historial del paciente.</p>
                </div>
                <button type="button" onclick="closeTratamientoModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">&times;</button>
            </div>

            <form id="tratamientoForm" method="POST" action="{{ route('tratamientos.store') }}" data-store-action="{{ route('tratamientos.store') }}" data-update-template="{{ url('tratamientos/__ID__') }}" data-today="{{ now()->format('Y-m-d') }}" data-old-products='@json($oldProductos)' class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
                @csrf
                @if($isEditTratamiento)
                    @method('PUT')
                @endif
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">

                @if($hasTratamientoErrors)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach($errors->tratamientoStore->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.95fr)]">
                    <div class="space-y-5">
                        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.3fr)_minmax(260px,0.9fr)]">
                            <div>
                                <label for="tratamiento_historia_id" class="mb-2 block text-sm font-semibold text-slate-600">Atención clínica de origen</label>
                                <select id="tratamiento_historia_id" name="historia_clinica_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                    <option value="">Selecciona una atención clínica</option>
                                    @foreach($historiaCatalogo as $historiaOption)
                                        @php
                                            $mascotaOption = $historiaOption->mascota;
                                            $clienteOption = optional($mascotaOption)->cliente;
                                            $fotoOption = optional($mascotaOption)->foto ? \App\Support\PhotoUrl::make($mascotaOption->foto) : \App\Support\PhotoUrl::make(null);
                                        @endphp
                                        <option value="{{ $historiaOption->id }}" data-mascota="{{ optional($mascotaOption)->nombre }}" data-cliente="{{ optional($clienteOption)->nombre }}" data-tipo="{{ optional($mascotaOption)->tipo_animal }}" data-color="{{ optional($mascotaOption)->color }}" data-fecha="{{ optional($historiaOption->fecha)->format('d/m/Y') }}" data-diagnostico="{{ $historiaOption->diagnostico }}" data-foto="{{ $fotoOption }}" @selected(old('historia_clinica_id', $prefillHistoriaId) == $historiaOption->id)>{{ optional($mascotaOption)->nombre }} - {{ optional($clienteOption)->nombre }} | {{ optional($historiaOption->fecha)->format('d/m/Y') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="tratamiento_template" class="mb-2 block text-sm font-semibold text-slate-600">Guia sugerida</label>
                                <select id="tratamiento_template" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                    <option value="">Selecciona una guia sugerida</option>
                                    <option value="dermatologico">Dermatologico</option>
                                    <option value="otitis">Otitis</option>
                                    <option value="gastrointestinal">Gastrointestinal</option>
                                    <option value="postoperatorio">Postoperatorio</option>
                                    <option value="desparasitacion">Desparasitación</option>
                                    <option value="control">Control general</option>
                                </select>
                                <p class="mt-2 text-xs leading-5 text-slate-500">Esto llena una base de tratamiento para que no escribas todo desde cero. Siempre puedes editarlo antes de guardar.</p>
                            </div>
                        </div>
                        <div>
                            <label for="tratamiento_veterinario_id" class="mb-2 block text-sm font-semibold text-slate-600">Profesional</label>
                            <select id="tratamiento_veterinario_id" name="veterinario_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                <option value="">Selecciona un profesional</option>
                                @foreach($veterinarios as $veterinario)
                                    <option value="{{ $veterinario->id }}" data-name="{{ $veterinario->nombre }}" @selected(old('veterinario_id') == $veterinario->id)>{{ $veterinario->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="tratamiento_descripcion" class="mb-2 block text-sm font-semibold text-slate-600">Descripción del tratamiento</label>
                            <textarea id="tratamiento_descripcion" name="descripcion" rows="5" placeholder="Describe el tratamiento, medicación aplicada o seguimiento..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-6 text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">{{ old('descripcion') }}</textarea>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-4">
                            <div><label for="tratamiento_costo" class="mb-2 block text-sm font-semibold text-slate-600">Costo</label><input id="tratamiento_costo" type="number" step="0.01" min="0" name="costo" value="{{ old('costo', '0') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100"></div>
                            <div><label for="tratamiento_fecha_inicio" class="mb-2 block text-sm font-semibold text-slate-600">Fecha de inicio</label><input id="tratamiento_fecha_inicio" type="date" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100"></div>
                            <div><label for="tratamiento_fecha_fin" class="mb-2 block text-sm font-semibold text-slate-600">Fecha de fin</label><input id="tratamiento_fecha_fin" type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100"></div>
                            <div><label for="tratamiento_proximo_control" class="mb-2 block text-sm font-semibold text-slate-600">Próximo control</label><input id="tratamiento_proximo_control" type="date" name="proximo_control" value="{{ old('proximo_control') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100"></div>
                        </div>
                        <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Insumos y servicios</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-500">Vincula productos o servicios usados en el tratamiento para preparar ventas y control de stock sin registrar lo mismo dos veces.</p>
                                </div>
                                <button id="tratamientoAddProductButton" type="button" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                                    <span>+</span>
                                    Agregar insumo
                                </button>
                            </div>
                            <div id="tratamientoProductosList" class="mt-4 space-y-3"></div>
                            <p class="mt-3 text-xs leading-5 text-slate-500">Si eliges un producto físico, luego podrás descontarlo automáticamente desde ventas. Si eliges un servicio, quedará vinculado como costo del tratamiento.</p>
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                            <p class="font-semibold text-amber-700">Idea de uso</p>
                            <p class="mt-1">Usa este módulo para dejar seguimiento real: cuando inicia, cuando termina, cuánto cuesta y si el paciente está activo, por vencer o ya finalizó.</p>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-5 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Resumen clinico</p>
                        <div class="mt-4 rounded-[22px] bg-white p-4 shadow-sm">
                            <div class="flex items-start gap-4">
                                <img id="tratamientoMascotaPhoto" src="{{ \App\Support\PhotoUrl::make(null) }}" alt="Vista de mascota" class="h-24 w-24 rounded-[20px] object-cover" onerror="this.onerror=null;this.src='{{ \App\Support\PhotoUrl::make(null) }}';">
                                <div class="min-w-0 flex-1">
                                    <p id="tratamientoMascotaName" class="text-xl font-bold text-slate-900">Selecciona una atención</p>
                                    <p id="tratamientoMascotaOwner" class="mt-1 text-sm text-slate-500">El due&ntilde;o aparecerá aquí.</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span id="tratamientoMascotaType" class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Paciente veterinario</span>
                                        <span id="tratamientoMascotaColor" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">Color pendiente</span>
                                        <span id="tratamientoHistoriaDate" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">Sin fecha</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900"><p class="font-semibold text-amber-700">Diagnóstico base</p><p id="tratamientoDiagnostico" class="mt-1 leading-6">Se mostrará el diagnóstico asociado.</p></div>
                                <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-700"><p class="font-semibold text-slate-700">Profesional</p><p id="tratamientoProfesional" class="mt-1 leading-6">Se asignará al guardar.</p></div>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900"><p class="font-semibold text-emerald-700">Estado proyectado</p><p id="tratamientoEstado" class="mt-1 leading-6">Sin definir</p></div>
                                <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-900"><p class="font-semibold text-blue-700">Duración estimada</p><p id="tratamientoDuracion" class="mt-1 leading-6">Sin rango calculado</p></div>
                                <div class="rounded-2xl bg-violet-50 px-4 py-3 text-sm text-violet-900"><p class="font-semibold text-violet-700">Control sugerido</p><p id="tratamientoControl" class="mt-1 leading-6">Defínelo con la fecha de fin.</p></div>
                            </div>
                            <div class="mt-4 rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-700">
                                <p class="font-semibold text-slate-700">Insumos vinculados</p>
                                <p id="tratamientoProductosPreview" class="mt-1 leading-6">Aún no se agregaron productos o servicios a este tratamiento.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeTratamientoModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-200 transition hover:bg-amber-600"><span id="tratamientoSubmitLabel">Guardar tratamiento</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="tratamientoProductoTemplate">
    <div class="tratamiento-product-row grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 md:grid-cols-[minmax(0,1.4fr)_110px_auto]">
        <div>
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Producto o servicio</label>
            <select data-role="producto-select" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                <option value="">Selecciona un producto</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->id }}" data-name="{{ $producto->nombre }}" data-price="{{ $producto->precio }}" data-stock="{{ $producto->stock }}" data-service="{{ $producto->es_servicio ? '1' : '0' }}">
                        {{ $producto->nombre }} | {{ $producto->es_servicio ? 'Servicio' : 'Stock: ' . $producto->stock }} | S/ {{ number_format((float) $producto->precio, 2) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Cantidad</label>
            <input data-role="producto-cantidad" type="number" min="1" value="1" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
        </div>
        <div class="flex items-end">
            <button type="button" data-role="remove-product" class="w-full rounded-2xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-50">Quitar</button>
        </div>
    </div>
</template>

@if ($hasTratamientoErrors)
<script>window.tratamientoModalState = { hasErrors: true, isEdit: @json($isEditTratamiento), editingId: @json(old('editing_id')) };</script>
@endif

