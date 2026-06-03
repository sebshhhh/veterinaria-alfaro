@php
    $hasProductoErrors = $errors->productoStore->any();
    $isEditProducto = old('_method') === 'PUT' && old('editing_id');
    $categoryOptions = $categoryOptions ?? [];
@endphp

<div id="productoModal" data-open-on-load="{{ $hasProductoErrors ? 'true' : 'false' }}" class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="flex max-h-[calc(100vh-3rem)] w-full max-w-4xl flex-col overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="shrink-0 border-b border-slate-100 bg-gradient-to-r from-blue-50 via-white to-cyan-50 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.27 9 5.15M3 6.75l9-5.25 9 5.25-9 5.25-9-5.25Z" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Servicios e Inventario</p>
                            <h3 id="productoModalTitle" class="mt-1 text-2xl font-black text-slate-950">Nuevo registro</h3>
                            <p id="productoModalSummary" class="mt-1 text-sm leading-6 text-slate-500">Registra un producto físico o servicio cobrable para usarlo en atención, tratamientos y caja.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeProductoModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">&times;</button>
                </div>
            </div>

            <form id="productoForm" method="POST" action="{{ route('productos.store') }}" data-store-action="{{ route('productos.store') }}" data-update-template="{{ url('productos/__ID__') }}" class="min-h-0 flex-1 overflow-y-auto px-6 py-6">
                @csrf
                @if($isEditProducto)
                    @method('PUT')
                @endif
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">

                @if($hasProductoErrors)
                    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach($errors->productoStore->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">
                    <div class="space-y-5">
                        <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Clasificación</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="producto_es_servicio" class="mb-2 block text-sm font-bold text-slate-700">Tipo de registro</label>
                                    <select id="producto_es_servicio" name="es_servicio" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                        <option value="0" @selected(old('es_servicio', '0') == '0')>Producto físico</option>
                                        <option value="1" @selected(old('es_servicio') == '1')>Servicio</option>
                                    </select>
                                    <p class="mt-2 text-xs leading-5 text-slate-500">Producto controla stock. Servicio solo controla precio.</p>
                                </div>
                                <div>
                                    <label for="producto_categoria" class="mb-2 block text-sm font-bold text-slate-700">Categoría</label>
                                    <select id="producto_categoria" name="categoria" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                        @foreach($categoryOptions as $group => $options)
                                            @php $kind = $group === 'Servicios' ? 'servicio' : 'producto'; @endphp
                                            <optgroup label="{{ $group }}">
                                                @foreach($options as $value => $label)
                                                    <option value="{{ $value }}" data-kind="{{ $kind }}" @selected(old('categoria', 'medicamento') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Datos principales</p>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="producto_nombre" class="mb-2 block text-sm font-bold text-slate-700">Nombre</label>
                                    <input id="producto_nombre" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Baño medicado, Vacuna antirrábica, Shampoo medicado" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label for="producto_descripcion" class="mb-2 block text-sm font-bold text-slate-700">Descripción</label>
                                    <textarea id="producto_descripcion" name="descripcion" rows="4" placeholder="Uso, detalle clínico o indicación comercial..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('descripcion') }}</textarea>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Precio y disponibilidad</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="producto_precio" class="mb-2 block text-sm font-bold text-slate-700">Precio base</label>
                                    <input id="producto_precio" type="number" step="0.01" min="0" name="precio" value="{{ old('precio', '0') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label for="producto_stock" class="mb-2 block text-sm font-bold text-slate-700">Stock disponible</label>
                                    <input id="producto_stock" type="number" min="0" name="stock" value="{{ old('stock', '0') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                    <p id="productoStockHelp" class="mt-2 text-xs leading-5 text-slate-500">Solo aplica para productos físicos.</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="rounded-[26px] border border-blue-100 bg-blue-50/70 p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">Resumen automático</p>
                        <div class="mt-4 rounded-[24px] bg-white p-5 shadow-sm">
                            <span id="productoPreviewType" class="inline-flex rounded-full bg-blue-600 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-white">Producto físico</span>
                            <h4 id="productoPreviewName" class="mt-3 text-xl font-black text-slate-950">Producto o servicio</h4>
                            <p id="productoPreviewCategory" class="mt-1 text-sm font-semibold text-slate-500">Medicamento</p>
                            <div class="mt-5 grid gap-3">
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Precio</p>
                                    <p id="productoPreviewPrice" class="mt-1 text-2xl font-black text-slate-950">S/ 0.00</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Stock</p>
                                    <p id="productoPreviewStock" class="mt-1 text-lg font-black text-slate-950">0 unidades</p>
                                </div>
                            </div>
                            <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm leading-6 text-slate-700">
                                <p class="font-bold text-slate-900">Cómo se usará</p>
                                <p id="productoPreviewAutomation" class="mt-1">Este registro podrá seleccionarse en atención, tratamientos y caja sin volver a escribirlo.</p>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeProductoModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700"><span id="productoSubmitLabel">Guardar registro</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($hasProductoErrors)
<script>window.productoModalState = { hasErrors: true, isEdit: @json($isEditProducto), editingId: @json(old('editing_id')) };</script>
@endif