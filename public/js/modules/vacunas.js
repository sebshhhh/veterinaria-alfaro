(function () {
    function getVacunaModalElements() {
        const form = document.getElementById('vacunaForm');

        return {
            modal: document.getElementById('vacunaModal'),
            form,
            title: document.getElementById('vacunaModalTitle'),
            summary: document.getElementById('vacunaMascotaSummary'),
            submitLabel: document.getElementById('vacunaSubmitLabel'),
            stateSelectorWrap: document.getElementById('vacuna_state_selector_wrap'),
            mascotaSelect: document.getElementById('vacuna_mascota_id'),
            mascotaSearch: document.getElementById('vacuna_mascota_search'),
            mascotaResults: document.getElementById('vacunaMascotaResults'),
            mascotaResultsList: document.getElementById('vacunaMascotaResultsList'),
            mascotaSelected: document.getElementById('vacunaSelectedMascota'),
            mascotaSelectedName: document.getElementById('vacunaSelectedMascotaName'),
            mascotaSelectedMeta: document.getElementById('vacunaSelectedMascotaMeta'),
            mascotaClear: document.getElementById('vacunaClearMascota'),
            estadoInputs: document.querySelectorAll('input[name="estado_aplicacion"]'),
            vacunaSelect: document.getElementById('vacuna_nombre_select'),
            vacunaLabel: document.getElementById('vacuna_nombre_label'),
            vacunaCustomWrap: document.getElementById('vacuna_nombre_custom_wrap'),
            vacunaCustomInput: document.getElementById('vacuna_nombre_custom'),
            fechaProgramadaWrap: document.getElementById('vacuna_fecha_programada_wrap'),
            fechaProgramadaInput: document.getElementById('vacuna_fecha_programada'),
            fechaProgramadaLabel: document.getElementById('vacuna_fecha_programada_label'),
            fechaAplicacionWrap: document.getElementById('vacuna_fecha_aplicacion_wrap'),
            fechaAplicacionInput: document.getElementById('vacuna_fecha_aplicacion'),
            fechaAplicacionLabel: document.getElementById('vacuna_fecha_aplicacion_label'),
            proximaDosisWrap: document.getElementById('vacuna_proxima_dosis_wrap'),
            proximaDosisInput: document.getElementById('vacuna_proxima_dosis'),
            proximaDosisLabel: document.getElementById('vacuna_proxima_dosis_label'),
            proximaDosisHelp: document.getElementById('vacuna_proxima_dosis_help'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            flowModeInput: form ? form.querySelector('input[name="flow_mode"]') : null,
            photo: document.getElementById('vacunaMascotaPhoto'),
            petName: document.getElementById('vacunaMascotaName'),
            petOwner: document.getElementById('vacunaMascotaOwner'),
            petType: document.getElementById('vacunaMascotaType'),
            estadoBadge: document.getElementById('vacunaEstadoBadge'),
            helperCard: document.getElementById('vacunaHelperCard'),
        };
    }

    function getSelectedEstadoAplicacion() {
        const selected = document.querySelector('input[name="estado_aplicacion"]:checked');
        return selected ? selected.value : 'aplicada';
    }

    function ensureMethodInput(form) {
        let method = form.querySelector('input[name="_method"]');

        if (!method) {
            method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            form.appendChild(method);
        }

        return method;
    }

    function removeMethodInput(form) {
        const method = form.querySelector('input[name="_method"]');

        if (method) {
            method.remove();
        }
    }

    function getUpdateAction(form, id) {
        const template = form?.dataset.updateTemplate || '/vacunas/__ID__';
        return template.replace('__ID__', encodeURIComponent(id));
    }

    function getTodayValue(form) {
        return form?.dataset.today || new Date().toISOString().slice(0, 10);
    }

    function setEstadoAplicacion(value) {
        const { estadoInputs } = getVacunaModalElements();
        estadoInputs.forEach((input) => {
            input.checked = input.value === value;
        });
    }

    function applyVacunaFlowMode(mode) {
        const {
            form,
            flowModeInput,
            stateSelectorWrap,
            vacunaLabel,
            fechaProgramadaLabel,
            fechaAplicacionLabel,
            proximaDosisLabel,
            proximaDosisHelp,
            helperCard,
        } = getVacunaModalElements();

        if (!form) {
            return;
        }

        const config = {
            create_applied: {
                state: 'aplicada',
                showSelector: false,
                vacunaLabel: 'Que vacuna se aplico',
                fechaAplicacionLabel: 'Cuando se aplico',
                fechaProgramadaLabel: 'Cuando debe aplicarse',
                proximaDosisLabel: 'Programar siguiente vacuna',
                proximaDosisHelp: 'Si la llenas, el sistema dejara la siguiente dosis como pendiente en este mismo modulo.',
                helper: 'Usa esta pantalla cuando el paciente viene por su vacuna de hoy. Si corresponde, tambien puedes dejar programada la siguiente dosis.',
            },
            create_scheduled: {
                state: 'programada',
                showSelector: false,
                vacunaLabel: 'Que vacuna quedara pendiente',
                fechaAplicacionLabel: 'Cuando se aplico',
                fechaProgramadaLabel: 'Cuando debe aplicarse',
                proximaDosisLabel: 'Programar siguiente vacuna',
                proximaDosisHelp: '',
                helper: 'Usa esta pantalla cuando solo quieres dejar una vacuna pendiente. Luego podras volver y marcarla como aplicada con un solo paso.',
            },
            edit_applied: {
                state: 'aplicada',
                showSelector: false,
                vacunaLabel: 'Vacuna aplicada',
                fechaAplicacionLabel: 'Cuando se aplico',
                fechaProgramadaLabel: 'Cuando debe aplicarse',
                proximaDosisLabel: 'Programar siguiente vacuna',
                proximaDosisHelp: 'Si corresponde, deja aqui la fecha de la siguiente dosis.',
                helper: 'Usa esta pantalla para corregir o completar una vacuna ya aplicada.',
            },
            edit_scheduled: {
                state: 'programada',
                showSelector: false,
                vacunaLabel: 'Vacuna pendiente',
                fechaAplicacionLabel: 'Cuando se aplico',
                fechaProgramadaLabel: 'Cambiar fecha programada',
                proximaDosisLabel: 'Programar siguiente vacuna',
                proximaDosisHelp: '',
                helper: 'Usa esta pantalla para cambiar la fecha de una vacuna pendiente sin marcarla como aplicada todavia.',
            },
            apply_scheduled: {
                state: 'aplicada',
                showSelector: false,
                vacunaLabel: 'Vacuna pendiente que se aplico',
                fechaAplicacionLabel: 'Cuando se aplico',
                fechaProgramadaLabel: 'Cuando debe aplicarse',
                proximaDosisLabel: 'Programar siguiente vacuna',
                proximaDosisHelp: 'Si esta vacuna requiere otra dosis, puedes dejarla programada de inmediato.',
                helper: 'Aqui confirmas que una vacuna pendiente ya fue aplicada. El sistema actualiza el mismo registro y, si quieres, deja programada la siguiente.',
            },
        }[mode] || null;

        if (!config) {
            return;
        }

        form.dataset.mode = mode;

        if (flowModeInput) {
            flowModeInput.value = mode;
        }

        setEstadoAplicacion(config.state);

        if (stateSelectorWrap) {
            stateSelectorWrap.classList.toggle('hidden', !config.showSelector);
        }

        if (vacunaLabel) vacunaLabel.textContent = config.vacunaLabel;
        if (fechaProgramadaLabel) fechaProgramadaLabel.textContent = config.fechaProgramadaLabel;
        if (fechaAplicacionLabel) fechaAplicacionLabel.textContent = config.fechaAplicacionLabel;
        if (proximaDosisLabel) proximaDosisLabel.textContent = config.proximaDosisLabel;

        if (proximaDosisHelp) {
            proximaDosisHelp.textContent = config.proximaDosisHelp;
            proximaDosisHelp.classList.toggle('hidden', !config.proximaDosisHelp);
        }

        if (helperCard) {
            helperCard.textContent = config.helper;
        }
    }

    function updateVacunaNameMode() {
        const { vacunaSelect, vacunaCustomWrap, vacunaCustomInput } = getVacunaModalElements();

        if (!vacunaSelect || !vacunaCustomWrap || !vacunaCustomInput) {
            return;
        }

        const isCustom = vacunaSelect.value === '__custom__';
        vacunaCustomWrap.classList.toggle('hidden', !isCustom);

        if (!isCustom) {
            vacunaCustomInput.value = '';
        }
    }

    function updateVacunaMode() {
        const {
            fechaProgramadaWrap,
            fechaProgramadaInput,
            fechaAplicacionWrap,
            proximaDosisWrap,
            proximaDosisInput,
            estadoBadge,
        } = getVacunaModalElements();

        const estado = getSelectedEstadoAplicacion();
        const isApplied = estado === 'aplicada';

        if (fechaProgramadaWrap) {
            fechaProgramadaWrap.classList.toggle('hidden', isApplied);
        }

        if (fechaAplicacionWrap) {
            fechaAplicacionWrap.classList.toggle('hidden', !isApplied);
        }

        if (proximaDosisWrap) {
            proximaDosisWrap.classList.toggle('hidden', !isApplied);
        }

        if (!isApplied && proximaDosisInput) {
            proximaDosisInput.value = '';
        }

        if (isApplied && fechaProgramadaInput) {
            fechaProgramadaInput.value = '';
        }

        if (estadoBadge) {
            estadoBadge.textContent = isApplied ? 'Vacuna aplicada' : 'Vacuna programada';
            estadoBadge.className = 'inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold ' + (isApplied
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-amber-50 text-amber-700');
        }
    }

    function getMascotaOptionById(id) {
        const { mascotaSelect } = getVacunaModalElements();

        if (!mascotaSelect || !id) {
            return null;
        }

        return Array.from(mascotaSelect.options).find((option) => option.value === String(id)) || null;
    }

    function toggleMascotaResults(show) {
        const { mascotaResults } = getVacunaModalElements();

        if (!mascotaResults) {
            return;
        }

        mascotaResults.classList.toggle('hidden', !show);
    }

    function renderMascotaResults(query) {
        const { mascotaSelect, mascotaResultsList } = getVacunaModalElements();

        if (!mascotaSelect || !mascotaResultsList) {
            return;
        }

        const normalizedQuery = (query || '').trim().toLowerCase();
        mascotaResultsList.innerHTML = '';

        if (!normalizedQuery) {
            toggleMascotaResults(false);
            return;
        }

        const matches = Array.from(mascotaSelect.options)
            .filter((option) => option.value)
            .map((option) => ({
                option,
                mascota: (option.dataset.label || option.textContent || '').trim(),
                cliente: (option.dataset.cliente || '').trim(),
                dni: (option.dataset.dni || '').trim(),
                tipo: (option.dataset.tipo || '').trim(),
            }))
            .filter((item) => {
                const haystack = [item.mascota, item.cliente, item.dni, item.tipo]
                    .join(' ')
                    .toLowerCase();

                return haystack.includes(normalizedQuery);
            })
            .slice(0, 8);

        if (!matches.length) {
            mascotaResultsList.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">No se encontro ninguna mascota con esa busqueda.</div>';
            toggleMascotaResults(true);
            return;
        }

        matches.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flex w-full items-start justify-between gap-3 rounded-2xl px-4 py-3 text-left transition hover:bg-blue-50';
            button.innerHTML = `
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">${item.mascota}</p>
                    <p class="mt-1 truncate text-xs text-slate-500">${item.cliente || 'Sin propietario'}${item.dni ? ' - DNI ' + item.dni : ''}</p>
                </div>
                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">${item.tipo || 'Paciente'}</span>
            `;
            button.addEventListener('click', () => {
                selectVacunaMascota(item.option.value);
            });
            mascotaResultsList.appendChild(button);
        });

        toggleMascotaResults(true);
    }

    function updateSelectedMascotaChip() {
        const { mascotaSelect, mascotaSelected, mascotaSelectedName, mascotaSelectedMeta, mascotaSearch } = getVacunaModalElements();

        if (!mascotaSelect || !mascotaSelected || !mascotaSelectedName || !mascotaSelectedMeta) {
            return;
        }

        const selected = mascotaSelect.options[mascotaSelect.selectedIndex];

        if (!selected || !mascotaSelect.value) {
            mascotaSelected.classList.add('hidden');
            if (mascotaSearch) {
                mascotaSearch.value = '';
            }
            return;
        }

        const mascota = selected.dataset.label || selected.textContent.split(' - ')[0] || 'Mascota';
        const cliente = selected.dataset.cliente || 'Sin propietario';
        const dni = selected.dataset.dni ? ' - DNI ' + selected.dataset.dni : '';

        mascotaSelected.classList.remove('hidden');
        mascotaSelected.classList.add('flex');
        mascotaSelectedName.textContent = mascota;
        mascotaSelectedMeta.textContent = cliente + dni;

        if (mascotaSearch) {
            mascotaSearch.value = mascota;
        }
    }

    function selectVacunaMascota(id) {
        const { mascotaSelect } = getVacunaModalElements();

        if (!mascotaSelect) {
            return;
        }

        mascotaSelect.value = id ? String(id) : '';
        updateSelectedMascotaChip();
        updateSelectedMascotaPreview();
        toggleMascotaResults(false);
    }

    function updateSelectedMascotaPreview() {
        const { mascotaSelect, title, summary, form, photo, petName, petOwner, petType, submitLabel } = getVacunaModalElements();

        if (!mascotaSelect || !title || !summary || !form || !photo || !petName || !petOwner || !petType || !submitLabel) {
            return;
        }

        const selectedOption = mascotaSelect.options[mascotaSelect.selectedIndex];
        const mode = form.dataset.mode || 'create_applied';
        const isEdit = mode === 'edit_applied' || mode === 'edit_scheduled';
        const isApply = mode === 'apply_scheduled';
        const isApplied = getSelectedEstadoAplicacion() === 'aplicada';
        const defaultPhoto = '/storage/default.png';

        if (mode === 'create_scheduled') {
            title.textContent = 'Programar vacuna';
            submitLabel.textContent = 'Guardar programacion';
        } else if (isApply) {
            title.textContent = 'Aplicar vacuna pendiente';
            submitLabel.textContent = 'Guardar aplicacion';
        } else {
            title.textContent = isEdit
                ? (isApplied ? 'Actualizar vacuna aplicada' : 'Reprogramar vacuna pendiente')
                : 'Registrar vacuna aplicada';
            submitLabel.textContent = isEdit
                ? (isApplied ? 'Actualizar control' : 'Guardar nueva fecha')
                : 'Guardar control';
        }

        if (!selectedOption || !mascotaSelect.value) {
            photo.src = defaultPhoto;
            petName.textContent = 'Selecciona una mascota';
            petOwner.textContent = 'El propietario aparecera aqui.';
            petType.textContent = 'Control preventivo';
            summary.textContent = mode === 'create_scheduled'
                ? 'Selecciona la mascota y deja lista la vacuna pendiente.'
                : (isApply
                    ? 'Selecciona la mascota y confirma la fecha en que la vacuna pendiente ya fue aplicada.'
                    : 'Selecciona la mascota y registra la vacuna aplicada.');
            return;
        }

        const cliente = selectedOption.dataset.cliente || 'Sin propietario registrado';
        const tipo = selectedOption.dataset.tipo || 'Paciente veterinario';
        const foto = selectedOption.dataset.foto || defaultPhoto;
        const nombre = selectedOption.text.split(' - ')[0] || 'Mascota seleccionada';

        photo.src = foto;
        petName.textContent = nombre;
        petOwner.textContent = cliente;
        petType.textContent = tipo;

        if (mode === 'create_scheduled') {
            summary.textContent = 'Programando una vacuna pendiente para ' + nombre + '.';
            return;
        }

        if (isApply) {
            summary.textContent = 'Registrando como aplicada la vacuna pendiente de ' + nombre + '.';
            return;
        }

        summary.textContent = isEdit
            ? (isApplied
                ? 'Actualizando la vacuna aplicada de ' + nombre + '.'
                : 'Reprogramando la vacuna pendiente de ' + nombre + '.')
            : 'Registrando una vacuna aplicada para ' + nombre + '.';
    }

    function resetVacunaForm(mascotaId = '') {
        const {
            form,
            mascotaSelect,
            vacunaSelect,
            vacunaCustomInput,
            fechaProgramadaInput,
            fechaAplicacionInput,
            proximaDosisInput,
            editingId,
        } = getVacunaModalElements();

        if (!form) {
            return;
        }

        form.action = form.dataset.storeAction || form.action;
        removeMethodInput(form);

        if (editingId) {
            editingId.value = '';
        }

        if (mascotaSelect) {
            mascotaSelect.value = mascotaId ? String(mascotaId) : '';
        }

        if (vacunaSelect) {
            vacunaSelect.value = '';
        }

        if (vacunaCustomInput) {
            vacunaCustomInput.value = '';
        }

        if (fechaProgramadaInput) {
            fechaProgramadaInput.value = getTodayValue(form);
        }

        if (fechaAplicacionInput) {
            fechaAplicacionInput.value = getTodayValue(form);
        }

        if (proximaDosisInput) {
            proximaDosisInput.value = '';
        }

        updateVacunaNameMode();
        updateSelectedMascotaChip();
    }

    function openModal() {
        const { modal } = getVacunaModalElements();

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function openCreateVacunaModal(mascotaId, mode) {
        resetVacunaForm(mascotaId);
        applyVacunaFlowMode(mode);
        updateVacunaMode();
        updateSelectedMascotaPreview();
        openModal();
    }

    

    window.openVacunaModal = function (mascotaId = '') {
        openCreateVacunaModal(mascotaId, 'create_applied');
    };

    window.selectVacunaMascota = function (mascotaId = '') {
        if (!mascotaId) {
            return;
        }

        selectVacunaMascota(mascotaId);
    };

    window.openVacunaAppliedModal = function (mascotaId = '') {
        openCreateVacunaModal(mascotaId, 'create_applied');
    };

    window.openProgramVacunaModal = function (mascotaId = '') {
        openCreateVacunaModal(mascotaId, 'create_scheduled');
    };

    window.openEditVacunaModal = function (vacuna) {
        const {
            form,
            mascotaSelect,
            vacunaSelect,
            vacunaCustomInput,
            fechaProgramadaInput,
            fechaAplicacionInput,
            proximaDosisInput,
            editingId,
        } = getVacunaModalElements();

        if (!form) {
            return;
        }

        resetVacunaForm();
        form.action = getUpdateAction(form, vacuna.id);
        ensureMethodInput(form).value = 'PUT';

        if (editingId) {
            editingId.value = vacuna.id;
        }

        if (mascotaSelect) {
            mascotaSelect.value = String(vacuna.mascota_id || '');
        }

        applyVacunaFlowMode(
            vacuna.applyFlow
                ? 'apply_scheduled'
                : ((vacuna.estado_aplicacion || 'aplicada') === 'programada' ? 'edit_scheduled' : 'edit_applied')
        );

        if (vacunaSelect) {
            vacunaSelect.value = vacuna.nombre_select || '';
        }

        if (vacunaCustomInput) {
            vacunaCustomInput.value = vacuna.nombre_custom || '';
        }

        if (fechaProgramadaInput) {
            fechaProgramadaInput.value = vacuna.fecha_programada || getTodayValue(form);
        }

        if (fechaAplicacionInput) {
            fechaAplicacionInput.value = vacuna.fecha_aplicacion || getTodayValue(form);
        }

        if (proximaDosisInput) {
            proximaDosisInput.value = vacuna.proxima_dosis || '';
        }

        updateVacunaNameMode();
        updateVacunaMode();
        updateSelectedMascotaChip();
        updateSelectedMascotaPreview();
        openModal();
    };

    window.openApplyVacunaModal = function (vacuna) {
        window.openEditVacunaModal({
            ...vacuna,
            applyFlow: true,
            estado_aplicacion: 'aplicada',
            fecha_aplicacion: vacuna.fecha_programada || getTodayValue(getVacunaModalElements().form),
            proxima_dosis: '',
        });
    };

    window.closeVacunaModal = function () {
        const { modal } = getVacunaModalElements();

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, mascotaSelect, mascotaSearch, mascotaClear, vacunaSelect, estadoInputs, form } = getVacunaModalElements();

        if (!modal || !form) {
            return;
        }

        if (mascotaSelect) {
            mascotaSelect.addEventListener('change', () => {
                updateSelectedMascotaChip();
                updateSelectedMascotaPreview();
            });
        }

        if (mascotaSearch) {
            mascotaSearch.addEventListener('input', (event) => {
                renderMascotaResults(event.target.value);
            });

            mascotaSearch.addEventListener('focus', () => {
                if (mascotaSearch.value.trim() !== '') {
                    renderMascotaResults(mascotaSearch.value);
                }
            });
        }

        if (mascotaClear) {
            mascotaClear.addEventListener('click', () => {
                selectVacunaMascota('');
            });
        }

        if (vacunaSelect) {
            vacunaSelect.addEventListener('change', updateVacunaNameMode);
        }

        if (estadoInputs?.length) {
            estadoInputs.forEach((input) => {
                input.addEventListener('change', () => {
                    updateVacunaMode();
                    updateSelectedMascotaPreview();
                });
            });
        }

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeVacunaModal();
            }
        });

        document.addEventListener('click', (event) => {
            const { mascotaResults, mascotaSearch } = getVacunaModalElements();

            if (!mascotaResults || !mascotaSearch) {
                return;
            }

            if (!mascotaResults.contains(event.target) && event.target !== mascotaSearch) {
                toggleMascotaResults(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                window.closeVacunaModal();
            }
        });

        if (window.vacunaModalState?.hasErrors || modal.dataset.openOnLoad === 'true') {
            if (window.vacunaModalState?.isEdit && window.vacunaModalState.editingId) {
                form.action = getUpdateAction(form, window.vacunaModalState.editingId);
                ensureMethodInput(form).value = 'PUT';
            } else if (modal.dataset.prefillMascota && mascotaSelect && !mascotaSelect.value) {
                mascotaSelect.value = modal.dataset.prefillMascota;
            }

            applyVacunaFlowMode(form.dataset.initialFlow || 'create_applied');
            updateVacunaNameMode();
            updateVacunaMode();
            updateSelectedMascotaChip();
            updateSelectedMascotaPreview();
            openModal();
            return;
        }

        applyVacunaFlowMode(form.dataset.initialFlow || 'create_applied');
        updateVacunaNameMode();
        updateVacunaMode();
        updateSelectedMascotaChip();
        updateSelectedMascotaPreview();
    });
})();





