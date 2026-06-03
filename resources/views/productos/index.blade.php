<x-app-layout>
@php
    $stats = $stats ?? [];
    $categoryOptions = $categoryOptions ?? [];
    $categoryLabels = $categoryLabels ?? [];
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-6">
        <section class="overflow-hidden rounded-[32px] border border-blue-100 bg-white shadow-xl shadow-slate-200/70">
            <div class="relative border-b border-blue-100 bg-gradient-to-br from-blue-50 via-white to-cyan-50 px-6 py-6 sm:px-8">
                <div class="absolute right-8 top-8 hidden h-28 w-28 rounded-full bg-cyan-200/30 blur-3xl lg:block"></div>
                <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.27 9 5.15m-9 10.31 9-5.15m-9-5.16v10.31M3 6.75l9-5.25 9 5.25-9 5.25-9-5.25Z" />
                            </svg>
                        </div>
                        <div class="max-w-3xl">
                            <p class="text-xs font-bold uppercase tracking-[0.26em] text-blue-600">Gestión operativa</p>
                            <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Servicios e Inventario</h1>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Centraliza productos, vacunas, medicamentos y servicios cobrables. La atención usa esta base para sugerir precios y Caja controla cobros sin duplicar registros.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700"><span class="h-2 w-2 rounded-full bg-blue-500"></span>Base única de precios</span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Stock para productos</span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-1.5 text-xs font-semibold text-cyan-700"><span class="h-2 w-2 rounded-full bg-cyan-500"></span>Servicios sin stock</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="rounded-2xl border border-blue-100 bg-white/90 px-4 py-3 text-sm text-slate-500 shadow-sm">
                            <span class="block text-2xl font-black text-slate-950">{{ $stats['total'] ?? 0 }}</span>
                            registros disponibles
                        </div>
                        <button type="button" onclick="openProductoModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-blue-700">
                            <span class="text-lg leading-none">+</span>
                            Nuevo registro
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Total</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950">{{ $stats['total'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-slate-500">Base de trabajo</p>
            </article>
            <article class="rounded-[24px] border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">Productos</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.27 9 5.15M3 6.75l9-5.25 9 5.25-9 5.25-9-5.25Z" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950">{{ $stats['fisicos'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-blue-700/80">Controlan stock</p>
            </article>
            <article class="rounded-[24px] border border-cyan-200 bg-cyan-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-700">Servicios</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-600 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950">{{ $stats['servicios'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-cyan-700/80">Cobrables sin stock</p>
            </article>
            <article class="rounded-[24px] border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700">Bajo stock</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3l9 16H3L12 3Z" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950">{{ $stats['bajo_stock'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-amber-700/80">Reponer pronto</p>
            </article>
            <article class="rounded-[24px] border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-700">Agotados</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-500 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950">{{ $stats['agotados'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-rose-700/80">Sin unidades</p>
            </article>
        </section>

        <form method="GET" action="{{ route('productos.index') }}" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_180px_220px_190px_auto] xl:items-end">
                <div>
                    <label for="search" class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Buscar</label>
                    <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Nombre, categoría o descripción..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label for="tipo" class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Tipo</label>
                    <select id="tipo" name="tipo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Todos</option>
                        <option value="producto" @selected(request('tipo') === 'producto')>Producto físico</option>
                        <option value="servicio" @selected(request('tipo') === 'servicio')>Servicio</option>
                    </select>
                </div>
                <div>
                    <label for="categoria" class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Categoría</label>
                    <select id="categoria" name="categoria" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Todas</option>
                        @foreach($categoryOptions as $group => $options)
                            <optgroup label="{{ $group }}">
                                @foreach($options as $value => $label)
                                    <option value="{{ $value }}" @selected(request('categoria') === $value)>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="stock" class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Stock</label>
                    <select id="stock" name="stock" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Todo</option>
                        <option value="bajo" @selected(request('stock') === 'bajo')>Bajo stock</option>
                        <option value="agotado" @selected(request('stock') === 'agotado')>Agotados</option>
                    </select>
                </div>
                <div class="flex gap-2 xl:justify-end">
                    <button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">Buscar</button>
                    <a href="{{ route('productos.index') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a>
                </div>
            </div>
        </form>

        <section class="rounded-[30px] border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Base operativa</p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">Registros disponibles</h2>
                    <p class="mt-1 text-sm text-slate-500">Usa estos precios y existencias desde atención, tratamientos y caja.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">{{ $productos->total() }} resultados</span>
            </div>

            <div class="grid gap-4 p-5 lg:grid-cols-2 2xl:grid-cols-3">
                @forelse($productos as $producto)
                    @php
                        $payload = [
                            'id' => $producto->id,
                            'nombre' => $producto->nombre,
                            'descripcion' => $producto->descripcion,
                            'precio' => (string) $producto->precio,
                            'stock' => $producto->stock,
                            'es_servicio' => $producto->es_servicio ? '1' : '0',
                            'categoria' => $producto->categoria,
                        ];
                        $categoryLabel = $categoryLabels[$producto->categoria] ?? 'Sin categoría';
                        $tone = $producto->es_servicio
                            ? ['wrap' => 'border-cyan-200 bg-cyan-50', 'badge' => 'bg-cyan-600 text-white', 'icon' => 'bg-cyan-100 text-cyan-700', 'label' => 'Servicio']
                            : ['wrap' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-600 text-white', 'icon' => 'bg-blue-100 text-blue-700', 'label' => 'Producto'];
                        $stockTone = $producto->es_servicio
                            ? ['label' => 'No usa stock', 'class' => 'border-cyan-200 bg-cyan-50 text-cyan-700']
                            : ($producto->stock <= 0
                                ? ['label' => 'Agotado', 'class' => 'border-rose-200 bg-rose-50 text-rose-700']
                                : ($producto->stock <= 5
                                    ? ['label' => 'Bajo stock', 'class' => 'border-amber-200 bg-amber-50 text-amber-700']
                                    : ['label' => 'Disponible', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700']));
                    @endphp
                    <article class="group rounded-[26px] border {{ $tone['wrap'] }} p-1 transition duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-200/80">
                        <div class="h-full rounded-[22px] bg-white p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tone['icon'] }}">
                                        @if($producto->es_servicio)
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        @else
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75 12 1.5l9 5.25-9 5.25-9-5.25Z" /></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="inline-flex rounded-full {{ $tone['badge'] }} px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.14em]">{{ $tone['label'] }}</span>
                                        <h3 class="mt-2 truncate text-xl font-black text-slate-950">{{ $producto->nombre }}</h3>
                                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $categoryLabel }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-bold {{ $stockTone['class'] }}">{{ $stockTone['label'] }}</span>
                            </div>

                            <p class="mt-4 min-h-[48px] text-sm leading-6 text-slate-500">{{ $producto->descripcion ?: 'Sin descripción registrada por ahora.' }}</p>

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Precio</p>
                                    <p class="mt-1 text-lg font-black text-slate-950">S/ {{ number_format((float) $producto->precio, 2) }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Stock</p>
                                    <p class="mt-1 text-lg font-black text-slate-950">{{ $producto->es_servicio ? 'N/A' : $producto->stock }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Uso</p>
                                    <p class="mt-1 text-lg font-black text-slate-950">{{ $producto->tratamientos_count + $producto->detalle_ventas_count }}</p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <button type="button" onclick='openEditProductoModal(@json($payload))' class="rounded-2xl border border-blue-200 bg-white px-3 py-2.5 text-sm font-bold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">Editar</button>
                                <a href="{{ route('ventas.index', ['search' => $producto->nombre]) }}" class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-3 py-2.5 text-sm font-bold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50">Caja</a>
                                <form method="POST" action="{{ route('productos.destroy', $producto) }}" onsubmit="return confirm('¿Eliminar este registro?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-2xl border border-rose-200 bg-white px-3 py-2.5 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-50">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm lg:col-span-2 2xl:col-span-3">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-50 text-blue-600">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.27 9 5.15M3 6.75l9-5.25 9 5.25-9 5.25-9-5.25Z" /></svg>
                        </div>
                        <h3 class="mt-4 text-xl font-black text-slate-950">Aún no hay productos o servicios</h3>
                        <p class="mt-2 text-sm text-slate-500">Registra la base operativa para que atención y caja trabajen sin duplicar datos.</p>
                        <button type="button" onclick="openProductoModal()" class="mt-5 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">Registrar primero</button>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $productos->links('pagination::tailwind') }}
            </div>
        </section>
    </div>
</div>

@include('productos.modals.form')
<script src="{{ asset('js/modules/productos.js') }}?v={{ filemtime(public_path('js/modules/productos.js')) }}"></script>
</x-app-layout>