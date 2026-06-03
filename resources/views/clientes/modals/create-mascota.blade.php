@php
    $mascotaStoreErrors = $errors->mascotaStore;
    $hasMascotaStoreErrors = $mascotaStoreErrors->any();
    $redirectTo = $redirectTo ?? 'clientes.index';
    $clientes = $clientes ?? collect();
    $allowClienteSelection = $allowClienteSelection ?? false;
    $clienteMode = old('cliente_mode', 'existing');
    $clienteCatalog = $clientes->map(fn ($cliente) => [
        'id' => $cliente->id,
        'nombre' => $cliente->nombre,
        'dni' => $cliente->dni,
    ])->values();
@endphp

<div id="createMascotaModal"
     class="workspace-modal fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-950/60 px-4 py-6">
    <div class="modal-card my-auto flex max-h-[calc(100vh-3rem)] w-full max-w-5xl scale-95 flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 ease-out">
        <div class="shrink-0 border-b border-slate-100 bg-white px-6 py-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-emerald-600">Modulo de mascotas</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">Nueva mascota</h2>
                    <p id="mascotaClienteSummary" class="mt-1 text-sm text-slate-500">Selecciona o registra al cliente y completa los datos del paciente en un solo paso.</p>
                </div>

                <button type="button"
                        onclick="closeMascotaCreateModal()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </div>

        <form id="createMascotaForm"
              method="POST"
              action="{{ route('mascotas.store') }}"
              enctype="multipart/form-data"
              class="min-h-0 flex-1 space-y-6 overflow-y-auto px-6 py-6">
            @csrf
            <input type="hidden" name="cliente_id" id="modal_cliente_id" value="{{ old('cliente_id') }}">
            <input type="hidden" name="redirect_to" value="{{ old('redirect_to', $redirectTo) }}">
            <input type="hidden" name="raza" id="modal_raza" value="{{ old('raza') }}" data-current="{{ old('raza') }}">
            <input type="hidden" name="cliente_mode" id="modal_cliente_mode" value="{{ $clienteMode }}">

            @if($hasMascotaStoreErrors)
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach($mascotaStoreErrors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1.9fr)]">
                <section class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Paso 1</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">Cliente del paciente</h3>
                            <p class="mt-1 text-sm text-slate-500">Puedes elegir uno existente o registrarlo aqui mismo sin salir del flujo.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Flujo rápido
                        </span>
                    </div>

                    @if($allowClienteSelection)
                        <div class="mt-5 grid grid-cols-2 gap-2 rounded-2xl bg-white p-1 shadow-sm">
                            <button type="button"
                                    data-cliente-mode-button="existing"
                                    class="cliente-mode-button inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19h6m-3-3v6M6.5 8a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm0 2C3.462 10 1 12.462 1 15.5V17h11v-1.5C12 12.462 9.538 10 6.5 10Z" />
                                </svg>
                                Cliente existente
                            </button>
                            <button type="button"
                                    data-cliente-mode-button="new"
                                    class="cliente-mode-button inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372A3.375 3.375 0 0 0 21 16.125a3.375 3.375 0 0 0-3.375-3.375H16.5m-8.25 6.378A9.38 9.38 0 0 1 5.625 19.5 3.375 3.375 0 0 1 2.25 16.125 3.375 3.375 0 0 1 5.625 12.75H6.75m10.5-1.5a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0Zm-10.5 0a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm7.5 9.75V16.5m0 0V13.5m0 3h3m-3 0h-3" />
                                </svg>
                                Cliente nuevo
                            </button>
                        </div>

                        <div id="mascotaClienteExistingPanel" class="mt-5 space-y-4">
                            <div>
                                <label for="modal_cliente_search" class="mb-2 block text-sm font-semibold text-slate-600">Buscar cliente</label>
                                <div class="flex items-center rounded-2xl border {{ $mascotaStoreErrors->has('cliente_id') ? 'border-red-500' : 'border-slate-200' }} bg-white px-4 py-3 shadow-sm focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-100">
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                    <input id="modal_cliente_search"
                                           type="text"
                                           placeholder="Buscar por nombre o DNI..."
                                           class="ml-3 w-full border-0 p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0">
                                </div>
                                @if($mascotaStoreErrors->has('cliente_id'))
                                    <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('cliente_id') }}</p>
                                @endif
                            </div>

                            <div id="mascotaClienteSelectedCard" class="hidden rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Cliente seleccionado</p>
                                <p id="mascotaClienteSelectedName" class="mt-2 text-base font-bold text-slate-900"></p>
                                <p id="mascotaClienteSelectedMeta" class="mt-1 text-sm text-slate-600"></p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white">
                                <div class="border-b border-slate-100 px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-700">Resultados rápidos</p>
                                    <p class="mt-1 text-xs text-slate-500">Elige un cliente y continua inmediatamente con la mascota.</p>
                                </div>
                                <div id="mascotaClienteResults" class="max-h-72 space-y-2 overflow-y-auto p-3"></div>
                            </div>
                        </div>

                        <div id="mascotaClienteNewPanel" class="mt-5 grid gap-4 hidden">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-600">DNI del cliente</label>
                                <input type="text"
                                       name="cliente_dni"
                                       value="{{ old('cliente_dni') }}"
                                       inputmode="numeric"
                                       minlength="8"
                                       maxlength="8"
                                       pattern="[0-9]{8}"
                                       placeholder="12345678"
                                       oninput="this.value=this.value.replace(/\D/g,'').slice(0,8)"
                                       class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('cliente_dni') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                @if($mascotaStoreErrors->has('cliente_dni'))
                                    <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('cliente_dni') }}</p>
                                @endif
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-600">Nombre completo</label>
                                <input type="text"
                                       name="cliente_nombre"
                                       value="{{ old('cliente_nombre') }}"
                                       maxlength="255"
                                       autocomplete="name"
                                       class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('cliente_nombre') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                @if($mascotaStoreErrors->has('cliente_nombre'))
                                    <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('cliente_nombre') }}</p>
                                @endif
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-600">Celular</label>
                                    <input type="text"
                                           name="cliente_telefono"
                                           value="{{ old('cliente_telefono') }}"
                                           inputmode="numeric"
                                           minlength="9"
                                           maxlength="9"
                                           pattern="[0-9]{9}"
                                           placeholder="987654321"
                                           autocomplete="tel"
                                           oninput="this.value=this.value.replace(/\D/g,'').slice(0,9)"
                                           class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('cliente_telefono') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    @if($mascotaStoreErrors->has('cliente_telefono'))
                                        <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('cliente_telefono') }}</p>
                                    @endif
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-600">Correo</label>
                                    <input type="email"
                                           name="cliente_email"
                                           value="{{ old('cliente_email') }}"
                                           maxlength="255"
                                           autocomplete="email"
                                           placeholder="cliente@correo.com"
                                           class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('cliente_email') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    @if($mascotaStoreErrors->has('cliente_email'))
                                        <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('cliente_email') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-600">Dirección</label>
                                <input type="text"
                                       name="cliente_direccion"
                                       value="{{ old('cliente_direccion') }}"
                                       maxlength="255"
                                       autocomplete="street-address"
                                       class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('cliente_direccion') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                @if($mascotaStoreErrors->has('cliente_direccion'))
                                    <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('cliente_direccion') }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            Esta mascota quedará vinculada al cliente seleccionado y, al guardarse, volverás al módulo actual.
                        </div>
                    @endif
                </section>

                <section class="rounded-[24px] border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Paso 2</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">Datos de la mascota</h3>
                            <p class="mt-1 text-sm text-slate-500">Completa solo la información esencial para registrar rápido al paciente.</p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-600">Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="255" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('nombre') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Nombre de la mascota">
                            @if($mascotaStoreErrors->has('nombre'))
                                <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('nombre') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="modal_tipo_animal" class="mb-2 block text-sm font-semibold text-slate-600">Tipo de animal</label>
                            <select id="modal_tipo_animal" name="tipo_animal" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('tipo_animal') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                <option value="">Seleccione tipo de animal</option>
                                @foreach(['Perro', 'Gato', 'Ave', 'Otro'] as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tipo_animal') === $tipo)>{{ $tipo }}</option>
                                @endforeach
                            </select>
                            @if($mascotaStoreErrors->has('tipo_animal'))
                                <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('tipo_animal') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-600">Sexo</label>
                            <select name="sexo" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('sexo') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                <option value="">Seleccione sexo</option>
                                <option value="Macho" @selected(old('sexo') === 'Macho')>Macho</option>
                                <option value="Hembra" @selected(old('sexo') === 'Hembra')>Hembra</option>
                            </select>
                            @if($mascotaStoreErrors->has('sexo'))
                                <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('sexo') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="modal_raza_select" class="mb-2 block text-sm font-semibold text-slate-600">Raza</label>
                            <select id="modal_raza_select" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('raza') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                <option value="">Seleccione raza</option>
                            </select>
                            @if($mascotaStoreErrors->has('raza'))
                                <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('raza') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-600">Edad</label>
                            <input type="number" name="edad" min="0" step="1" value="{{ old('edad') }}" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('edad') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Edad en años">
                            @if($mascotaStoreErrors->has('edad'))
                                <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('edad') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-600">Color</label>
                            <input type="text" name="color" value="{{ old('color') }}" maxlength="100" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('color') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Ej. Negro, blanco, caramelo, tricolor">
                            @if($mascotaStoreErrors->has('color'))
                                <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('color') }}</p>
                            @endif
                        </div>

                        <div id="modal_input_otro_raza" class="hidden md:col-span-2">
                            <label for="modal_raza_otro" class="mb-2 block text-sm font-semibold text-slate-600">Especifica la raza</label>
                            <input type="text" id="modal_raza_otro" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Escribe la raza">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-600">Foto</label>
                            <input type="file" name="foto" accept="image/*" class="w-full rounded-2xl border {{ $mascotaStoreErrors->has('foto') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                            @if($mascotaStoreErrors->has('foto'))
                                <p class="input-error mt-1 text-xs text-red-500">{{ $mascotaStoreErrors->first('foto') }}</p>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeMascotaCreateModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
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

@if($allowClienteSelection)
    <script>
        window.mascotaClienteCatalog = @json($clienteCatalog);
    </script>
@endif

@if ($hasMascotaStoreErrors)
    <script>
        window.mascotaCreateModalState = {
            hasErrors: true,
            clienteId: @json(old('cliente_id')),
            clienteMode: @json(old('cliente_mode', 'existing')),
        };
    </script>
@endif

