@php
    $applyVacunaErrors = isset($errors) ? $errors->aplicarVacuna : new \Illuminate\Support\ViewErrorBag();
    $oldApplySeguimientoId = old('apply_seguimiento_id');
    $applyAction = filled($oldApplySeguimientoId)
        ? route('seguimientos.aplicar-vacuna', $oldApplySeguimientoId)
        : '#';
@endphp

<div id="seguimientoVacunaModal"
     data-open-on-load="{{ $applyVacunaErrors->any() ? 'true' : 'false' }}"
     class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-4 py-6"
     aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="modal-card flex max-h-[calc(100vh-3rem)] w-full max-w-3xl scale-95 flex-col overflow-hidden rounded-[30px] border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 ease-out">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 via-white to-blue-50 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-100">
                            <i data-feather="shield" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-600">Control preventivo</p>
                            <h3 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Aplicar vacuna programada</h3>
                            <p id="seguimientoVacunaSummary" class="mt-1 text-sm leading-6 text-slate-500">Confirma la aplicación y el sistema actualizará vacunas, historial, cita y controles.</p>
                        </div>
                    </div>
                    <button type="button"
                            onclick="closeSeguimientoVacunaModal()"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-white hover:text-slate-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="seguimientoVacunaForm" method="POST" action="{{ $applyAction }}" class="flex min-h-0 flex-1 flex-col">
                @csrf
                @method('PATCH')
                <input type="hidden" name="apply_seguimiento_id" id="seguimientoVacunaId" value="{{ old('apply_seguimiento_id') }}">

                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                    @if($applyVacunaErrors->any())
                        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                            @foreach($applyVacunaErrors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-[240px_1fr]">
                        <aside class="rounded-[24px] border border-emerald-100 bg-emerald-50/80 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700">Paciente</p>
                            <h4 id="seguimientoVacunaMascota" class="mt-2 text-xl font-extrabold text-slate-950">Paciente seleccionado</h4>
                            <p id="seguimientoVacunaCliente" class="mt-1 text-sm font-semibold text-slate-500">Propietario</p>
                            <div class="mt-4 rounded-2xl bg-white px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Vacuna</p>
                                <p id="seguimientoVacunaNombre" class="mt-1 text-sm font-extrabold text-slate-950">Vacuna programada</p>
                            </div>
                            <div class="mt-3 rounded-2xl bg-white px-3 py-3 shadow-sm">
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Programada para</p>
                                <p id="seguimientoVacunaFecha" class="mt-1 text-sm font-extrabold text-slate-950">--/--/----</p>
                            </div>
                        </aside>

                        <section class="space-y-4">
                            <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-sm font-black text-white">1</span>
                                    <div>
                                        <h4 class="text-base font-extrabold text-slate-950">Confirmar aplicación</h4>
                                        <p class="mt-1 text-sm leading-6 text-slate-500">Este paso cambiará la vacuna de programada a aplicada y guardará el evento en el historial clínico.</p>
                                    </div>
                                </div>
                                <label class="mt-4 block space-y-2">
                                    <span class="text-sm font-bold text-slate-700">Fecha de aplicación</span>
                                    <input id="seguimientoVacunaFechaAplicacion" type="date" name="fecha_aplicacion" value="{{ old('fecha_aplicacion', now()->toDateString()) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                                </label>
                            </div>

                            <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-sm font-black text-white">2</span>
                                    <div>
                                        <h4 class="text-base font-extrabold text-slate-950">Próximo control preventivo</h4>
                                        <p class="mt-1 text-sm leading-6 text-slate-500">Si la mascota necesita otra dosis, indica la fecha y el sistema creará el siguiente control automáticamente.</p>
                                    </div>
                                </div>
                                <label class="mt-4 block space-y-2">
                                    <span class="text-sm font-bold text-slate-700">Próxima dosis opcional</span>
                                    <input id="seguimientoVacunaProximaDosis" type="date" name="proxima_dosis" value="{{ old('proxima_dosis') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                                </label>
                            </div>

                            <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-slate-700 text-sm font-black text-white">3</span>
                                    <div>
                                        <h4 class="text-base font-extrabold text-slate-950">Observación del control</h4>
                                        <p class="mt-1 text-sm leading-6 text-slate-500">Usa este campo solo si deseas dejar una nota de evolución o reacción inicial.</p>
                                    </div>
                                </div>
                                <label class="mt-4 block space-y-2">
                                    <span class="text-sm font-bold text-slate-700">Evolución u observación</span>
                                    <textarea id="seguimientoVacunaEvolucion" name="evolucion" rows="4" placeholder="Ej. Vacuna aplicada sin reacción inmediata. Se programa refuerzo si corresponde." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100">{{ old('evolucion') }}</textarea>
                                </label>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-white px-6 py-4">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" onclick="closeSeguimientoVacunaModal()" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-emerald-100 transition hover:bg-emerald-700">
                            <i data-feather="check-circle" class="h-4 w-4"></i>
                            Aplicar vacuna y sincronizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
