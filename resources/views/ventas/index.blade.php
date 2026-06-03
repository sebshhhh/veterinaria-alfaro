<x-app-layout>
@php
    $stats = $stats ?? [];
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-6">
        <section class="overflow-hidden rounded-[34px] border border-emerald-100 bg-white shadow-xl shadow-slate-200/70">
            <div class="relative border-b border-emerald-100 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_34%),linear-gradient(135deg,#f8fffb_0%,#ffffff_48%,#ecfeff_100%)] px-6 py-6 sm:px-8">
                <div class="absolute right-10 top-8 hidden h-32 w-32 rounded-full bg-emerald-200/30 blur-3xl lg:block"></div>
                <div class="relative flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[24px] bg-emerald-600 text-white shadow-xl shadow-emerald-200">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 11h10M7 15h5M6.5 3.75h11A2.25 2.25 0 0 1 19.75 6v15l-3-1.5-3 1.5-3-1.5-3 1.5-3-1.5V6A2.25 2.25 0 0 1 6.5 3.75Z" />
                            </svg>
                        </div>
                        <div class="max-w-3xl">
                            <p class="text-xs font-black uppercase tracking-[0.28em] text-emerald-600">Caja operativa</p>
                            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Caja y cobros</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Cobra productos, servicios y tratamientos desde un flujo tipo POS. Puedes vender sin cliente o conectar el cobro a una mascota, atención o tratamiento cuando corresponda.</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>POS rápido</span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-1.5 text-xs font-bold text-blue-700"><span class="h-2 w-2 rounded-full bg-blue-500"></span>Stock sincronizado</span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-1.5 text-xs font-bold text-cyan-700"><span class="h-2 w-2 rounded-full bg-cyan-500"></span>Venta sin cliente</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="rounded-[22px] border border-emerald-100 bg-white/90 px-5 py-4 text-sm text-slate-500 shadow-sm">
                            <span class="block text-3xl font-black text-slate-950">{{ $stats['total'] ?? 0 }}</span>
                            cobros registrados
                        </div>
                        <button type="button" onclick="openVentaModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-xl shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" /></svg>
                            Nuevo cobro
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-[26px] border border-blue-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-100/60">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Total</p>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10" /></svg></span>
                </div>
                <p class="mt-4 text-4xl font-black tracking-tight text-slate-950">{{ $stats['total'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-slate-500">Registros de cobro</p>
            </article>
            <article class="rounded-[26px] border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-100/70">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">Pagadas</p>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-200"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg></span>
                </div>
                <p class="mt-4 text-4xl font-black tracking-tight text-slate-950">{{ $stats['pagadas'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-emerald-700/80">Cerradas correctamente</p>
            </article>
            <article class="rounded-[26px] border border-amber-200 bg-amber-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-100/70">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-amber-700">Pendientes</p>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-200"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                </div>
                <p class="mt-4 text-4xl font-black tracking-tight text-slate-950">{{ $stats['pendientes'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-amber-700/80">Sin afectar stock aún</p>
            </article>
            <article class="rounded-[26px] border border-cyan-200 bg-cyan-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-cyan-100/70">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-700">Hoy</p>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-600 text-white shadow-lg shadow-cyan-200"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 9h16M5.5 5.5h13A1.5 1.5 0 0 1 20 7v12a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19V7a1.5 1.5 0 0 1 1.5-1.5Z" /></svg></span>
                </div>
                <p class="mt-4 text-4xl font-black tracking-tight text-slate-950">{{ $stats['hoy'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-cyan-700/80">Movimientos del día</p>
            </article>
            <article class="rounded-[26px] border border-violet-200 bg-violet-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-violet-100/70">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-700">Ingresos</p>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-600 text-white shadow-lg shadow-violet-200"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" /></svg></span>
                </div>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-950">S/ {{ number_format((float) ($stats['ingresos'] ?? 0), 2) }}</p>
                <p class="mt-1 text-sm text-violet-700/80">Cobros pagados</p>
            </article>
        </section>

        <form method="GET" action="{{ route('ventas.index') }}" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(210px,0.8fr)_minmax(210px,0.8fr)_auto] xl:items-end">
                <div>
                    <label for="search" class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Buscar cobro</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Cliente, mascota, DNI, método o servicio..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pl-12 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </div>
                </div>
                <div>
                    <label for="estado" class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Estado</label>
                    <select id="estado" name="estado" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                        <option value="">Todos</option>
                        <option value="pagado" @selected(request('estado') === 'pagado')>Pagado</option>
                        <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente</option>
                        <option value="anulado" @selected(request('estado') === 'anulado')>Anulado</option>
                    </select>
                </div>
                <div>
                    <label for="fecha" class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-slate-500">Fecha</label>
                    <input id="fecha" type="date" name="fecha" value="{{ request('fecha') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>
                <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-lg shadow-slate-200 transition hover:-translate-y-0.5 hover:bg-slate-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        Buscar
                    </button>
                    <a href="{{ route('ventas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a>
                </div>
            </div>
        </form>

        <section class="rounded-[30px] border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Movimientos</p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">Cobros registrados</h2>
                    <p class="mt-1 text-sm text-slate-500">Vista rápida para revisar estado, total, paciente asociado y detalle cobrado.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">{{ $ventas->total() }} resultados</span>
            </div>

            <div class="grid gap-4 p-5 xl:grid-cols-2">
                @forelse($ventas as $venta)
                    @php
                        $payload = [
                            'id' => $venta->id,
                            'cliente_id' => $venta->cliente_id,
                            'mascota_id' => $venta->mascota_id,
                            'historia_clinica_id' => $venta->historia_clinica_id,
                            'metodo_pago' => $venta->metodo_pago,
                            'estado' => $venta->estado,
                            'fecha' => optional($venta->fecha)->format('Y-m-d'),
                            'items' => $venta->detalles->map(function ($detalle) {
                                return [
                                    'tipo' => $detalle->producto_id ? 'producto' : 'tratamiento',
                                    'producto_id' => $detalle->producto_id,
                                    'tratamiento_id' => $detalle->tratamiento_id,
                                    'cantidad' => $detalle->cantidad,
                                    'precio' => (float) $detalle->precio,
                                ];
                            })->values()->all(),
                        ];
                        $estadoClass = $venta->estado === 'pagado'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : ($venta->estado === 'pendiente'
                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                : 'border-rose-200 bg-rose-50 text-rose-700');
                        $estadoIconClass = $venta->estado === 'pagado'
                            ? 'bg-emerald-600 text-white shadow-emerald-200'
                            : ($venta->estado === 'pendiente'
                                ? 'bg-amber-500 text-white shadow-amber-200'
                                : 'bg-rose-500 text-white shadow-rose-200');
                    @endphp
                    <article class="group overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-200/80">
                        <div class="h-1.5 bg-gradient-to-r {{ $venta->estado === 'pagado' ? 'from-emerald-500 to-cyan-400' : ($venta->estado === 'pendiente' ? 'from-amber-400 to-orange-400' : 'from-rose-500 to-pink-400') }}"></div>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-start gap-3">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-lg {{ $estadoIconClass }}">
                                        @if($venta->estado === 'pagado')
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                        @elseif($venta->estado === 'pendiente')
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        @else
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Cobro #{{ $venta->id }}</p>
                                        <h3 class="mt-1 truncate text-xl font-black text-slate-950">{{ optional($venta->cliente)->nombre ?: 'Venta rápida / sin cliente' }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ optional($venta->mascota)->nombre ?: 'Sin mascota específica' }} · {{ optional($venta->fecha)->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-black {{ $estadoClass }}">{{ ucfirst($venta->estado) }}</span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-slate-50 px-4 py-3"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Método</p><p class="mt-1 text-sm font-bold text-slate-900">{{ ucfirst($venta->metodo_pago) }}</p></div>
                                <div class="rounded-2xl bg-emerald-50 px-4 py-3"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">Total</p><p class="mt-1 text-lg font-black text-emerald-700">S/ {{ number_format((float) $venta->total, 2) }}</p></div>
                                <div class="rounded-2xl bg-blue-50 px-4 py-3"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700">Items</p><p class="mt-1 text-sm font-bold text-slate-900">{{ $venta->detalles->count() }} elementos</p></div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm leading-6 text-emerald-900">
                                <div class="flex items-center gap-2 font-black text-emerald-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10" /></svg>Detalle cobrado</div>
                                <p class="mt-1 text-slate-700">{{ $venta->detalles->map(fn ($detalle) => $detalle->producto?->nombre ?: \Illuminate\Support\Str::limit($detalle->tratamiento?->descripcion, 45))->implode(' | ') ?: 'Sin detalle visible' }}</p>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <button type="button" onclick='openEditVentaModal(@json($payload))' class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-3 py-2.5 text-sm font-black text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.651-1.651a1.875 1.875 0 0 1 2.652 2.652L8.582 18.07 4 19l.93-4.582L16.862 4.487Z" /></svg>Editar</button>
                                @if($venta->historia_clinica_id)
                                    <a href="{{ route('historias-clinicas.index', ['mascota_id' => optional($venta->mascota)->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-white px-3 py-2.5 text-sm font-black text-blue-700 transition hover:border-blue-300 hover:bg-blue-50"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h7.5L19.5 9v11.25H6.75A2.25 2.25 0 0 1 4.5 18V6A2.25 2.25 0 0 1 6.75 3.75Z" /></svg>Historial</a>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-black text-slate-400">Sin historia</span>
                                @endif
                                <form method="POST" action="{{ route('ventas.destroy', $venta) }}" onsubmit="return confirm('¿Eliminar este cobro?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-white px-3 py-2.5 text-sm font-black text-rose-700 transition hover:border-rose-300 hover:bg-rose-50"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0 .75 12h6.5L16 7M10 7V5h4v2" /></svg>Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm xl:col-span-2">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-emerald-50 text-emerald-600"><svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 11h10M7 15h5M6.5 3.75h11A2.25 2.25 0 0 1 19.75 6v15l-3-1.5-3 1.5-3-1.5-3 1.5-3-1.5V6A2.25 2.25 0 0 1 6.5 3.75Z" /></svg></div>
                        <h3 class="mt-4 text-xl font-black text-slate-950">Todavía no hay cobros registrados</h3>
                        <p class="mt-2 text-sm text-slate-500">Puedes cobrar desde mostrador o llegar desde una atención/tratamiento para acelerar el proceso.</p>
                        <button type="button" onclick="openVentaModal()" class="mt-5 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">Registrar primer cobro</button>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $ventas->links('pagination::tailwind') }}
            </div>
        </section>
    </div>
</div>

@include('ventas.modals.form', [
    'clientes' => $clientes,
    'mascotas' => $mascotas,
    'historias' => $historias,
    'productos' => $productos,
    'tratamientos' => $tratamientos,
    'prefillPayload' => $prefillPayload,
    'shouldOpenCreate' => $shouldOpenCreate,
])
<script src="{{ asset('js/modules/ventas.js') }}?v={{ filemtime(public_path('js/modules/ventas.js')) }}"></script>
</x-app-layout>
