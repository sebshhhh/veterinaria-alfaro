@php
    $redirectTo = $redirectTo ?? 'clientes.index';
@endphp

<div id="createClienteModal"
     class="workspace-modal fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-950/60 px-4 py-6">

    <div class="modal-card my-auto w-full max-w-2xl scale-95 overflow-hidden rounded-[28px] border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 ease-out">
        <div class="border-b border-slate-100 bg-white px-6 py-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-blue-600">Modulo de clientes</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">Cliente</h2>
                    <p class="mt-1 text-sm text-slate-500">Registro y edición de dueños.</p>
                </div>

                <button type="button"
                        onclick="closeCreateModal()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </div>

        <form id="clienteForm"
              method="POST"
              action="{{ route('clientes.store') }}"
              data-store-action="{{ route('clientes.store') }}"
              data-update-template="{{ url('clientes/__ID__') }}"
              class="space-y-5 px-6 py-6">
            @csrf
            <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">
            <input type="hidden" name="redirect_to" value="{{ old('redirect_to', $redirectTo) }}">

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-600">DNI</label>
                <input type="text" name="dni" value="{{ old('dni') }}" inputmode="numeric" minlength="8" maxlength="8" pattern="[0-9]{8}" placeholder="12345678" oninput="this.value=this.value.replace(/\D/g,'').slice(0,8)" class="w-full rounded-2xl border {{ $errors->has('dni') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                @error('dni')
                    <p class="input-error mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-600">Nombre completo</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="255" autocomplete="name" class="w-full rounded-2xl border {{ $errors->has('nombre') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                @error('nombre')
                    <p class="input-error mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" inputmode="numeric" minlength="9" maxlength="9" pattern="[0-9]{9}" placeholder="987654321" autocomplete="tel" oninput="this.value=this.value.replace(/\D/g,'').slice(0,9)" class="w-full rounded-2xl border {{ $errors->has('telefono') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    @error('telefono')
                        <p class="input-error mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-600">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" placeholder="cliente@correo.com" class="w-full rounded-2xl border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    @error('email')
                        <p class="input-error mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-600">Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion') }}" maxlength="255" autocomplete="street-address" class="w-full rounded-2xl border {{ $errors->has('direccion') ? 'border-red-500' : 'border-slate-200' }} px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                @error('direccion')
                    <p class="input-error mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeCreateModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                    Cancelar
                </button>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Guardar cliente
                </button>
            </div>
        </form>
    </div>
</div>

@if ($errors->any())
    <script>
        window.clienteModalState = {
            hasErrors: true,
            isEdit: @json(old('_method') === 'PUT'),
            editingId: @json(old('editing_id')),
        };
    </script>
@endif

