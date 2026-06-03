<x-app-layout>
@php
    $stats = $stats ?? [];
@endphp

<div class="module-page">
    <div class="module-page__inner lg:h-[calc(100vh-11rem)] lg:overflow-hidden">
        <section class="module-shell flex h-full flex-col overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 shadow-inner shadow-blue-100/80">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372A3.375 3.375 0 0 0 21 16.125a3.375 3.375 0 0 0-3.375-3.375H16.5m-8.25 6.378A9.38 9.38 0 0 1 5.625 19.5 3.375 3.375 0 0 1 2.25 16.125 3.375 3.375 0 0 1 5.625 12.75H6.75m10.5-1.5a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0Zm-10.5 0a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm10.5 0v.75a6.75 6.75 0 0 1-13.5 0v-.75" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-semibold uppercase tracking-[0.22em] text-blue-600">Gestión de clientes</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Clientes</h1>
                            <p class="mt-1 text-base text-slate-500">Registra, actualiza y consulta los datos del dueño de cada paciente.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                            <span class="font-semibold text-slate-700">{{ $stats['total'] ?? 0 }}</span>
                            clientes registrados
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                            <span class="font-semibold">{{ $stats['con_mascotas'] ?? 0 }}</span>
                            con mascotas
                        </div>
                        <button type="button" onclick="openCreateModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Nuevo cliente
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-hidden px-6 py-5 sm:px-8">
                <div class="flex h-full flex-col gap-5">
                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            <span class="font-semibold">{{ $stats['nuevos'] ?? 0 }}</span>
                            nuevos del mes
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            <span class="font-semibold">{{ $stats['mascotas'] ?? 0 }}</span>
                            mascotas relacionadas
                        </div>
                    </div>

                    <form method="GET" action="{{ route('clientes.index') }}" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                            <div class="w-full xl:max-w-2xl">
                                <label for="search" class="mb-2 block text-base font-semibold text-slate-600">Buscar cliente</label>
                                <div class="flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100">
                                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                    <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, DNI o telefono..." class="ml-3 w-full border-0 p-0 text-base text-slate-700 placeholder:text-slate-400 focus:ring-0">
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                                <button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">Buscar</button>
                                <a href="{{ route('clientes.index') }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="flex-1 min-h-0 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                        <div class="flex h-full flex-col">
                            <div class="overflow-auto flex-1 min-h-0">
                                <table class="min-w-full text-base text-slate-600">
                                    <thead class="sticky top-0 z-10 bg-slate-50 text-left text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">
                                        <tr>
                                            <th class="px-6 py-4">Cliente</th>
                                            <th class="px-6 py-4">DNI</th>
                                            <th class="px-6 py-4">Teléfono</th>
                                            <th class="px-6 py-4">Mascotas</th>
                                            <th class="px-6 py-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse($clientes as $cliente)
                                            @php
                                                $clientInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($cliente->nombre, 0, 1));
                                            @endphp
                                            <tr class="align-top transition hover:bg-slate-50/70">
                                                <td class="px-6 py-5">
                                                    <div class="flex items-start gap-4">
                                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-base font-bold text-slate-700">
                                                            {{ $clientInitial }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="truncate text-base font-semibold text-slate-900">{{ $cliente->nombre }}</p>
                                                            <p class="mt-1 truncate text-base text-slate-500">{{ $cliente->email ?: 'Sin correo registrado' }}</p>
                                                            <p class="mt-1 truncate text-base text-slate-400">{{ $cliente->direccion }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-5 text-base font-semibold text-slate-700">{{ $cliente->dni }}</td>
                                                <td class="px-6 py-5 text-base text-slate-600">{{ $cliente->telefono }}</td>
                                                <td class="px-6 py-5">
                                                    <span class="inline-flex min-w-11 items-center justify-center rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700">{{ $cliente->mascotas_count }}</span>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <div class="flex flex-wrap gap-2.5">
                                                        <button type="button" onclick="toggleMascotas({{ $cliente->id }})" class="inline-flex items-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 5.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM17.25 8.25a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM8.25 9.75a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM20.25 13.5a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0ZM14.856 20.523c-.64.473-1.664.727-2.856.727-1.192 0-2.216-.254-2.856-.727-.657-.486-1.03-1.159-1.03-1.898 0-.82.456-1.578 1.258-2.122.802-.545 1.92-.878 3.128-.878 1.208 0 2.326.333 3.128.878.802.544 1.258 1.302 1.258 2.122 0 .739-.373 1.412-1.03 1.898Z" /></svg>
                                                            Mascotas
                                                        </button>
                                                        <button type="button" onclick='editCliente(@json($cliente))' class="inline-flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.651-1.652a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM15 6.349 17.651 9" /></svg>
                                                            Actualizar datos
                                                        </button>
                                                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" onsubmit="return confirm('Eliminar cliente?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-9.75 0V6A1.5 1.5 0 0 1 9.75 4.5h4.5A1.5 1.5 0 0 1 15.75 6v1.5m-8.25 0v10.125A1.875 1.875 0 0 0 9.375 19.5h5.25A1.875 1.875 0 0 0 16.5 17.625V7.5" /></svg>
                                                                Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr id="mascotas-{{ $cliente->id }}" class="hidden bg-slate-50/80">
                                                <td colspan="5" class="px-6 pb-5 pt-0">
                                                    <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                                        <p class="text-base font-semibold text-slate-600">Mascotas registradas</p>
                                                        <div class="mt-3 flex flex-wrap gap-2.5">
                                                            @forelse($cliente->mascotas as $mascota)
                                                                <button type="button" onclick="openModal({{ $mascota->id }})" class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                                                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                                                    {{ $mascota->nombre }}
                                                                </button>
                                                            @empty
                                                                <span class="text-base text-slate-500">Este cliente todavía no tiene mascotas registradas.</span>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-16 text-center">
                                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 text-xl font-bold">C</div>
                                                    <h3 class="mt-5 text-xl font-semibold text-slate-900">No encontramos clientes con esos filtros</h3>
                                                    <p class="mt-2 text-sm text-slate-500">Prueba ajustando la búsqueda o registra al cliente desde Nueva atención cuando llegue a consulta.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="border-t border-slate-100 bg-white px-6 py-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div class="text-base text-slate-500">
                                        Mostrando {{ $clientes->firstItem() ?? 0 }} a {{ $clientes->lastItem() ?? 0 }} de {{ $clientes->total() }} clientes
                                    </div>
                                    <div class="clientes-pagination flex justify-center md:justify-end">
                                        {{ $clientes->links('pagination::tailwind') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@include('clientes.modals.create')
@include('clientes.modals.mascota')
@include('clientes.modals.create-mascota')
<script src="{{ asset('js/modules/clientes.js') }}"></script>
</x-app-layout>




