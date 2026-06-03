(function () {
    const breedCatalog = {
        Perro: ['Labrador', 'Pitbull', 'Pastor Aleman', 'Beagle', 'Pug', 'Otro'],
        Gato: ['Siames', 'Persa', 'Mestizo', 'Angora', 'Otro'],
        Ave: ['Canario', 'Loro', 'Perico', 'Otro'],
        Otro: ['Otro'],
    };

    function getMascotaModalElements() {
        return {
            modal: document.getElementById('mascotaModal'),
            content: document.getElementById('modalContent'),
        };
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatDate(value) {
        if (!value) {
            return '--/--/----';
        }

        const date = new Date(value + 'T00:00:00');

        if (Number.isNaN(date.getTime())) {
            return '--/--/----';
        }

        return date.toLocaleDateString('es-PE');
    }

    function truncate(value, limit = 90) {
        const text = String(value ?? '').trim();

        if (text.length <= limit) {
            return text;
        }

        return text.slice(0, limit - 3).trimEnd() + '...';
    }

    function normalizeText(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function getColorPresentation(colorName) {
        const normalized = normalizeText(colorName);
        const palette = [
            { keyword: 'negro', dot: '#111827', className: 'bg-slate-900 text-white border-slate-800' },
            { keyword: 'blanco', dot: '#e5e7eb', className: 'bg-slate-100 text-slate-700 border-slate-200' },
            { keyword: 'gris', dot: '#6b7280', className: 'bg-slate-200 text-slate-700 border-slate-300' },
            { keyword: 'marron', dot: '#8b5e3c', className: 'bg-amber-100 text-amber-900 border-amber-200' },
            { keyword: 'cafe', dot: '#8b5e3c', className: 'bg-amber-100 text-amber-900 border-amber-200' },
            { keyword: 'caramelo', dot: '#d97706', className: 'bg-amber-50 text-amber-800 border-amber-200' },
            { keyword: 'dorado', dot: '#f59e0b', className: 'bg-yellow-50 text-yellow-800 border-yellow-200' },
            { keyword: 'amarillo', dot: '#eab308', className: 'bg-yellow-50 text-yellow-800 border-yellow-200' },
            { keyword: 'crema', dot: '#f3e8c8', className: 'bg-orange-50 text-orange-700 border-orange-100' },
            { keyword: 'naranja', dot: '#f97316', className: 'bg-orange-50 text-orange-800 border-orange-200' },
            { keyword: 'tricolor', dot: '#7c3aed', className: 'bg-violet-50 text-violet-800 border-violet-200' },
            { keyword: 'manchado', dot: '#0f766e', className: 'bg-teal-50 text-teal-800 border-teal-200' },
            { keyword: 'atigrado', dot: '#b45309', className: 'bg-amber-50 text-amber-800 border-amber-200' },
        ];

        const selected = palette.find((item) => normalized.includes(item.keyword));

        return {
            label: colorName || 'Sin color registrado',
            dot: selected?.dot || '#94a3b8',
            className: selected?.className || 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    function formatCurrency(value) {
        const amount = Number(value ?? 0);

        if (Number.isNaN(amount)) {
            return 'S/ 0.00';
        }

        return 'S/ ' + amount.toFixed(2);
    }

    function renderListFromText(value, emptyText) {
        const items = String(value ?? '')
            .split(/\n|,|;/)
            .map((item) => item.trim())
            .filter(Boolean)
            .slice(0, 4);

        if (!items.length) {
            return `<p class="text-sm text-slate-500">${escapeHtml(emptyText)}</p>`;
        }

        return `<ul class="space-y-2">${items.map((item) => `<li class="flex items-start gap-2 text-sm text-slate-700"><span class="mt-1.5 h-2 w-2 rounded-full bg-blue-500"></span><span>${escapeHtml(item)}</span></li>`).join('')}</ul>`;
    }

    function getTimelineToneClass(tone) {
        if (tone === 'emerald') {
            return 'border-emerald-100 bg-emerald-50 text-emerald-700';
        }

        if (tone === 'amber') {
            return 'border-amber-100 bg-amber-50 text-amber-700';
        }

        if (tone === 'violet') {
            return 'border-violet-100 bg-violet-50 text-violet-700';
        }

        return 'border-blue-100 bg-blue-50 text-blue-700';
    }

    function updateBreedOptions() {
        const tipoAnimal = document.getElementById('tipo_animal');
        const razaSelect = document.getElementById('raza_select');
        const razaInput = document.getElementById('raza');
        const otroContainer = document.getElementById('input_otro_raza');
        const razaOtro = document.getElementById('raza_otro');

        if (!tipoAnimal || !razaSelect || !razaInput || !otroContainer || !razaOtro) {
            return;
        }

        const tipo = tipoAnimal.value;
        const currentBreed = razaInput.dataset.current || razaInput.value || '';
        const breeds = breedCatalog[tipo] || [];
        const normalizedCurrentBreed = currentBreed.trim();
        const hasPresetBreed = breeds.includes(normalizedCurrentBreed);

        razaSelect.innerHTML = '<option value="">Seleccione raza</option>';

        breeds.forEach((breed) => {
            const option = document.createElement('option');
            option.value = breed;
            option.textContent = breed;
            razaSelect.appendChild(option);
        });

        if (normalizedCurrentBreed && !hasPresetBreed) {
            razaSelect.value = 'Otro';
            otroContainer.classList.remove('hidden');
            razaOtro.value = normalizedCurrentBreed;
            razaInput.value = normalizedCurrentBreed;
            return;
        }

        otroContainer.classList.add('hidden');
        razaOtro.value = '';
        razaSelect.value = normalizedCurrentBreed || '';
        razaInput.value = normalizedCurrentBreed || '';
    }

    function bindBreedSelector() {
        const tipoAnimal = document.getElementById('tipo_animal');
        const razaSelect = document.getElementById('raza_select');
        const razaInput = document.getElementById('raza');
        const otroContainer = document.getElementById('input_otro_raza');
        const razaOtro = document.getElementById('raza_otro');
        const mascotaForm = document.getElementById('mascotaForm');

        if (!tipoAnimal || !razaSelect || !razaInput || !otroContainer || !razaOtro || !mascotaForm) {
            return;
        }

        updateBreedOptions();

        tipoAnimal.addEventListener('change', () => {
            razaInput.dataset.current = '';
            updateBreedOptions();
        });

        razaSelect.addEventListener('change', function () {
            if (this.value === 'Otro') {
                otroContainer.classList.remove('hidden');
                razaInput.value = razaOtro.value.trim();
                return;
            }

            otroContainer.classList.add('hidden');
            razaOtro.value = '';
            razaInput.value = this.value;
        });

        razaOtro.addEventListener('input', function () {
            razaInput.value = this.value.trim();
        });

        mascotaForm.addEventListener('submit', () => {
            if (razaSelect.value === 'Otro') {
                razaInput.value = razaOtro.value.trim();
            }
        });
    }

    window.openMascotaModal = function (id) {
        const { modal, content } = getMascotaModalElements();

        if (!modal || !content) {
            return;
        }

        const defaultImage = modal.dataset.defaultImage || '/storage/default.png';
        const clinicName = modal.dataset.clinicName || 'DRA. ALFARO';

        fetch('/mascotas/show-json/' + encodeURIComponent(id))
            .then((response) => response.json())
            .then((data) => {
                const sexoBadge = data.sexo === 'Macho'
                    ? '<span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Macho</span>'
                    : '<span class="inline-flex rounded-full bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700">Hembra</span>';

                const photo = data.foto ? '/storage/' + data.foto : defaultImage;
                const ultimaHistoria = data.ultima_historia;
                const ultimaVacuna = data.ultima_vacuna;
                const lineaTiempo = Array.isArray(data.linea_tiempo) ? data.linea_tiempo : [];
                const proximaCita = data.proxima_cita;
                const colorInfo = getColorPresentation(data.color);
                const tratamientosActivos = Array.isArray(data.tratamientos_activos) ? data.tratamientos_activos : [];
                const recetasRecientes = Array.isArray(data.recetas_recientes) ? data.recetas_recientes : [];
                const controlesActivos = Array.isArray(data.controles_activos) ? data.controles_activos : [];
                const timelineHtml = lineaTiempo.length
                    ? lineaTiempo.map((evento) => {
                        const toneClass = getTimelineToneClass(evento.tone);

                        return `
                            <div class="rounded-2xl border ${toneClass} px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold">${escapeHtml(evento.titulo || 'Evento clinico')}</p>
                                        <p class="mt-1 text-xs opacity-80">${formatDate(evento.fecha)}</p>
                                    </div>
                                    <span class="rounded-full bg-white/85 px-2.5 py-1 text-[11px] font-semibold">${escapeHtml(evento.badge || 'Registro')}</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-600">${escapeHtml(truncate(evento.detalle || 'Sin detalle adicional.', 160))}</p>
                            </div>
                        `;
                    }).join('')
                    : '<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-500">Todavía no hay eventos clinicos registrados para esta mascota.</div>';
                const tratamientosHtml = tratamientosActivos.length
                    ? tratamientosActivos.map((tratamiento) => {
                        const isOpen = !tratamiento.fecha_fin;
                        const resume = isOpen
                            ? 'Tratamiento abierto'
                            : `Hasta ${formatDate(tratamiento.fecha_fin)}`;

                        return `
                            <article class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-amber-800">${formatDate(tratamiento.fecha_inicio)}</p>
                                        <p class="mt-1 text-sm leading-6 text-amber-900">${escapeHtml(truncate(tratamiento.descripcion || 'Sin descripcion registrada.', 130))}</p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700">${escapeHtml(resume)}</span>
                                </div>
                                <div class="mt-3 grid gap-2 md:grid-cols-2 text-sm text-amber-900">
                                    <div class="rounded-xl bg-white/80 px-3 py-2">Profesional: ${escapeHtml(tratamiento.profesional || 'Sin profesional')}</div>
                                    <div class="rounded-xl bg-white/80 px-3 py-2">Costo: ${escapeHtml(formatCurrency(tratamiento.costo))}</div>
                                    <div class="rounded-xl bg-white/80 px-3 py-2 md:col-span-2">Proximo control: ${escapeHtml(tratamiento.proximo_control ? formatDate(tratamiento.proximo_control) : 'Se calcula segun el cierre del tratamiento')}</div>
                                </div>
                            </article>
                        `;
                    }).join('')
                    : '<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-500">No hay tratamientos activos para esta mascota en este momento.</div>';
                const recetasHtml = recetasRecientes.length
                    ? recetasRecientes.map((receta) => `
                        <article class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-violet-800">${formatDate(receta.fecha)}</p>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-violet-700">Receta reciente</span>
                            </div>
                            <div class="mt-3">
                                <p class="text-sm font-semibold text-violet-800">Medicamentos</p>
                                <div class="mt-2">${renderListFromText(receta.medicamentos, 'Sin medicamentos detallados.')}</div>
                            </div>
                            <div class="mt-3">
                                <p class="text-sm font-semibold text-violet-800">Indicaciones</p>
                                <div class="mt-2">${renderListFromText(receta.indicaciones, 'Sin indicaciones detalladas.')}</div>
                            </div>
                        </article>
                    `).join('')
                    : '<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-500">Todavía no hay recetas registradas para esta mascota.</div>';
                const controlesHtml = controlesActivos.length
                    ? controlesActivos.map((seguimiento) => `
                        <article class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-rose-800">${escapeHtml(seguimiento.titulo || 'Control de retorno')}</p>
                                    <p class="mt-1 text-xs text-rose-700">${escapeHtml(seguimiento.tipo_label || 'Control médico')} - ${seguimiento.fecha_proximo_control ? 'Retorno: ' + formatDate(seguimiento.fecha_proximo_control) : 'Inicio: ' + formatDate(seguimiento.fecha_inicio)}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-rose-700">${escapeHtml(seguimiento.estado || 'activo')}</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">${escapeHtml(truncate(seguimiento.motivo || seguimiento.evolucion || seguimiento.notas || 'Seguimiento sin detalle adicional.', 150))}</p>
                        </article>
                    `).join('')
                    : '<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-500">No hay controles activos separados para esta mascota.</div>';
                const alertasClinicas = Array.isArray(data.alertas_clinicas) ? data.alertas_clinicas : [];
                const alertToneClass = (tono) => {
                    if (tono === 'rose') {
                        return 'border-rose-100 bg-rose-50 text-rose-700';
                    }

                    if (tono === 'amber') {
                        return 'border-amber-100 bg-amber-50 text-amber-700';
                    }

                    if (tono === 'emerald') {
                        return 'border-emerald-100 bg-emerald-50 text-emerald-700';
                    }

                    if (tono === 'violet') {
                        return 'border-violet-100 bg-violet-50 text-violet-700';
                    }

                    return 'border-blue-100 bg-blue-50 text-blue-700';
                };
                const alertasHtml = alertasClinicas.length
                    ? alertasClinicas.map((alerta) => `
                        <div class="rounded-2xl border px-4 py-4 ${alertToneClass(alerta.tono)}">
                            <p class="text-sm font-semibold">${escapeHtml(alerta.titulo || 'Seguimiento')}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">${escapeHtml(alerta.detalle || 'Sin detalle adicional.')}</p>
                        </div>
                    `).join('')
                    : '<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-500">No hay alertas clínicas urgentes para esta mascota por ahora.</div>';
                const siguienteAccion = data.siguiente_accion || null;
                const siguienteAccionClass = siguienteAccion ? alertToneClass(siguienteAccion.tone) : 'border-emerald-100 bg-emerald-50 text-emerald-700';

                content.innerHTML = `
                    <div class="space-y-5">
                        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-white via-white to-slate-50 shadow-sm">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-600">${escapeHtml(clinicName)}</p>
                                        <h4 class="mt-2 text-[1.9rem] font-bold text-slate-900">Ficha del paciente</h4>
                                        <p class="mt-1 text-[0.97rem] leading-6 text-slate-500">Perfil médico resumido del paciente, su control de retorno y control preventivo.</p>
                                    </div>
                                    <span class="inline-flex self-start rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">Paciente activo</span>
                                </div>
                            </div>

                            <div class="px-7 py-7" style="display:grid;gap:1.5rem;">
                                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                                    <div style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(360px,0.9fr);gap:1.35rem;align-items:start;">
                                        <div style="display:flex;gap:1.25rem;align-items:flex-start;min-width:0;">
                                            <div style="width:104px;min-width:104px;">
                                                <div style="width:104px;height:104px;border-radius:20px;border:1px solid #dbe4f0;background:#f8fafc;padding:8px;box-shadow:inset 0 1px 2px rgba(15,23,42,.06);">
                                                    <img src="${photo}" alt="Foto de ${escapeHtml(data.nombre)}" style="width:100%;height:100%;border-radius:14px;object-fit:cover;display:block;" onerror="this.onerror=null;this.src='${defaultImage}';">
                                                </div>
                                                <p style="margin:.55rem 0 0;text-align:center;font-size:.72rem;font-weight:700;color:#64748b;letter-spacing:.08em;text-transform:uppercase;">Foto clínica</p>
                                            </div>

                                            <div style="min-width:0;flex:1;">
                                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.55rem;">
                                                    <h4 style="margin:0;font-size:2rem;line-height:1.05;font-weight:800;color:#0f172a;max-width:100%;word-break:break-word;">${escapeHtml(data.nombre)}</h4>
                                                    ${sexoBadge}
                                                </div>
                                                <p style="margin:.4rem 0 0;font-size:1.04rem;font-weight:600;color:#64748b;">${escapeHtml(data.raza || data.tipo_animal || 'Mascota registrada')}</p>
                                                <p style="margin:.3rem 0 0;font-size:.96rem;line-height:1.6;color:#94a3b8;">Paciente registrado en el sistema veterinario.</p>
                                                <div style="margin-top:.85rem;display:flex;flex-wrap:wrap;gap:.55rem;">
                                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold ${colorInfo.className}">
                                                        <span style="display:inline-block;width:.7rem;height:.7rem;border-radius:9999px;background:${colorInfo.dot};"></span>
                                                        ${escapeHtml(colorInfo.label)}
                                                    </span>
                                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">Perfil clínico</span>
                                                </div>

                                                <div style="margin-top:1.1rem;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem;">
                                                    <div style="border-radius:16px;background:#f8fafc;padding:1rem 1.05rem;">
                                                        <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Due�o</span>
                                                        <span style="display:block;margin-top:.35rem;font-size:1rem;line-height:1.45;font-weight:700;color:#0f172a;">${escapeHtml(data.cliente?.nombre || 'Sin cliente asignado')}</span>
                                                    </div>
                                                    <div style="border-radius:16px;background:#f8fafc;padding:1rem 1.05rem;">
                                                        <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">DNI</span>
                                                        <span style="display:block;margin-top:.35rem;font-size:1rem;line-height:1.45;font-weight:700;color:#0f172a;">${escapeHtml(data.cliente?.dni || '-')}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.9rem;">
                                            <div style="border-radius:18px;background:#eff6ff;padding:1rem 1.05rem;">
                                                <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#2563eb;">Próxima cita</span>
                                                <span style="display:block;margin-top:.4rem;font-size:1rem;line-height:1.55;color:#1e3a8a;">${proximaCita?.fecha ? `${formatDate(proximaCita.fecha)}${proximaCita.hora ? ` - ${escapeHtml(String(proximaCita.hora).slice(0, 5))}` : ''}` : 'Sin cita pendiente'}</span>
                                            </div>
                                            <div style="border-radius:18px;background:#ecfdf5;padding:1rem 1.05rem;">
                                                <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#047857;">Atenciones</span>
                                                <span style="display:block;margin-top:.4rem;font-size:1rem;line-height:1.55;color:#065f46;">${Number(data.historias_total || 0)} registradas</span>
                                            </div>
                                            <div style="border-radius:18px;background:#f8fafc;padding:1rem 1.05rem;">
                                                <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#475569;">Registro</span>
                                                <span style="display:block;margin-top:.4rem;font-size:1rem;line-height:1.55;color:#334155;">${formatDate(data.created_at)}</span>
                                            </div>
                                            <div style="grid-column:1 / -1;border-radius:18px;background:#ffffff;border:1px solid #e2e8f0;padding:1.1rem 1.15rem;">
                                                <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.85rem;">
                                                    <div>
                                                        <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Edad</span>
                                                        <span style="display:block;margin-top:.38rem;font-size:1rem;line-height:1.45;font-weight:700;color:#0f172a;">${escapeHtml(data.edad ?? '-')} a�os</span>
                                                    </div>
                                                    <div>
                                                        <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Especie</span>
                                                        <span style="display:block;margin-top:.38rem;font-size:1rem;line-height:1.45;font-weight:700;color:#0f172a;">${escapeHtml(data.tipo_animal || '-')}</span>
                                                    </div>
                                                    <div>
                                                        <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Sexo</span>
                                                        <span style="display:block;margin-top:.38rem;font-size:1rem;line-height:1.45;font-weight:700;color:#0f172a;">${escapeHtml(data.sexo || '-')}</span>
                                                    </div>
                                                    <div>
                                                        <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;">Color</span>
                                                        <span style="display:block;margin-top:.38rem;font-size:1rem;line-height:1.45;font-weight:700;color:#0f172a;">${escapeHtml(colorInfo.label)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Prioridades del paciente</p>
                                            <h5 class="mt-1 text-xl font-bold text-slate-900">Qué toca revisar ahora</h5>
                                            <p class="mt-2 text-sm leading-6 text-slate-500">Esta franja resume lo más importante para no perder el seguimiento de la mascota.</p>
                                        </div>
                                        <span class="inline-flex self-start rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Resumen inteligente</span>
                                    </div>
                                    <div class="mt-4 grid gap-3 xl:grid-cols-3">${alertasHtml}</div>
                                    ${siguienteAccion ? `
                                        <div class="mt-4 rounded-2xl border px-4 py-4 ${siguienteAccionClass}">
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                                <div>
                                                    <p class="text-sm font-semibold">Siguiente acción sugerida</p>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">${escapeHtml(siguienteAccion.detalle || 'Sin detalle adicional.')}</p>
                                                </div>
                                                <a href="${escapeHtml(siguienteAccion.url || '#')}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-100">
                                                    ${escapeHtml(siguienteAccion.label || 'Abrir acción')}
                                                </a>
                                            </div>
                                        </div>
                                    ` : ''}
                                </section>

                                <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                                    <div class="flex flex-col gap-2">
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Acciones clínicas</p>
                                        <h5 class="text-xl font-bold text-slate-900">Que puede hacer el personal desde la ficha</h5>
                                        <p class="text-sm leading-6 text-slate-500">Las acciones se concentran aquí para evitar botones repetidos dentro de cada bloque de información.</p>
                                    </div>
                                    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        <a href="${escapeHtml(data.nueva_historia_url || '#')}" class="inline-flex min-h-[3.2rem] items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700">
                                            Registrar atención
                                        </a>
                                        <a href="${escapeHtml(data.citas_url || '#')}" class="inline-flex min-h-[3.2rem] items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100">
                                            Agendar cita
                                        </a>
                                        <a href="${escapeHtml(data.historial_url || '#')}" class="inline-flex min-h-[3.2rem] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                            Ver historial
                                        </a>
                                        <a href="${escapeHtml(data.controles_url || '#')}" class="inline-flex min-h-[3.2rem] items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                                            Controles
                                        </a>
                                    </div>
                                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                                        <a href="${escapeHtml(data.vacunas_url || '#')}" class="inline-flex min-h-[2.9rem] items-center justify-center rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Vacunas</a>
                                        <a href="${escapeHtml(data.tratamientos_url || '#')}" class="inline-flex min-h-[2.9rem] items-center justify-center rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">Tratamientos</a>
                                        <a href="${escapeHtml(data.recetas_url || '#')}" class="inline-flex min-h-[2.9rem] items-center justify-center rounded-2xl border border-violet-100 bg-violet-50/70 px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">Recetas</a>
                                    </div>
                                </section>

                                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,1fr)]">
                                    <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                            <div>
                                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Datos del paciente</p>
                                                <h5 class="mt-1 text-xl font-bold text-slate-900">Información general y clínica</h5>
                                            </div>
                                            <span class="inline-flex self-start rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Solo lectura</span>
                                        </div>

                                        <dl class="mt-5 grid gap-3.5 sm:grid-cols-2 xl:grid-cols-3 text-sm text-slate-600">
                                            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                                <dt class="font-medium text-slate-400">Due&ntilde;o</dt>
                                                <dd class="mt-1.5 text-[1rem] leading-6 font-semibold text-slate-900">${escapeHtml(data.cliente?.nombre || 'Sin cliente asignado')}</dd>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                                <dt class="font-medium text-slate-400">DNI</dt>
                                                <dd class="mt-1.5 text-[1rem] leading-6 font-semibold text-slate-900">${escapeHtml(data.cliente?.dni || '-')}</dd>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                                <dt class="font-medium text-slate-400">Edad</dt>
                                                <dd class="mt-1.5 text-[1rem] leading-6 font-semibold text-slate-900">${escapeHtml(data.edad ?? '-')} a&ntilde;os</dd>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                                <dt class="font-medium text-slate-400">Especie</dt>
                                                <dd class="mt-1.5 text-[1rem] leading-6 font-semibold text-slate-900">${escapeHtml(data.tipo_animal || '-')}</dd>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                                <dt class="font-medium text-slate-400">Raza</dt>
                                                <dd class="mt-1.5 text-[1rem] leading-6 font-semibold text-slate-900">${escapeHtml(data.raza || '-')}</dd>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                                                <dt class="font-medium text-slate-400">Sexo</dt>
                                                <dd class="mt-1.5 text-[1rem] leading-6 font-semibold text-slate-900">${escapeHtml(data.sexo || '-')}</dd>
                                            </div>
                                        </dl>
                                    </section>

                                    <div class="space-y-4">
                                        <section class="rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-5 shadow-sm">
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Última atención</p>
                                            <p class="mt-2 text-base font-semibold text-slate-900">${ultimaHistoria ? formatDate(ultimaHistoria.fecha) : 'Sin atención registrada'}</p>
                                            <p class="mt-3 text-sm leading-6 text-slate-600">${escapeHtml(truncate(ultimaHistoria?.diagnostico || ultimaHistoria?.observaciones || 'Cuando registres la primera atención clínica, aparecerá aquí un resumen médico.', 180))}</p>
                                            ${ultimaHistoria?.tratamiento ? `<p class="mt-3 text-sm font-medium text-amber-700">Tratamiento: ${escapeHtml(truncate(ultimaHistoria.tratamiento, 100))}</p>` : ''}
                                            ${ultimaHistoria?.receta ? `<p class="mt-2 text-sm font-medium text-emerald-700">Receta: ${escapeHtml(truncate(ultimaHistoria.receta, 100))}</p>` : ''}
                                        </section>

                                        <section class="rounded-[24px] border border-blue-100 bg-blue-50 px-5 py-5 shadow-sm">
                                            <div class="flex flex-col gap-3">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">Control preventivo</p>
                                                    <p class="mt-2 text-base font-semibold text-slate-900">${ultimaVacuna ? escapeHtml(ultimaVacuna.nombre) : 'Sin vacuna reciente registrada'}</p>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">${ultimaVacuna ? `Aplicada el ${formatDate(ultimaVacuna.fecha_aplicacion)}` : 'Cuando registres una vacuna desde la atenci?n, aqu? se resumir? la ?ltima dosis.'}</p>
                                                    ${ultimaVacuna?.proxima_dosis ? `<p class="mt-2 text-sm font-medium text-blue-700">Próxima dosis: ${formatDate(ultimaVacuna.proxima_dosis)}</p>` : ''}
                                                    ${data.ultima_venta?.fecha ? `<p class="mt-2 text-sm font-medium text-emerald-700">Última venta: ${formatDate(data.ultima_venta.fecha)} - ${escapeHtml(formatCurrency(data.ultima_venta.total))}</p>` : ''}
                                                </div>
                                                <p class="rounded-2xl bg-white/80 px-4 py-3 text-sm leading-6 text-blue-900">El detalle preventivo se consulta desde la barra de acciones clínicas.</p>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="grid gap-6 xl:grid-cols-3">
                            <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Controles de retorno</p>
                                        <h5 class="mt-1 text-xl font-bold text-slate-900">Controles activos</h5>
                                    </div>
                                    <span class="inline-flex self-start rounded-full bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700">Controles de retorno</span>
                                </div>
                                <div class="mt-4 space-y-3">${controlesHtml}</div>
                            </section>

                            <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Tratamiento activo</p>
                                        <h5 class="mt-1 text-xl font-bold text-slate-900">Tratamientos activos</h5>
                                    </div>
                                    <span class="inline-flex self-start rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">Manejo activo</span>
                                </div>
                                <div class="mt-4 space-y-3">${tratamientosHtml}</div>
                            </section>

                            <section class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Prescripción médica</p>
                                        <h5 class="mt-1 text-xl font-bold text-slate-900">Recetas recientes</h5>
                                    </div>
                                    <span class="inline-flex self-start rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700">Indicaciones</span>
                                </div>
                                <div class="mt-4 space-y-3">${recetasHtml}</div>
                            </section>
                        </section>

                        <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Seguimiento</p>
                                    <h5 class="mt-1 text-xl font-bold text-slate-900">Línea de tiempo clínica</h5>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Orden cronológico más reciente</span>
                            </div>
                            <div class="mt-4 space-y-3">${timelineHtml}</div>
                        </section>
                    </div>`;
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            })
            .catch(() => {
                content.innerHTML = '<p class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">No se pudo cargar la ficha de la mascota.</p>';
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            });
    };

    window.closeMascotaModal = function () {
        const { modal } = getMascotaModalElements();

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal } = getMascotaModalElements();

        if (modal) {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    window.closeMascotaModal();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                window.closeMascotaModal();
            }
        });

        bindBreedSelector();
    });
})();


