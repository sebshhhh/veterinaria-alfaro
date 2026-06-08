@php
    $isEdit = $isEdit ?? false;
    $mascota = $mascota ?? null;
    $cliente = $cliente ?? $mascota?->cliente;
    $pageTag = $pageTag ?? ($isEdit ? 'Edicion de mascota' : 'Registro de mascota');
    $pageTitle = $pageTitle ?? ($isEdit ? 'Editar mascota' : 'Nueva mascota');
    $pageDescription = $pageDescription ?? ($cliente ? 'Estas gestionando la mascota de ' . $cliente->nombre . '.' : 'Completa los datos de la mascota.');
    $backUrl = $backUrl ?? route('mascotas.index');
    $backLabel = $backLabel ?? 'Volver';
    $formAction = $formAction ?? ($isEdit && $mascota ? route('mascotas.update', $mascota) : route('mascotas.store'));
    $submitLabel = $submitLabel ?? ($isEdit ? 'Actualizar mascota' : 'Guardar mascota');
    $currentPhoto = old('foto_actual', $mascota?->foto);
@endphp

<x-app-layout>
<div class="min-h-screen bg-slate-50/80">
    <div class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">{{ $pageTag }}</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $pageTitle }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $pageDescription }}</p>
                </div>

                <a href="{{ $backUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    {{ $backLabel }}
                </a>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form id="mascotaForm" method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <input type="hidden" name="cliente_id" value="{{ old('cliente_id', $cliente?->id) }}">
                <input type="hidden" name="foto_actual" value="{{ $currentPhoto }}">

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-600">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $mascota?->nombre) }}" maxlength="255" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Nombre de la mascota">
                    </div>

                    <div>
                        <label for="tipo_animal" class="mb-2 block text-sm font-semibold text-slate-600">Tipo de animal</label>
                        <select id="tipo_animal" name="tipo_animal" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Seleccione tipo de animal</option>
                            @foreach(['Perro', 'Gato', 'Ave', 'Otro'] as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo_animal', $mascota?->tipo_animal) === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-600">Sexo</label>
                        <select name="sexo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Seleccione sexo</option>
                            <option value="Macho" @selected(old('sexo', $mascota?->sexo) === 'Macho')>Macho</option>
                            <option value="Hembra" @selected(old('sexo', $mascota?->sexo) === 'Hembra')>Hembra</option>
                        </select>
                    </div>

                    <div>
                        <label for="raza_select" class="mb-2 block text-sm font-semibold text-slate-600">Raza</label>
                        <select id="raza_select" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Seleccione raza</option>
                        </select>
                        <input type="hidden" name="raza" id="raza" value="{{ old('raza', $mascota?->raza) }}" data-current="{{ old('raza', $mascota?->raza) }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-600">Edad</label>
                        <input type="number" name="edad" min="0" max="40" step="1" inputmode="numeric" value="{{ old('edad', $mascota?->edad) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Edad en años">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-600">Color</label>
                        <input type="text" name="color" value="{{ old('color', $mascota?->color) }}" maxlength="100" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Ej. Negro, blanco, caramelo, tricolor">
                    </div>

                    <div id="input_otro_raza" class="hidden md:col-span-2">
                        <label for="raza_otro" class="mb-2 block text-sm font-semibold text-slate-600">Especifica la raza</label>
                        <input type="text" id="raza_otro" maxlength="255" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Escribe la raza">
                    </div>

                    @if($isEdit && $currentPhoto)
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-600">Foto actual</label>
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-3">
                                <img src="{{ \App\Support\PhotoUrl::make($currentPhoto) }}" alt="Foto actual de {{ $mascota?->nombre }}" class="h-52 w-full rounded-[18px] object-cover sm:h-60">
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Si no seleccionas una nueva foto, se mantiene la actual.</p>
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-600">{{ $isEdit ? 'Cambiar foto' : 'Foto' }}</label>
                        <input type="file" name="foto" accept="image/*" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ $backUrl }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        Cancelar
                    </a>

                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
<script src="{{ asset('js/modules/mascotas.js') }}"></script>
</x-app-layout>


