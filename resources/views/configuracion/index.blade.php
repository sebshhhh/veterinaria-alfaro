<x-app-layout>
@php
    $values = $settings->flatten(1)->pluck('valor', 'clave');
    $groupTitles = [
        'general' => ['title' => 'Datos de la clínica', 'description' => 'Información institucional visible y reutilizable dentro del sistema.'],
        'operacion' => ['title' => 'Reglas operativas', 'description' => 'Parámetros que influyen en agenda, alertas, controles y stock.'],
        'documentos' => ['title' => 'Documentos', 'description' => 'Datos base para importes y textos institucionales.'],
    ];
@endphp

<div class="module-page">
    <div class="module-page__inner space-y-5">
        <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm">
                        <i data-feather="settings" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Configuración</p>
                        <h1 class="mt-1 text-3xl font-extrabold text-slate-950">Ajustes del sistema</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                            Define los datos institucionales y las reglas que el sistema usará para automatizar alertas, controles, agenda y stock.
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                    Cambios aplicados al guardar
                </div>
            </div>
        </section>

        @if(isset($errors) && $errors->configuracionUpdate->any())
            <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                <p class="font-extrabold">Revisa los datos ingresados</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->configuracionUpdate->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('configuracion.update') }}" class="grid gap-5 xl:grid-cols-[280px_1fr]">
            @csrf
            @method('PATCH')

            <aside class="h-fit rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                <p class="px-2 text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Panel</p>
                <nav class="mt-4 space-y-2 text-sm font-bold text-slate-600">
                    <a href="#general" class="block rounded-2xl bg-blue-50 px-4 py-3 text-blue-700">Datos de la clínica</a>
                    <a href="#operacion" class="block rounded-2xl px-4 py-3 hover:bg-slate-50">Reglas operativas</a>
                    <a href="#documentos" class="block rounded-2xl px-4 py-3 hover:bg-slate-50">Documentos</a>
                    <a href="#tecnico" class="block rounded-2xl px-4 py-3 hover:bg-slate-50">Información técnica</a>
                </nav>
                <button type="submit" class="mt-5 w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                    Guardar configuración
                </button>
            </aside>

            <div class="space-y-5">
                <section id="general" class="rounded-[24px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">General</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Datos de la clínica</h2>
                        <p class="mt-1 text-sm text-slate-500">Estos datos identifican a la veterinaria dentro del sistema.</p>
                    </div>
                    <div class="grid gap-4 p-6 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Nombre de la clínica</span>
                            <input name="clinic_name" value="{{ old('clinic_name', $values['clinic_name'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Subtítulo institucional</span>
                            <input name="clinic_subtitle" value="{{ old('clinic_subtitle', $values['clinic_subtitle'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Teléfono</span>
                            <input name="clinic_phone" value="{{ old('clinic_phone', $values['clinic_phone'] ?? '') }}" inputmode="numeric" maxlength="9" placeholder="9 dígitos" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Correo institucional</span>
                            <input name="clinic_email" type="email" value="{{ old('clinic_email', $values['clinic_email'] ?? '') }}" placeholder="correo@clinica.com" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="space-y-2 md:col-span-2">
                            <span class="text-sm font-bold text-slate-700">Dirección</span>
                            <textarea name="clinic_address" rows="2" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('clinic_address', $values['clinic_address'] ?? '') }}</textarea>
                        </label>
                    </div>
                </section>

                <section id="operacion" class="rounded-[24px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Operación</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Reglas automáticas</h2>
                        <p class="mt-1 text-sm text-slate-500">Estos valores sí se usan en alertas, agenda de retorno y control de productos.</p>
                    </div>
                    <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Duración estándar de cita</span>
                            <select name="appointment_interval" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach([15, 20, 30, 45, 60] as $minutes)
                                    <option value="{{ $minutes }}" @selected((string) old('appointment_interval', $values['appointment_interval'] ?? '30') === (string) $minutes)>{{ $minutes }} minutos</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Hora por defecto para controles</span>
                            <input name="default_control_time" type="time" value="{{ old('default_control_time', $values['default_control_time'] ?? '09:00') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Stock mínimo</span>
                            <input name="low_stock_threshold" type="number" min="0" max="999" value="{{ old('low_stock_threshold', $values['low_stock_threshold'] ?? '5') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Alerta previa de vacunas</span>
                            <input name="vaccine_alert_days" type="number" min="1" max="30" value="{{ old('vaccine_alert_days', $values['vaccine_alert_days'] ?? '3') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <span class="text-xs text-slate-500">Días antes de mostrar vacunas próximas.</span>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Alerta previa de controles</span>
                            <input name="control_alert_days" type="number" min="1" max="30" value="{{ old('control_alert_days', $values['control_alert_days'] ?? '7') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <span class="text-xs text-slate-500">Días antes de mostrar controles próximos.</span>
                        </label>
                    </div>
                </section>

                <section id="documentos" class="rounded-[24px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Documentos</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Datos para salida administrativa</h2>
                    </div>
                    <div class="grid gap-4 p-6 md:grid-cols-[220px_1fr]">
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Moneda</span>
                            <input name="currency_symbol" value="{{ old('currency_symbol', $values['currency_symbol'] ?? 'S/') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-bold text-slate-700">Pie institucional</span>
                            <input name="receipt_footer" value="{{ old('receipt_footer', $values['receipt_footer'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                    </div>
                </section>

                <section id="tecnico" class="rounded-[24px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Solo lectura</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Información técnica</h2>
                    </div>
                    <div class="grid divide-y divide-slate-100 md:grid-cols-4 md:divide-x md:divide-y-0">
                        @foreach($systemInfo as $item)
                            <div class="px-6 py-4">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ $item['label'] }}</p>
                                <p class="mt-2 text-sm font-extrabold text-slate-950">{{ $item['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
