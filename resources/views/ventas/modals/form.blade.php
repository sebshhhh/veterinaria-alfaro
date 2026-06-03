@php
    $clientes = $clientes ?? collect();
    $mascotas = $mascotas ?? collect();
    $historias = $historias ?? collect();
    $productos = $productos ?? collect();
    $tratamientos = $tratamientos ?? collect();
    $shouldOpenCreate = $shouldOpenCreate ?? false;
    $prefillPayload = $prefillPayload ?? ['cliente_id' => null, 'mascota_id' => null, 'historia_clinica_id' => null, 'items' => []];
    $hasVentaErrors = $errors->ventaStore->any();
    $isEditVenta = old('_method') === 'PUT' && old('editing_id');
    $oldItems = old('items', $prefillPayload['items'] ?? []);
    $clientesById = $clientes->keyBy('id');
    $posProductos = $productos->sortByDesc('es_servicio')->values();
@endphp

<div id="ventaModal" data-open-on-load="{{ ($hasVentaErrors || $shouldOpenCreate) ? 'true' : 'false' }}" class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="flex max-h-[calc(100vh-2.5rem)] w-full max-w-[1500px] flex-col overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/25">
            <div class="shrink-0 border-b border-emerald-100 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_34%),linear-gradient(135deg,#f8fffb_0%,#ffffff_55%,#ecfeff_100%)] px-6 py-5 sm:px-7">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[22px] bg-emerald-600 text-white shadow-xl shadow-emerald-200">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 11h10M7 15h5M6.5 3.75h11A2.25 2.25 0 0 1 19.75 6v15l-3-1.5-3 1.5-3-1.5-3 1.5-3-1.5V6A2.25 2.25 0 0 1 6.5 3.75Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.26em] text-emerald-600">Caja y cobros</p>
                            <h3 id="ventaModalTitle" class="mt-1 text-2xl font-black text-slate-950">Nuevo cobro</h3>
                            <p id="ventaModalSummary" class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">Cobra servicios, productos o tratamientos con total calculado y stock sincronizado.</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="rounded-full border border-emerald-200 bg-white px-3 py-1 text-[11px] font-black uppercase tracking-[0.14em] text-emerald-700">1. Paciente opcional</span>
                                <span class="rounded-full border border-cyan-200 bg-white px-3 py-1 text-[11px] font-black uppercase tracking-[0.14em] text-cyan-700">2. POS rápido</span>
                                <span class="rounded-full border border-blue-200 bg-white px-3 py-1 text-[11px] font-black uppercase tracking-[0.14em] text-blue-700">3. Confirmar cobro</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="closeVentaModal()" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-xl leading-none text-slate-500 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">&times;</button>
                </div>
            </div>

            <form id="ventaForm"
                  method="POST"
                  action="{{ route('ventas.store') }}"
                  data-store-action="{{ route('ventas.store') }}"
                  data-update-template="{{ url('ventas/__ID__') }}"
                  data-prefill='@json($prefillPayload)'
                  data-old-items='@json($oldItems)'
                  class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 px-5 py-5 sm:px-6">
                @csrf
                @if($isEditVenta)
                    @method('PUT')
                @endif
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">

                @if($hasVentaErrors)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach($errors->ventaStore->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <datalist id="ventaPacienteOptions">
                    @foreach($mascotas as $mascota)
                        @php $cliente = $clientesById->get($mascota->cliente_id); @endphp
                        <option
                            value="{{ $mascota->nombre }} - {{ optional($cliente)->nombre }}"
                            data-cliente-id="{{ $mascota->cliente_id }}"
                            data-mascota-id="{{ $mascota->id }}"
                            data-label="{{ $mascota->nombre }} {{ optional($cliente)->nombre }} {{ optional($cliente)->dni }}">
                            DNI {{ optional($cliente)->dni }} · {{ $mascota->tipo_animal }}
                        </option>
                    @endforeach
                </datalist>

                <div class="grid gap-5 2xl:grid-cols-[minmax(0,1.55fr)_400px]">
                    <div class="space-y-5">
                        <section class="rounded-[28px] border border-emerald-100 bg-white p-5 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
    <div class="flex items-start gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
            </svg>
        </span>
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-600">Paciente opcional</p>
            <h4 class="mt-1 text-lg font-black text-slate-950">Conecta el cobro solo si corresponde</h4>
            <p class="mt-1 text-sm leading-6 text-slate-500">Para mostrador puedes cobrar sin cliente. Para atención clínica, busca la mascota y el sistema relaciona el cobro.</p>
        </div>
    </div>
</div>
<label for="venta_context_search" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Buscar paciente</label>
                            <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_minmax(280px,auto)_auto] xl:items-center">
                                <input id="venta_context_search" type="search" list="ventaPacienteOptions" placeholder="Mascota, dueño o DNI..." class="w-full rounded-2xl border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                <span id="ventaContextHint" class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs font-bold leading-5 text-emerald-800 shadow-sm">Venta rápida sin cliente por defecto. Busca una mascota solo si deseas asociar el cobro.</span>
                                <button id="ventaClearPatientButton" type="button" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-black uppercase tracking-[0.12em] text-slate-600 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">Limpiar paciente</button>
                            </div>
                        </section>

                        <div class="hidden" aria-hidden="true">
                            <div>
                                <label for="venta_cliente_id" class="mb-2 block text-sm font-semibold text-slate-600">Cliente</label>
                                <select id="venta_cliente_id" name="cliente_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Venta rápida / cliente no registrado</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" data-name="{{ $cliente->nombre }}" @selected(old('cliente_id', $prefillPayload['cliente_id']) == $cliente->id)>{{ $cliente->nombre }} | DNI {{ $cliente->dni }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="venta_mascota_id" class="mb-2 block text-sm font-semibold text-slate-600">Mascota</label>
                                <select id="venta_mascota_id" name="mascota_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Sin mascota</option>
                                    @foreach($mascotas as $mascota)
                                        <option value="{{ $mascota->id }}" data-cliente-id="{{ $mascota->cliente_id }}" data-name="{{ $mascota->nombre }}" @selected(old('mascota_id', $prefillPayload['mascota_id']) == $mascota->id)>{{ $mascota->nombre }} - {{ optional($mascota->cliente)->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="venta_historia_id" class="mb-2 block text-sm font-semibold text-slate-600">Atención relacionada</label>
                                <select id="venta_historia_id" name="historia_clinica_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Sin atención específica</option>
                                    @foreach($historias as $historia)
                                        <option value="{{ $historia->id }}" data-mascota-id="{{ $historia->mascota_id }}" data-cliente-id="{{ optional(optional($historia->mascota)->cliente)->id }}" @selected(old('historia_clinica_id', $prefillPayload['historia_clinica_id']) == $historia->id)>{{ optional($historia->mascota)->nombre }} | {{ optional($historia->fecha)->format('d/m/Y') }} | {{ \Illuminate\Support\Str::limit($historia->diagnostico, 30) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="venta_metodo_pago" class="mb-2 block text-sm font-semibold text-slate-600">Método de pago</label>
                                <select id="venta_metodo_pago" name="metodo_pago" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value="efectivo" @selected(old('metodo_pago') === 'efectivo')>Efectivo</option>
                                    <option value="yape" @selected(old('metodo_pago') === 'yape')>Yape</option>
                                    <option value="tarjeta" @selected(old('metodo_pago') === 'tarjeta')>Tarjeta</option>
                                    <option value="transferencia" @selected(old('metodo_pago') === 'transferencia')>Transferencia</option>
                                </select>
                            </div>
                            <div>
                                <label for="venta_estado" class="mb-2 block text-sm font-semibold text-slate-600">Estado</label>
                                <select id="venta_estado" name="estado" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value="pagado" @selected(old('estado', 'pagado') === 'pagado')>Pagado</option>
                                    <option value="pendiente" @selected(old('estado') === 'pendiente')>Pendiente</option>
                                    <option value="anulado" @selected(old('estado') === 'anulado')>Anulado</option>
                                </select>
                            </div>
                            <div>
                                <label for="venta_fecha" class="mb-2 block text-sm font-semibold text-slate-600">Fecha</label>
                                <input id="venta_fecha" type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                            </div>
                        </div>

                        <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                <div>
                                    <div class="flex items-start gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10" /></svg></span><div><p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-600">Catálogo rápido POS</p><h4 class="mt-1 text-lg font-black text-slate-950">Toca para agregar al cobro</h4>
                                    <p class="mt-1 text-sm leading-6 text-slate-500">Productos controlan stock. Servicios como baño, corte o consulta se cobran sin descontar unidades.</p></div></div>
                                </div>
                                <input id="venta_pos_search" type="search" placeholder="Filtrar producto o servicio..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 xl:max-w-sm">
                            </div>
                            <div id="ventaPosCatalog" class="mt-5 grid max-h-[360px] gap-3 overflow-y-auto pr-1 sm:grid-cols-2 xl:grid-cols-3">
                                @forelse($posProductos as $producto)
                                    @php
                                        $agotado = !$producto->es_servicio && $producto->stock <= 0;
                                        $categoria = ucfirst(str_replace('_', ' ', (string) $producto->categoria));
                                    @endphp
                                    <button type="button"
                                            data-pos-product
                                            data-id="{{ $producto->id }}"
                                            data-name="{{ $producto->nombre }}"
                                            data-price="{{ $producto->precio }}"
                                            data-stock="{{ $producto->stock }}"
                                            data-service="{{ $producto->es_servicio ? '1' : '0' }}"
                                            data-search="{{ $producto->nombre }} {{ $categoria }} {{ $producto->es_servicio ? 'servicio' : 'producto' }}"
                                            @disabled($agotado)
                                            class="group overflow-hidden rounded-[22px] border p-4 text-left transition {{ $agotado ? 'cursor-not-allowed border-slate-200 bg-slate-100 opacity-60' : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-xl hover:shadow-emerald-100/80' }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black text-slate-900">{{ $producto->nombre }}</p>
                                                <p class="mt-1 text-xs font-semibold {{ $producto->es_servicio ? 'text-cyan-700' : 'text-blue-700' }}">{{ $producto->es_servicio ? 'Servicio' : 'Producto' }} · {{ $categoria }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-full {{ $producto->es_servicio ? 'bg-cyan-50 text-cyan-700' : 'bg-blue-50 text-blue-700' }} px-2.5 py-1 text-[11px] font-bold">
                                                {{ $producto->es_servicio ? 'Sin stock' : $producto->stock . ' und.' }}
                                            </span>
                                        </div>
                                        <p class="mt-3 text-2xl font-black text-emerald-700">S/ {{ number_format((float) $producto->precio, 2) }}</p>
                                    </button>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center text-sm font-semibold text-slate-500 sm:col-span-2 xl:col-span-3">
                                        Registra productos o servicios en Servicios e Inventario para usarlos aquí.
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Detalle del cobro</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-500">Agrega productos, servicios o tratamientos. El sistema calcula subtotal, total y stock según el estado del cobro.</p>
                                </div>
                                <button id="ventaAddItemButton" type="button" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-black text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                    <span>+</span>
                                    Agregar manualmente
                                </button>
                            </div>
                            <div id="ventaItemsList" class="mt-4 space-y-3"></div>
                        </div>
                    </div>

                    <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-lg shadow-slate-200/70 2xl:sticky 2xl:top-0 2xl:self-start">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-600">Resumen de cobro</p>
                        <div class="mt-4 rounded-[24px] border border-slate-200 bg-slate-50 p-4 shadow-sm">
                            <p id="ventaPreviewCliente" class="text-xl font-black text-slate-950">Venta rápida / sin cliente</p>
                            <p id="ventaPreviewMascota" class="mt-1 text-sm text-slate-500">Puedes asociar una mascota si corresponde.</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900"><p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700">Estado</p><p id="ventaPreviewEstado" class="mt-1 leading-6">Pagado</p></div>
                                <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-900"><p class="text-xs font-black uppercase tracking-[0.16em] text-blue-700">Método</p><p id="ventaPreviewMetodo" class="mt-1 leading-6">Efectivo</p></div>
                            </div>
                            <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Detalle calculado</p>
                                <p id="ventaPreviewItems" class="mt-2 max-h-32 overflow-y-auto leading-6">Aún no se agregaron items.</p>
                            </div>
                            <div class="mt-4 rounded-[24px] bg-gradient-to-br from-emerald-600 to-cyan-600 px-5 py-5 text-white shadow-xl shadow-emerald-200">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-100">Total estimado</p>
                                <p id="ventaPreviewTotal" class="mt-2 text-4xl font-black tracking-tight">S/ 0.00</p>
                            </div>
                            <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-900">
                                <p class="font-black">Uso recomendado</p>
                                <p class="mt-1">Mostrador: vende sin cliente. Atención: asocia mascota/historia. Tratamiento: el sistema completa el paciente automáticamente.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeVentaModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700"><span id="ventaSubmitLabel">Guardar cobro</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="ventaItemTemplate">
    <div class="venta-item-row rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 2xl:grid-cols-[170px_minmax(0,1fr)_130px_140px_auto] xl:items-end">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Tipo</label>
                <select data-role="item-type" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                    <option value="producto">Producto o servicio</option>
                    <option value="tratamiento">Tratamiento</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Elemento</label>
                <input data-role="item-search" type="search" placeholder="Busca por nombre antes de seleccionar..." class="mb-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                <select data-role="item-producto" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                    <option value="">Selecciona un producto o servicio</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}" data-name="{{ $producto->nombre }}" data-price="{{ $producto->precio }}" data-stock="{{ $producto->stock }}" data-service="{{ $producto->es_servicio ? '1' : '0' }}">{{ $producto->nombre }} | {{ $producto->es_servicio ? 'Servicio' : 'Stock: ' . $producto->stock }} | S/ {{ number_format((float) $producto->precio, 2) }}</option>
                    @endforeach
                </select>
                <select data-role="item-tratamiento" class="mt-3 hidden w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                    <option value="">Selecciona un tratamiento</option>
                    @foreach($tratamientos as $tratamiento)
                        <option value="{{ $tratamiento->id }}"
                                data-name="{{ \Illuminate\Support\Str::limit($tratamiento->descripcion, 65) }}"
                                data-price="{{ $tratamiento->costo }}"
                                data-cliente-id="{{ optional(optional(optional($tratamiento->historiaClinica)->mascota)->cliente)->id }}"
                                data-mascota-id="{{ optional($tratamiento->historiaClinica)->mascota_id }}"
                                data-historia-id="{{ $tratamiento->historia_clinica_id }}">
                            {{ optional(optional($tratamiento->historiaClinica)->mascota)->nombre }} | {{ optional($tratamiento->fecha_inicio)->format('d/m/Y') }} | S/ {{ number_format((float) $tratamiento->costo, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Cantidad</label>
                <input data-role="item-cantidad" type="number" min="1" value="1" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Precio</label>
                <input data-role="item-precio" type="number" step="0.01" min="0" value="0" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm">
            </div>
            <div class="flex items-end">
                <button type="button" data-role="remove-item" class="w-full rounded-2xl border border-rose-200 bg-white px-4 py-3 text-sm font-black text-rose-700 transition hover:border-rose-300 hover:bg-rose-50">Quitar</button>
            </div>
        </div>
    </div>
</template>

@if ($hasVentaErrors)
<script>window.ventaModalState = { hasErrors: true, isEdit: @json($isEditVenta), editingId: @json(old('editing_id')) };</script>
@endif



