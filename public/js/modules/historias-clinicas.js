(function () {
    function getHistoriaModalElements() {
        const form = document.getElementById('historiaForm');

        return {
            modal: document.getElementById('historiaModal'),
            form,
            title: document.getElementById('historiaModalTitle'),
            summary: document.getElementById('historiaMascotaSummary'),
            submitLabel: document.getElementById('historiaSubmitLabel'),
            mascotaSearch: document.getElementById('historia_mascota_search'),
            mascotaSelect: document.getElementById('historia_mascota_id'),
            fechaInput: document.getElementById('historia_fecha'),
            pesoInput: document.getElementById('historia_peso'),
            temperaturaInput: document.getElementById('historia_temperatura'),
            diagnosticoInput: document.getElementById('historia_diagnostico'),
            observacionesInput: document.getElementById('historia_observaciones'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            photo: document.getElementById('historiaMascotaPhoto'),
            petName: document.getElementById('historiaMascotaName'),
            petOwner: document.getElementById('historiaMascotaOwner'),
            petType: document.getElementById('historiaMascotaType'),
        };
    }

    function normalizeSearchText(value = '') {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
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
        const template = form?.dataset.updateTemplate || '/historias-clinicas/__ID__';
        return template.replace('__ID__', encodeURIComponent(id));
    }

    function getTodayValue(form) {
        return form?.dataset.today || new Date().toISOString().slice(0, 10);
    }

    function updateSelectedMascotaPreview() {
        const { mascotaSelect, mascotaSearch, title, summary, form, photo, petName, petOwner, petType, submitLabel } = getHistoriaModalElements();

        if (!mascotaSelect || !title || !summary || !form || !photo || !petName || !petOwner || !petType || !submitLabel) {
            return;
        }

        const selectedOption = mascotaSelect.options[mascotaSelect.selectedIndex];
        const isEdit = form.dataset.mode === 'edit';
        const defaultPhoto = '/storage/default.png';

        title.textContent = isEdit ? 'Actualizar evento clinico' : 'Registrar evento clinico';
        submitLabel.textContent = isEdit ? 'Actualizar evento' : 'Guardar evento';

        if (!selectedOption || !mascotaSelect.value) {
            if (mascotaSearch && form.dataset.syncingSearch !== 'true') {
                mascotaSearch.value = '';
            }
            photo.src = defaultPhoto;
            petName.textContent = 'Selecciona una mascota';
            petOwner.textContent = 'El propietario aparecera aqui.';
            petType.textContent = 'Paciente pendiente';
            summary.textContent = 'Selecciona la mascota y registra solo el detalle medico de esta atencion.';
            return;
        }

        const cliente = selectedOption.dataset.cliente || 'Sin propietario registrado';
        const tipo = selectedOption.dataset.tipo || 'Paciente veterinario';
        const foto = selectedOption.dataset.foto || defaultPhoto;
        const nombre = selectedOption.text.split(' - ')[0] || 'Mascota seleccionada';

        if (mascotaSearch && form.dataset.syncingSearch !== 'true') {
            mascotaSearch.value = cliente ? `${nombre} - ${cliente}` : nombre;
        }

        photo.src = foto;
        petName.textContent = nombre;
        petOwner.textContent = cliente;
        petType.textContent = tipo;
        summary.textContent = isEdit
            ? 'Actualizando un evento del historial clinico de ' + nombre + '.'
            : 'Registrando un evento en el historial clinico de ' + nombre + '.';
    }

    function filterHistoriaMascotas() {
        const { form, mascotaSearch, mascotaSelect } = getHistoriaModalElements();

        if (!form || !mascotaSearch || !mascotaSelect) {
            return;
        }

        const term = normalizeSearchText(mascotaSearch.value);
        form.dataset.syncingSearch = 'true';

        Array.from(mascotaSelect.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const haystack = normalizeSearchText([
                option.textContent,
                option.dataset.cliente,
                option.dataset.tipo,
            ].join(' '));

            option.hidden = Boolean(term) && !haystack.includes(term);
        });

        form.dataset.syncingSearch = 'false';
    }

    function resetHistoriaForm(mascotaId = '') {
        const { form, mascotaSearch, mascotaSelect, fechaInput, pesoInput, temperaturaInput, diagnosticoInput, observacionesInput, editingId } = getHistoriaModalElements();

        if (!form) {
            return;
        }

        form.action = form.dataset.storeAction || form.action;
        form.dataset.mode = 'create';
        removeMethodInput(form);

        if (editingId) {
            editingId.value = '';
        }

        if (mascotaSelect) {
            mascotaSelect.value = mascotaId ? String(mascotaId) : '';
        }

        if (mascotaSearch) {
            mascotaSearch.value = '';
            filterHistoriaMascotas();
        }

        if (fechaInput) {
            fechaInput.value = getTodayValue(form);
        }

        if (pesoInput) {
            pesoInput.value = '';
        }

        if (temperaturaInput) {
            temperaturaInput.value = '';
        }

        if (diagnosticoInput) {
            diagnosticoInput.value = '';
        }

        if (observacionesInput) {
            observacionesInput.value = '';
        }
    }

    function openModal() {
        const { modal } = getHistoriaModalElements();

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    window.openHistoriaModal = function (mascotaId = '') {
        resetHistoriaForm(mascotaId);
        updateSelectedMascotaPreview();
        openModal();
    };

    window.openEditHistoriaModal = function (historia) {
        const { form, mascotaSelect, fechaInput, pesoInput, temperaturaInput, diagnosticoInput, observacionesInput, editingId } = getHistoriaModalElements();

        if (!form) {
            return;
        }

        resetHistoriaForm();
        form.dataset.mode = 'edit';
        form.action = getUpdateAction(form, historia.id);
        ensureMethodInput(form).value = 'PUT';

        if (editingId) {
            editingId.value = historia.id;
        }

        if (mascotaSelect) {
            mascotaSelect.value = String(historia.mascota_id || '');
        }

        if (fechaInput) {
            fechaInput.value = historia.fecha || getTodayValue(form);
        }

        if (pesoInput) {
            pesoInput.value = historia.peso || '';
        }

        if (temperaturaInput) {
            temperaturaInput.value = historia.temperatura || '';
        }

        if (diagnosticoInput) {
            diagnosticoInput.value = historia.diagnostico || '';
        }

        if (observacionesInput) {
            observacionesInput.value = historia.observaciones || '';
        }

        updateSelectedMascotaPreview();
        openModal();
    };

    window.closeHistoriaModal = function () {
        const { modal } = getHistoriaModalElements();

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, mascotaSearch, mascotaSelect, form } = getHistoriaModalElements();

        if (!modal || !form) {
            return;
        }

        if (mascotaSelect) {
            mascotaSelect.addEventListener('change', updateSelectedMascotaPreview);
        }

        if (mascotaSearch) {
            mascotaSearch.addEventListener('input', filterHistoriaMascotas);
        }

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeHistoriaModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                window.closeHistoriaModal();
            }
        });

        if (window.historiaModalState?.hasErrors || modal.dataset.openOnLoad === 'true') {
            if (window.historiaModalState?.isEdit && window.historiaModalState.editingId) {
                form.dataset.mode = 'edit';
                form.action = getUpdateAction(form, window.historiaModalState.editingId);
                ensureMethodInput(form).value = 'PUT';
            } else {
                form.dataset.mode = 'create';
                if (modal.dataset.prefillMascota) {
                    const { mascotaSelect: prefillSelect } = getHistoriaModalElements();
                    if (prefillSelect && !prefillSelect.value) {
                        prefillSelect.value = modal.dataset.prefillMascota;
                    }
                }
            }

            updateSelectedMascotaPreview();
            openModal();
            return;
        }

        form.dataset.mode = 'create';
        updateSelectedMascotaPreview();
    });
})();
