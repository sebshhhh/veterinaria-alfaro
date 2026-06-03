(function () {
    const recipeTemplates = {
        dermatitis: {
            meds: 'Prednisona 5 mg\nShampoo medicado\nSuplemento dermatologico',
            notes: 'Prednisona cada 24 horas por 5 dias.\nAplicar shampoo medicado 3 veces por semana.\nMantener control de rascado y dieta indicada.',
        },
        otitis: {
            meds: 'Gotas oticas\nLimpiador auricular',
            notes: 'Aplicar gotas oticas cada 12 horas por 7 dias.\nRealizar limpieza auricular suave antes de cada aplicacion.',
        },
        gastrointestinal: {
            meds: 'Probiotico\nAntiemetico\nProtector gastrico',
            notes: 'Administrar antiemetico segun indicacion profesional.\nDar protector gastrico antes de alimento.\nMantener dieta blanda por 3 dias.',
        },
        postoperatorio: {
            meds: 'Antibiotico\nAntiinflamatorio\nProtector de herida',
            notes: 'Administrar medicacion cada 12 horas segun dosis indicada.\nMantener collar isabelino y vigilar herida quirurgica.\nControl en 5 a 7 dias.',
        },
        desparasitacion: {
            meds: 'Antiparasitario interno\nAntiparasitario externo',
            notes: 'Administrar antiparasitario segun peso.\nRepetir segun calendario preventivo y control veterinario.',
        },
        control: {
            meds: 'Suplemento o medicacion segun evaluacion',
            notes: 'Seguir las indicaciones del profesional y asistir al control programado.',
        },
    };

    function els() {
        const form = document.getElementById('recetaForm');
        const modal = document.getElementById('recetaModal');

        return {
            modal,
            card: modal ? modal.querySelector('.modal-card') : null,
            form,
            title: document.getElementById('recetaModalTitle'),
            summary: document.getElementById('recetaModalSummary'),
            submit: document.getElementById('recetaSubmitLabel'),
            historiaSearch: document.getElementById('receta_historia_search'),
            historia: document.getElementById('receta_historia_id'),
            template: document.getElementById('receta_template'),
            meds: document.getElementById('receta_medicamentos'),
            notes: document.getElementById('receta_indicaciones'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            photo: document.getElementById('recetaMascotaPhoto'),
            petName: document.getElementById('recetaMascotaName'),
            petOwner: document.getElementById('recetaMascotaOwner'),
            petType: document.getElementById('recetaMascotaType'),
            petBreed: document.getElementById('recetaMascotaBreed'),
            petColor: document.getElementById('recetaMascotaColor'),
            petSex: document.getElementById('recetaMascotaSex'),
            historiaDate: document.getElementById('recetaHistoriaDate'),
            historiaPeso: document.getElementById('recetaHistoriaPeso'),
            historiaTemperatura: document.getElementById('recetaHistoriaTemperatura'),
            diagnostico: document.getElementById('recetaDiagnostico'),
            previewMeds: document.getElementById('recetaPreviewMeds'),
            previewNotes: document.getElementById('recetaPreviewNotes'),
        };
    }

    function normalizeSearchText(value = '') {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function ensureMethod(form) {
        let method = form.querySelector('input[name="_method"]');

        if (!method) {
            method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            form.appendChild(method);
        }

        return method;
    }

    function removeMethod(form) {
        const method = form.querySelector('input[name="_method"]');

        if (method) {
            method.remove();
        }
    }

    function updateAction(form, id) {
        return (form?.dataset.updateTemplate || '/recetas/__ID__').replace('__ID__', encodeURIComponent(id));
    }

    function splitText(value) {
        return String(value || '')
            .split(/\n|;|,/)
            .map((item) => item.trim())
            .filter(Boolean)
            .slice(0, 6);
    }

    function renderList(container, items, emptyText, dotClass) {
        if (!container) {
            return;
        }

        if (!items.length) {
            container.innerHTML = '<p class="text-sm text-slate-500">' + emptyText + '</p>';
            return;
        }

        container.innerHTML = items.map((item) => (
            '<div class="flex items-start gap-2 text-sm leading-6 text-slate-700">' +
                '<span class="mt-2 h-2 w-2 shrink-0 rounded-full ' + dotClass + '"></span>' +
                '<span>' + item.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;') + '</span>' +
            '</div>'
        )).join('');
    }

    function setCardVisibility(isOpen) {
        const { card } = els();

        if (!card) {
            return;
        }

        if (isOpen) {
            card.classList.remove('opacity-0', 'scale-95');
            card.classList.add('opacity-100', 'scale-100');
            return;
        }

        card.classList.remove('opacity-100', 'scale-100');
        card.classList.add('opacity-0', 'scale-95');
    }

    function syncPreview() {
        const {
            historia,
            historiaSearch,
            title,
            summary,
            form,
            photo,
            petName,
            petOwner,
            petType,
            petBreed,
            petColor,
            petSex,
            historiaDate,
            historiaPeso,
            historiaTemperatura,
            diagnostico,
            submit,
            meds,
            notes,
            previewMeds,
            previewNotes,
        } = els();

        if (!historia || !title || !form) {
            return;
        }

        const selected = historia.options[historia.selectedIndex];
        const isEdit = form.dataset.mode === 'edit';

        title.textContent = isEdit ? 'Editar receta' : 'Nueva receta';
        submit.textContent = isEdit ? 'Actualizar receta' : 'Guardar receta';

        renderList(previewMeds, splitText(meds?.value), 'Cuando escribas los medicamentos, aqui se ordenaran mejor.', 'bg-blue-500');
        renderList(previewNotes, splitText(notes?.value), 'Aqui veras una lectura mas clara de las indicaciones del paciente.', 'bg-slate-500');

        if (!selected || !historia.value) {
            if (historiaSearch && form.dataset.syncingSearch !== 'true') {
                historiaSearch.value = '';
            }
            photo.src = '/storage/default.png';
            petName.textContent = 'Selecciona una historia';
            petOwner.textContent = 'El propietario aparecera aqui.';
            petType.textContent = 'Paciente veterinario';
            petBreed.textContent = 'Raza pendiente';
            petColor.textContent = 'Color pendiente';
            petSex.textContent = 'Sexo pendiente';
            historiaDate.textContent = 'Sin fecha';
            historiaPeso.textContent = 'Sin dato';
            historiaTemperatura.textContent = 'Sin dato';
            diagnostico.textContent = 'Se mostrara el diagnostico asociado.';
            summary.textContent = 'Selecciona la atencion clinica de origen y organiza la formula con claridad.';
            return;
        }

        photo.src = selected.dataset.foto || '/storage/default.png';
        petName.textContent = selected.dataset.mascota || 'Mascota seleccionada';
        petOwner.textContent = selected.dataset.cliente || 'Sin propietario registrado';
        petType.textContent = selected.dataset.tipo || 'Paciente veterinario';
        petBreed.textContent = selected.dataset.raza || 'Raza sin registrar';
        petColor.textContent = selected.dataset.color || 'Color sin registrar';
        petSex.textContent = selected.dataset.sexo || 'Sexo sin registrar';
        historiaDate.textContent = selected.dataset.fecha || 'Sin fecha';
        historiaPeso.textContent = selected.dataset.peso ? selected.dataset.peso + ' kg' : 'Sin dato';
        historiaTemperatura.textContent = selected.dataset.temperatura ? selected.dataset.temperatura + ' C' : 'Sin dato';
        diagnostico.textContent = selected.dataset.diagnostico || 'Sin diagnostico registrado';

        if (historiaSearch && form.dataset.syncingSearch !== 'true') {
            historiaSearch.value = [
                selected.dataset.mascota,
                selected.dataset.cliente,
                selected.dataset.fecha,
            ].filter(Boolean).join(' - ');
        }

        summary.textContent = isEdit
            ? 'Actualizando la receta de ' + petName.textContent + '.'
            : 'Registrando una receta para ' + petName.textContent + '.';
    }

    function filterHistorias() {
        const { form, historiaSearch, historia } = els();

        if (!form || !historiaSearch || !historia) {
            return;
        }

        const term = normalizeSearchText(historiaSearch.value);
        form.dataset.syncingSearch = 'true';

        Array.from(historia.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const searchable = normalizeSearchText([
                option.textContent,
                option.dataset.mascota,
                option.dataset.cliente,
                option.dataset.dni,
                option.dataset.diagnostico,
                option.dataset.servicio,
                option.dataset.fecha,
            ].join(' '));

            option.hidden = Boolean(term) && !searchable.includes(term);
        });

        form.dataset.syncingSearch = 'false';
    }

    function resetForm(historiaId = '') {
        const { form, historiaSearch, historia, template, meds, notes, editingId } = els();

        if (!form) {
            return;
        }

        form.action = form.dataset.storeAction || form.action;
        form.dataset.mode = 'create';
        removeMethod(form);

        if (editingId) editingId.value = '';
        if (historiaSearch) {
            historiaSearch.value = '';
            filterHistorias();
        }
        if (historia) historia.value = historiaId ? String(historiaId) : '';
        if (template) template.value = '';
        if (meds) meds.value = '';
        if (notes) notes.value = '';
    }

    function applyTemplate() {
        const { template, meds, notes } = els();

        if (!template || !template.value) {
            return;
        }

        const config = recipeTemplates[template.value];

        if (!config) {
            return;
        }

        if (meds && !meds.value.trim()) {
            meds.value = config.meds;
        }

        if (notes && !notes.value.trim()) {
            notes.value = config.notes;
        }
    }

    function open() {
        const { modal } = els();

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            setCardVisibility(true);
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    window.printRecetaCard = function (payload) {
        const printWindow = window.open('', '_blank', 'width=980,height=760');

        if (!printWindow) {
            return;
        }

        const meds = splitText(payload.medicamentos || '').map((item) => '<li>' + escapeHtml(item) + '</li>').join('');
        const notes = splitText(payload.indicaciones || '').map((item) => '<li>' + escapeHtml(item) + '</li>').join('');
        const diagnosis = escapeHtml(payload.diagnosis || 'Sin diagnostico registrado');
        const petName = escapeHtml(payload.petName || 'Paciente veterinario');
        const petType = escapeHtml(payload.petType || 'Sin especie');
        const petBreed = escapeHtml(payload.petBreed || 'Sin raza');
        const petColor = escapeHtml(payload.petColor || 'Sin color');
        const petSex = escapeHtml(payload.petSex || 'Sin sexo');
        const owner = escapeHtml(payload.owner || 'Sin propietario registrado');
        const ownerDni = escapeHtml(payload.ownerDni || 'Sin DNI');
        const ownerPhone = escapeHtml(payload.ownerPhone || 'Sin telefono');
        const ownerAddress = escapeHtml(payload.ownerAddress || 'Sin direccion');
        const date = escapeHtml(payload.date || 'Sin fecha');
        const historyType = escapeHtml(payload.historyType || 'Consulta');
        const serviceName = escapeHtml(payload.serviceName || 'No corresponde');
        const weight = payload.weight ? escapeHtml(String(payload.weight) + ' kg') : 'Sin dato';
        const temperature = payload.temperature ? escapeHtml(String(payload.temperature) + ' C') : 'Sin dato';
        const logo = escapeHtml(payload.logo || '');

        printWindow.document.write(`
            <html lang="es">
                <head>
                    <title>Receta veterinaria</title>
                    <style>
                        * { box-sizing: border-box; }
                        body { font-family: "Segoe UI", Arial, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
                        .page { padding: 28px; }
                        .sheet { background: #ffffff; border: 1px solid #dbe4f0; border-radius: 24px; overflow: hidden; }
                        .header { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 24px 28px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-bottom: 1px solid #dbe4f0; }
                        .brand { display: flex; align-items: center; gap: 18px; }
                        .brand img { width: 64px; height: 64px; object-fit: contain; border-radius: 18px; background: #ffffff; padding: 8px; border: 1px solid #dbe4f0; }
                        .eyebrow { color: #2563eb; font-size: 12px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; }
                        h1 { margin: 8px 0 0; font-size: 30px; line-height: 1.1; }
                        .subtitle { margin-top: 6px; color: #475569; font-size: 14px; }
                        .stamp { min-width: 170px; border: 1px solid #bfdbfe; border-radius: 18px; background: #ffffff; padding: 14px 16px; text-align: right; }
                        .stamp span { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .12em; font-weight: 700; }
                        .stamp strong { display: block; margin-top: 8px; font-size: 18px; color: #0f172a; }
                        .body { padding: 24px 28px 28px; }
                        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
                        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 12px 14px; min-height: 88px; }
                        .box span { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .10em; font-weight: 700; }
                        .box strong { display: block; margin-top: 8px; font-size: 15px; color: #0f172a; line-height: 1.45; }
                        .section { margin-top: 22px; }
                        .section h3 { margin: 0 0 12px; font-size: 15px; text-transform: uppercase; letter-spacing: .12em; color: #334155; }
                        .panel { border: 1px solid #e2e8f0; border-radius: 18px; padding: 16px 18px; background: #ffffff; }
                        ul { margin: 0; padding-left: 18px; line-height: 1.8; color: #1e293b; }
                        .clinical-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
                        .clinical-grid .box { min-height: auto; }
                        .hero { margin-top: 18px; border: 1px solid #dbe4f0; border-radius: 20px; padding: 18px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%); }
                        .hero h2 { margin: 0; font-size: 24px; line-height: 1.15; }
                        .hero p { margin: 8px 0 0; color: #475569; font-size: 14px; }
                        .signature { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-top: 28px; }
                        .signature .line { border-top: 1px solid #94a3b8; padding-top: 10px; text-align: center; font-size: 13px; color: #475569; }
                        .footer { margin-top: 18px; font-size: 12px; color: #64748b; text-align: center; }
                    </style>
                </head>
                <body>
                    <div class="page">
                        <div class="sheet">
                            <div class="header">
                                <div class="brand">
                                    <img src="${logo}" alt="Logo DRA. ALFARO">
                                    <div>
                                        <div class="eyebrow">DRA. ALFARO</div>
                                        <h1>Receta veterinaria</h1>
                                        <p class="subtitle">Formula clinica emitida como apoyo del seguimiento y tratamiento del paciente.</p>
                                    </div>
                                </div>
                                <div class="stamp">
                                    <span>Fecha de emision</span>
                                    <strong>${date}</strong>
                                </div>
                            </div>
                            <div class="body">
                                <div class="hero">
                                    <div class="eyebrow">Paciente atendido</div>
                                    <h2>${petName}</h2>
                                    <p>Tipo de atencion: ${historyType} | Servicio asociado: ${serviceName}</p>
                                </div>
                                <div class="meta">
                                    <div class="box"><span>Propietario</span><strong>${owner}</strong></div>
                                    <div class="box"><span>DNI</span><strong>${ownerDni}</strong></div>
                                    <div class="box"><span>Telefono</span><strong>${ownerPhone}</strong></div>
                                    <div class="box"><span>Direccion</span><strong>${ownerAddress}</strong></div>
                                </div>
                                <div class="section">
                                    <h3>Resumen clinico</h3>
                                    <div class="clinical-grid">
                                        <div class="box"><span>Diagnostico</span><strong>${diagnosis}</strong></div>
                                        <div class="box"><span>Especie</span><strong>${petType}</strong></div>
                                        <div class="box"><span>Raza / Sexo</span><strong>${petBreed} | ${petSex}</strong></div>
                                        <div class="box"><span>Color</span><strong>${petColor}</strong></div>
                                        <div class="box"><span>Peso</span><strong>${weight}</strong></div>
                                        <div class="box"><span>Temperatura</span><strong>${temperature}</strong></div>
                                    </div>
                                </div>
                                <div class="section">
                                    <h3>Medicamentos</h3>
                                    <div class="panel">
                                        <ul>${meds || '<li>Sin medicamentos registrados.</li>'}</ul>
                                    </div>
                                </div>
                                <div class="section">
                                    <h3>Indicaciones</h3>
                                    <div class="panel">
                                        <ul>${notes || '<li>Sin indicaciones registradas.</li>'}</ul>
                                    </div>
                                </div>
                                <div class="signature">
                                    <div class="line">Firma y sello profesional</div>
                                    <div class="line">Recepcion o conformidad del propietario</div>
                                </div>
                                <div class="footer">Documento de apoyo clinico generado por el sistema de gestion veterinaria DRA. ALFARO.</div>
                            </div>
                        </div>
                    </div>
                </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
        }, 250);
    };

    window.openRecetaModal = function (historiaId = '') {
        resetForm(historiaId);
        syncPreview();
        open();
    };

    window.openEditRecetaModal = function (receta) {
        const { form, historia, template, meds, notes, editingId } = els();

        if (!form) {
            return;
        }

        resetForm();
        form.dataset.mode = 'edit';
        form.action = updateAction(form, receta.id);
        ensureMethod(form).value = 'PUT';
        if (editingId) editingId.value = receta.id;
        if (historia) historia.value = String(receta.historia_clinica_id || '');
        if (template) template.value = '';
        if (meds) meds.value = receta.medicamentos || '';
        if (notes) notes.value = receta.indicaciones || '';
        syncPreview();
        open();
    };

    window.closeRecetaModal = function () {
        const { modal } = els();

        if (!modal) {
            return;
        }

        setCardVisibility(false);
        window.setTimeout(() => {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        }, 160);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, historiaSearch, historia, template, meds, notes, form } = els();

        if (!modal || !form) {
            return;
        }

        setCardVisibility(false);

        if (historia) historia.addEventListener('change', syncPreview);
        if (historiaSearch) historiaSearch.addEventListener('input', filterHistorias);
        if (meds) meds.addEventListener('input', syncPreview);
        if (notes) notes.addEventListener('input', syncPreview);
        if (template) {
            template.addEventListener('change', () => {
                applyTemplate();
                syncPreview();
            });
        }

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeRecetaModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                window.closeRecetaModal();
            }
        });

        if (window.recetaModalState?.hasErrors || modal.dataset.openOnLoad === 'true') {
            if (window.recetaModalState?.isEdit && window.recetaModalState.editingId) {
                form.dataset.mode = 'edit';
                form.action = updateAction(form, window.recetaModalState.editingId);
                ensureMethod(form).value = 'PUT';
            } else if (modal.dataset.prefillHistoria && historia && !historia.value) {
                historia.value = modal.dataset.prefillHistoria;
            }

            syncPreview();
            open();
            return;
        }

        form.dataset.mode = 'create';
        syncPreview();
    });
})();
