@php
    $hasAttendErrors = $errors->attendCita->any();
    $vacunaCatalogo = $vacunaCatalogo ?? collect();
    $serviciosCatalogo = $serviciosCatalogo ?? collect();
    $selectedVacunaValue = old('vacuna_nombre_select');
    $customVacunaValue = old('vacuna_nombre_custom');
    $hasServicioContent = old('servicio_producto_id') || old('precio_servicio');
    $hasSeguimientoContent = old('requiere_seguimiento') || old('seguimiento_fecha_proximo_control') || old('seguimiento_dias_retorno') || old('seguimiento_motivo') || old('seguimiento_notas');

    if (!$selectedVacunaValue && !$customVacunaValue && old('vacuna_nombre')) {
        if ($vacunaCatalogo->contains(old('vacuna_nombre'))) {
            $selectedVacunaValue = old('vacuna_nombre');
        } else {
            $selectedVacunaValue = '__custom__';
            $customVacunaValue = old('vacuna_nombre');
        }
    }
@endphp

<div id="attendCitaModal"
     data-open-on-load="{{ $hasAttendErrors ? 'true' : 'false' }}"
     data-default-image="{{ asset('storage/default.png') }}"
     class="workspace-modal fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 px-2 py-2 sm:px-4 sm:py-4"
     aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="flex max-h-[calc(100dvh-1.5rem)] w-full max-w-[96rem] flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="shrink-0 border-b border-slate-100 bg-white px-5 py-4 sm:px-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Atención clínica</p>
                        <h3 id="attendCitaTitle" class="mt-1 text-[1.65rem] font-black tracking-tight text-slate-900">Atender cita</h3>
                        <p id="attendCitaSummary" class="mt-1 text-sm leading-6 text-slate-500">Flujo compacto: confirma paciente, tipo de atención y completa solo los bloques usados.</p>
                    </div>

                    <button type="button"
                            onclick="closeAttendCitaModal()"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700"
                            aria-label="Cerrar modal">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="attendCitaForm"
                  method="POST"
                  action="{{ url('citas/__ID__/atender') }}"
                  data-action-template="{{ url('citas/__ID__/atender') }}"
                  data-default-date="{{ now()->format('Y-m-d') }}"
                  class="flex min-h-0 flex-1 flex-col">
                @csrf
                <input type="hidden" name="cita_id" id="attend_cita_id" value="{{ old('cita_id') }}">

                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5">
                    <div class="space-y-4">
                        @if($hasAttendErrors)
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                @foreach($errors->attendCita->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <div class="grid gap-4 xl:grid-cols-[250px_minmax(0,1fr)]">
                            <aside class="space-y-4">
                                <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Paciente</p>
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Ficha activa</span>
                                    </div>

                                    <div class="mt-3 rounded-[20px] bg-white p-3 shadow-sm">
                                        <img id="attendMascotaPhoto" src="{{ asset('storage/default.png') }}" alt="Mascota" class="h-28 w-full rounded-[18px] object-cover" onerror="this.onerror=null;this.src='{{ asset('storage/default.png') }}';">
                                        <div class="mt-3">
                                            <p id="attendMascotaName" class="text-xl font-black text-slate-900">Mascota seleccionada</p>
                                            <p id="attendMascotaOwner" class="mt-1 text-sm text-slate-500">El due&ntilde;o aparecerá aquí.</p>
                                        </div>
                                        <div class="mt-3 grid gap-2 text-xs text-slate-600">
                                            <div class="rounded-2xl bg-blue-50 px-3 py-2">
                                                <span class="font-semibold text-blue-700">Fecha:</span>
                                                <span id="attendMascotaFecha" class="ml-1">--/--/----</span>
                                            </div>
                                            <div class="rounded-2xl bg-amber-50 px-3 py-2">
                                                <span class="font-semibold text-amber-700">Hora:</span>
                                                <span id="attendMascotaHora" class="ml-1">--:--</span>
                                            </div>
                                            <div class="rounded-2xl bg-emerald-50 px-3 py-2">
                                                <span class="font-semibold text-emerald-700">Profesional:</span>
                                                <span id="attendMascotaVet" class="ml-1">Pendiente</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[24px] border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-800">
                                    Al guardar, esta atención se agregará al historial clínico del paciente y la cita pasará a completada automáticamente.
                                </div>
                            </aside>

                            <div class="space-y-4">
                                <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                    <div class="flex flex-col gap-2 border-b border-slate-100 pb-4 xl:flex-row xl:items-end xl:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Datos de la atención</p>
                                            <h4 class="mt-1 text-xl font-black text-slate-900">Registro principal</h4>
                                            <p class="mt-1 text-sm leading-6 text-slate-500">Confirma lo esencial para alimentar el historial de la mascota.</p>
                                        </div>
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                            <p class="font-semibold text-slate-700">Regla del flujo</p>
                                            <div class="mt-2 space-y-1 text-xs leading-5 text-slate-500">
                                                <p><span class="font-semibold text-slate-700">Vacuna:</span> completa vacuna si se aplicó dosis.</p>
                                                <p><span class="font-semibold text-slate-700">Servicio:</span> usa solo servicio si fue baño, corte u otro servicio.</p>
                                                <p><span class="font-semibold text-slate-700">Tratamiento, receta y control:</span> son opcionales.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                                        <div class="xl:col-span-2 rounded-[20px] border border-blue-100 bg-blue-50/40 p-3">
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Ruta rápida de atención</p>
                                            <p class="mt-1 text-sm text-slate-500">Selecciona el flujo y el sistema muestra solo lo necesario para completar la cita.</p>
                                            <div class="mt-3 grid gap-2 sm:grid-cols-2 2xl:grid-cols-4">
                                                <button type="button" class="attention-type-shortcut" data-attend-type="consulta" aria-pressed="false">
                                                    <span class="attention-type-shortcut__icon attention-type-shortcut__icon--emerald">+</span>
                                                    <span>
                                                        <span class="block font-bold">Consulta</span>
                                                        <span class="block text-xs text-slate-500">Clínica general</span>
                                                    </span>
                                                </button>
                                                <button type="button" class="attention-type-shortcut" data-attend-type="vacunacion" aria-pressed="false">
                                                    <span class="attention-type-shortcut__icon attention-type-shortcut__icon--blue">V</span>
                                                    <span>
                                                        <span class="block font-bold">Vacuna</span>
                                                        <span class="block text-xs text-slate-500">Aplica dosis</span>
                                                    </span>
                                                </button>
                                                <button type="button" class="attention-type-shortcut" data-attend-type="servicio" aria-pressed="false">
                                                    <span class="attention-type-shortcut__icon attention-type-shortcut__icon--cyan">S</span>
                                                    <span>
                                                        <span class="block font-bold">Servicio</span>
                                                        <span class="block text-xs text-slate-500">Precio directo</span>
                                                    </span>
                                                </button>
                                                <button type="button" class="attention-type-shortcut" data-attend-type="control" aria-pressed="false">
                                                    <span class="attention-type-shortcut__icon attention-type-shortcut__icon--rose">C</span>
                                                    <span>
                                                        <span class="block font-bold">Control</span>
                                                        <span class="block text-xs text-slate-500">Seguimiento</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 xl:col-span-2">
                                            <div>
                                                <label for="attend_tipo_atencion" class="mb-2 block text-sm font-semibold text-slate-600">Tipo de atención</label>
                                                <select id="attend_tipo_atencion" name="tipo_atencion" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                                    <option value="consulta" @selected(old('tipo_atencion', 'consulta') === 'consulta')>Consulta</option>
                                                    <option value="vacunacion" @selected(old('tipo_atencion') === 'vacunacion')>Vacunación</option>
                                                    <option value="control" @selected(old('tipo_atencion') === 'control')>Control</option>
                                                    <option value="desparasitacion" @selected(old('tipo_atencion') === 'desparasitacion')>Desparasitación</option>
                                                    <option value="servicio" @selected(old('tipo_atencion') === 'servicio')>Servicio</option>
                                                    <option value="otro" @selected(old('tipo_atencion') === 'otro')>Otro</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="historia_fecha" class="mb-2 block text-sm font-semibold text-slate-600">Fecha de atención</label>
                                                <input id="historia_fecha" type="date" name="historia_fecha" value="{{ old('historia_fecha', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                            </div>
                                            <div>
                                                <label for="attend_peso" class="mb-2 block text-sm font-semibold text-slate-600">Peso (kg)</label>
                                                <input id="attend_peso" type="number" step="0.01" min="0" max="200" name="peso" value="{{ old('peso') }}" placeholder="Ej. 14.20" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                            </div>
                                            <div>
                                                <label for="attend_temperatura" class="mb-2 block text-sm font-semibold text-slate-600">Temperatura (°C)</label>
                                                <input id="attend_temperatura" type="number" step="0.1" min="30" max="45" name="temperatura" value="{{ old('temperatura') }}" placeholder="Ej. 38.5" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                            </div>
                                        </div>

                                        <div id="attendClinicalNarrative" class="grid gap-4 xl:grid-cols-2 xl:col-span-2">
                                            <div>
                                                <label for="diagnostico" class="mb-2 block text-sm font-semibold text-slate-600">Diagnostico</label>
                                                <textarea id="diagnostico" name="diagnostico" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Describe el diagnóstico principal de la consulta...">{{ old('diagnostico') }}</textarea>
                                            </div>
                                            <div>
                                                <label for="observaciones" class="mb-2 block text-sm font-semibold text-slate-600">Observaciones</label>
                                                <textarea id="observaciones" name="observaciones" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Notas medicas, hallazgos o seguimiento recomendado...">{{ old('observaciones') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                    <div class="flex flex-col gap-2 border-b border-slate-100 pb-4 xl:flex-row xl:items-end xl:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Bloques opcionales</p>
                                            <h4 class="mt-1 text-xl font-black text-slate-900">Bloques opcionales de la atención</h4>
                                            <p class="mt-1 text-sm leading-6 text-slate-500">Abre únicamente lo que se realizó: servicio, vacuna, tratamiento, receta o control.</p>
                                        </div>
                                        <div class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Todos los bloques son opcionales</div>
                                    </div>

                                    <div id="attendClinicalBlocks" class="mt-4 grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
                                        <section id="attendServicioBlock" class="min-w-0 rounded-[22px] border border-cyan-100 bg-cyan-50/50 p-4 shadow-sm" data-optional-block="servicio" data-open="{{ $hasServicioContent ? 'true' : 'false' }}">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M7 4h10v6H7V4Zm-1 6h12v10H6V10Zm12-3h2a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1h-2" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-lg font-black text-slate-900">Servicio</h4>
                                                        <p class="text-sm text-slate-500">Usa este bloque para baño, corte de pelo u otros servicios no clínicos.</p>
                                                    </div>
                                                </div>
                                                <button type="button" data-optional-toggle="servicio" class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-2 text-xs font-semibold text-cyan-700 transition hover:border-cyan-300 hover:bg-cyan-100">
                                                    <span data-optional-label>Completar</span>
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-4 space-y-3 hidden" data-optional-body>
                                                <div>
                                                    <label for="attend_servicio_producto_id" class="mb-2 block text-sm font-semibold text-slate-600">Servicio realizado</label>
                                                    <select id="attend_servicio_producto_id" name="servicio_producto_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100">
                                                        <option value="">Selecciona un servicio</option>
                                                        @foreach($serviciosCatalogo as $servicio)
                                                            <option value="{{ $servicio->id }}" data-price="{{ $servicio->precio }}" @selected(old('servicio_producto_id') == $servicio->id)>
                                                                {{ $servicio->nombre }} | S/ {{ number_format((float) $servicio->precio, 2) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <p id="attendServicioHint" class="mt-2 text-xs leading-5 text-slate-500">Si la mascota ya tuvo este servicio antes, el sistema puede sugerirlo automáticamente.</p>
                                                </div>
                                                <div>
                                                    <label for="attend_precio_servicio" class="mb-2 block text-sm font-semibold text-slate-600">Precio del servicio</label>
                                                    <input id="attend_precio_servicio" type="number" step="0.01" min="0" name="precio_servicio" value="{{ old('precio_servicio') }}" placeholder="Ej. 35.00" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100">
                                                </div>
                                            </div>
                                        </section>

                                        <section class="min-w-0 rounded-[22px] border border-emerald-100 bg-emerald-50/50 p-4 shadow-sm" data-optional-block="vacuna" data-open="{{ $selectedVacunaValue || $customVacunaValue || old('vacuna_proxima_dosis') ? 'true' : 'false' }}">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-lg font-black text-slate-900">Vacuna aplicada</h4>
                                                        <p class="text-sm text-slate-500">Completa este bloque solo si se aplicó una vacuna.</p>
                                                    </div>
                                                </div>
                                                <button type="button" data-optional-toggle="vacuna" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100">
                                                    <span data-optional-label>Completar</span>
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-4 space-y-3 hidden" data-optional-body>
                                                <div>
                                                    <label for="attend_vacuna_nombre_select" class="mb-2 block text-sm font-semibold text-slate-600">Vacuna</label>
                                                    <select name="vacuna_nombre_select" id="attend_vacuna_nombre_select" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                                        <option value="">Selecciona una vacuna</option>
                                                        @foreach($vacunaCatalogo as $vacunaNombre)
                                                            <option value="{{ $vacunaNombre }}" @selected($selectedVacunaValue === $vacunaNombre)>{{ $vacunaNombre }}</option>
                                                        @endforeach
                                                        <option value="__custom__" @selected($selectedVacunaValue === '__custom__')>Otra vacuna</option>
                                                    </select>
                                                    <p id="attendVacunaSelectedLabel" class="mt-2 hidden text-sm font-medium leading-5 text-emerald-700"></p>
                                                </div>
                                                <div id="attend_vacuna_custom_wrap" class="{{ $selectedVacunaValue === '__custom__' ? '' : 'hidden' }}">
                                                    <label for="attend_vacuna_nombre_custom" class="mb-2 block text-sm font-semibold text-slate-600">Nombre personalizado</label>
                                                    <input type="text" name="vacuna_nombre_custom" id="attend_vacuna_nombre_custom" value="{{ $customVacunaValue }}" placeholder="Escribe el nombre de la vacuna" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                                </div>
                                                <div>
                                                    <label for="attend_vacuna_fecha_aplicacion" class="mb-2 block text-sm font-semibold text-slate-600">Fecha de aplicacion</label>
                                                    <input id="attend_vacuna_fecha_aplicacion" type="date" name="vacuna_fecha_aplicacion" value="{{ old('vacuna_fecha_aplicacion', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                                    <p class="mt-1.5 text-sm leading-5 text-slate-500">Día en que se aplicó la vacuna durante esta atención.</p>
                                                </div>
                                                <div class="rounded-2xl border border-emerald-100 bg-white/80 p-4">
                                                    <label class="inline-flex items-start gap-3 text-sm text-slate-700">
                                                        <input id="attend_programar_proxima_vacuna" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('vacuna_proxima_dosis'))>
                                                        <span>
                                                            <span class="block font-semibold text-slate-900">Programar próxima vacuna</span>
                                                            <span class="mt-1 block text-xs leading-5 text-slate-500">Activa esta opción si quieres dejar pendiente la siguiente dosis en el módulo Vacunas.</span>
                                                        </span>
                                                    </label>
                                                    <div id="attend_vacuna_proxima_dosis_wrap" class="{{ old('vacuna_proxima_dosis') ? '' : 'hidden' }} mt-4">
                                                        <label for="attend_vacuna_proxima_dosis" class="mb-2 block text-sm font-semibold text-slate-600">Fecha de la próxima vacuna</label>
                                                        <input id="attend_vacuna_proxima_dosis" type="date" name="vacuna_proxima_dosis" value="{{ old('vacuna_proxima_dosis') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <section class="min-w-0 rounded-[22px] border border-amber-100 bg-amber-50/50 p-4 shadow-sm" data-optional-block="tratamiento" data-open="{{ old('tratamiento_descripcion') || old('tratamiento_fecha_fin') || (float) old('tratamiento_costo', 0) > 0 ? 'true' : 'false' }}">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M6.75 7.5l.818 10.636A1.5 1.5 0 0 0 9.063 19.5h5.874a1.5 1.5 0 0 0 1.495-1.364L17.25 7.5m-8.25 0V6A1.5 1.5 0 0 1 10.5 4.5h3A1.5 1.5 0 0 1 15 6v1.5" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-lg font-black text-slate-900">Tratamiento</h4>
                                                        <p class="text-sm text-slate-500">Usalo si la consulta deja un tratamiento activo o indicado.</p>
                                                    </div>
                                                </div>
                                                <button type="button" data-optional-toggle="tratamiento" class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-3 py-2 text-xs font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                                                    <span data-optional-label>Completar</span>
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-4 space-y-3 hidden" data-optional-body>
                                                <div>
                                                    <label for="attend_tratamiento_descripcion" class="mb-2 block text-sm font-semibold text-slate-600">Descripción del tratamiento</label>
                                                    <textarea id="attend_tratamiento_descripcion" name="tratamiento_descripcion" rows="4" placeholder="Descripción del tratamiento..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">{{ old('tratamiento_descripcion') }}</textarea>
                                                </div>
                                                <div>
                                                    <label for="attend_tratamiento_costo" class="mb-2 block text-sm font-semibold text-slate-600">Costo</label>
                                                    <input id="attend_tratamiento_costo" type="number" step="0.01" min="0" name="tratamiento_costo" value="{{ old('tratamiento_costo', 0) }}" placeholder="Costo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                                </div>
                                                <div>
                                                    <label for="attend_tratamiento_fecha_inicio" class="mb-2 block text-sm font-semibold text-slate-600">Fecha de inicio</label>
                                                    <input id="attend_tratamiento_fecha_inicio" type="date" name="tratamiento_fecha_inicio" value="{{ old('tratamiento_fecha_inicio', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                                </div>
                                                <div>
                                                    <label for="attend_tratamiento_fecha_fin" class="mb-2 block text-sm font-semibold text-slate-600">Fecha de fin</label>
                                                    <input id="attend_tratamiento_fecha_fin" type="date" name="tratamiento_fecha_fin" value="{{ old('tratamiento_fecha_fin') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
                                                    <p class="mt-1.5 text-sm leading-5 text-slate-500">Dejala vacia si el tratamiento sigue activo.</p>
                                                </div>
                                            </div>
                                        </section>

                                        <section class="min-w-0 rounded-[22px] border border-blue-100 bg-blue-50/50 p-4 shadow-sm" data-optional-block="receta" data-open="{{ old('receta_medicamentos') || old('receta_indicaciones') ? 'true' : 'false' }}">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3.75h7.5L19.5 8.75v11.5A1.75 1.75 0 0 1 17.75 22H7.25A1.75 1.75 0 0 1 5.5 20.25V5.5A1.75 1.75 0 0 1 7.25 3.75Z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-lg font-black text-slate-900">Receta</h4>
                                                        <p class="text-sm text-slate-500">Completa este bloque si se deja medicación o indicaciones formales.</p>
                                                    </div>
                                                </div>
                                                <button type="button" data-optional-toggle="receta" class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                                    <span data-optional-label>Completar</span>
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-4 space-y-3 hidden" data-optional-body>
                                                <div>
                                                    <label for="attend_receta_medicamentos" class="mb-2 block text-sm font-semibold text-slate-600">Medicamentos</label>
                                                    <textarea id="attend_receta_medicamentos" name="receta_medicamentos" rows="4" placeholder="Medicamentos usados o recetados..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('receta_medicamentos') }}</textarea>
                                                </div>
                                                <div>
                                                    <label for="attend_receta_indicaciones" class="mb-2 block text-sm font-semibold text-slate-600">Indicaciones</label>
                                                    <textarea id="attend_receta_indicaciones" name="receta_indicaciones" rows="4" placeholder="Indicaciones de uso, dosis y frecuencia..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('receta_indicaciones') }}</textarea>
                                                </div>
                                            </div>
                                        </section>

                                        <section class="min-w-0 rounded-[22px] border border-rose-100 bg-rose-50/50 p-4 shadow-sm" data-optional-block="seguimiento" data-open="{{ $hasSeguimientoContent ? 'true' : 'false' }}">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2.5-5 4 10 2.5-5H21" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-lg font-black text-slate-900">Próximo control</h4>
                                                        <p class="text-sm text-slate-500">Define solo si esta atención necesita retorno posterior. El sistema generará el seguimiento y su cita de control.</p>
                                                    </div>
                                                </div>
                                                <button type="button" data-optional-toggle="seguimiento" class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                                                    <span data-optional-label>Completar</span>
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-4 space-y-3 hidden" data-optional-body>
                                                <div>
                                                    <label class="inline-flex items-start gap-3 text-sm text-slate-700">
                                                        <input id="attend_requiere_seguimiento" type="checkbox" name="requiere_seguimiento" value="1" class="mt-1 h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500" @checked(old('requiere_seguimiento') || $hasSeguimientoContent)>
                                                        <span>
                                                            <span class="block font-semibold text-slate-900">Requiere seguimiento posterior</span>
                                                            <span class="mt-1 block text-xs leading-5 text-slate-500">Actívalo cuando el paciente deba volver para revisar evolución, síntomas o respuesta al tratamiento.</span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div>
                                                    <label for="attend_seguimiento_motivo" class="mb-2 block text-sm font-semibold text-slate-600">Motivo del control</label>
                                                    <textarea id="attend_seguimiento_motivo" name="seguimiento_motivo" rows="4" placeholder="Ej. Control por diarrea, revisión de herida o respuesta a medicación..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-100">{{ old('seguimiento_motivo') }}</textarea>
                                                </div>
                                                <div class="grid gap-4 md:grid-cols-3">
                                                    <div>
                                                        <label for="attend_seguimiento_dias_retorno" class="mb-2 block text-sm font-semibold text-slate-600">Días para volver</label>
                                                        <input id="attend_seguimiento_dias_retorno" type="number" min="1" max="365" name="seguimiento_dias_retorno" value="{{ old('seguimiento_dias_retorno') }}" placeholder="Ej. 3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-100">
                                                    </div>
                                                    <div>
                                                        <label for="attend_seguimiento_fecha_proximo_control" class="mb-2 block text-sm font-semibold text-slate-600">Fecha del próximo control</label>
                                                        <input id="attend_seguimiento_fecha_proximo_control" type="date" name="seguimiento_fecha_proximo_control" value="{{ old('seguimiento_fecha_proximo_control') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-100">
                                                    </div>
                                                    <div>
                                                        <label for="attend_seguimiento_hora_proximo_control" class="mb-2 block text-sm font-semibold text-slate-600">Hora sugerida</label>
                                                        <input id="attend_seguimiento_hora_proximo_control" type="time" name="seguimiento_hora_proximo_control" value="{{ old('seguimiento_hora_proximo_control', '09:00') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-100">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="attend_seguimiento_notas" class="mb-2 block text-sm font-semibold text-slate-600">Notas de control</label>
                                                    <textarea id="attend_seguimiento_notas" name="seguimiento_notas" rows="4" placeholder="Indicaciones para revisar en la siguiente visita..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-rose-500 focus:ring-4 focus:ring-rose-100">{{ old('seguimiento_notas') }}</textarea>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 border-t border-slate-100 bg-white px-6 py-5 sm:px-7">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">Se guardará la atención, se completará la cita y solo se conectarán los bloques que llenaste.</p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button"
                                    onclick="closeAttendCitaModal()"
                                    class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span id="attendCitaSubmitLabel">Guardar atencion y completar cita</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($hasAttendErrors)
    <script>
        window.attendCitaModalState = {
            hasErrors: true,
            citaId: @json(old('cita_id')),
        };
    </script>
@endif

