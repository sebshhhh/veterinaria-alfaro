@php
    $clientes = $clientes ?? collect();
    $mascotaStoreErrors = $errors->citaMascotaStore;
    $hasMascotaStoreErrors = $mascotaStoreErrors->any();
@endphp

<div id="citaMascotaModal"
     class="workspace-modal fixed inset-0 z-[60] hidden items-center justify-center overflow-y-auto bg-slate-950/60 px-4 py-6">
    <div class="modal-card flex max-h-[calc(100vh-3rem)] w-full max-w-3xl scale-95 flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 ease-out">
        <div class="shrink-0 border-b border-slate-100 bg-white px-6 py-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-emerald-600">Modulo de citas</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">Nueva mascota</h2>
                    <p id="citaMascotaSummary" class="mt-1 text-sm text-slate-500">Registra a la mascota para dejar lista su primera cita.</p>
                </div>

                <button type="button"
                        onclick="closeCitaMascotaModal()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </div>

        <form id="citaMascotaForm"
              method="POST"
              action="{{ route('citas.mascotas.store') }}"
              enctype="multipart/form-data"
              class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
            @csrf
            <input type="hidden" name="raza" id="cita_modal_raza" value="{{ old('raza') }}" data-current="{{ old('raza') }}">

            @if($hasMascotaStoreErrors)
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach($mascotaStoreErrors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Crea a la mascota y el sistema la dejará preparada para seleccionarla enseguida en la programacion de la cita.
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Cliente o dueño</label>
                    <input id="cita_modal_cliente_search" type="search" placeholder="Buscar por nombre o DNI..." class="mb-3 w-full rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    <select name="cliente_id" id="cita_modal_cliente_id" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('cliente_id') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                        <option value="">Selecciona el cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                {{ $cliente->nombre }}{{ $cliente->dni ? ' - DNI ' . $cliente->dni : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="255" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('nombre') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Nombre de la mascota">
                </div>

                <div>
                    <label for="cita_modal_tipo_animal" class="mb-2 block text-sm font-semibold text-slate-600">Tipo de animal</label>
                    <select id="cita_modal_tipo_animal" name="tipo_animal" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('tipo_animal') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                        <option value="">Seleccione tipo de animal</option>
                        @foreach(['Perro', 'Gato', 'Ave', 'Otro'] as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo_animal') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Sexo</label>
                    <select name="sexo" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('sexo') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                        <option value="">Seleccione sexo</option>
                        <option value="Macho" @selected(old('sexo') === 'Macho')>Macho</option>
                        <option value="Hembra" @selected(old('sexo') === 'Hembra')>Hembra</option>
                    </select>
                </div>

                <div>
                    <label for="cita_modal_raza_select" class="mb-2 block text-sm font-semibold text-slate-600">Raza</label>
                    <select id="cita_modal_raza_select" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('raza') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                        <option value="">Seleccione raza</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Edad</label>
                    <input type="number" name="edad" min="0" max="40" step="1" inputmode="numeric" value="{{ old('edad') }}" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('edad') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Edad en años">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Color</label>
                    <input type="text" name="color" value="{{ old('color') }}" maxlength="100" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('color') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Ej. Negro, caramelo, tricolor">
                </div>

                <div id="cita_modal_input_otro_raza" class="hidden md:col-span-2">
                    <label for="cita_modal_raza_otro" class="mb-2 block text-sm font-semibold text-slate-600">Especifica la raza</label>
                    <input type="text" id="cita_modal_raza_otro" maxlength="255" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Escribe la raza">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Foto</label>
                    <input type="file" name="foto" accept="image/*" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('foto') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeCitaMascotaModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                    Cancelar
                </button>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Guardar mascota
                </button>
            </div>
        </form>
    </div>
</div>

@if ($hasMascotaStoreErrors)
    <script>
        window.citaMascotaModalState = {
            hasErrors: true,
            clienteId: @json(old('cliente_id')),
        };
    </script>
@endif

