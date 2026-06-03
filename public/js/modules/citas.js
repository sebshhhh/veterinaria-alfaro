(function () {
    const attendTypeDefaults = {
        consulta: {
            summary: 'Registra solo lo realizado. Tratamiento, receta y control son opcionales.',
        },
        vacunacion: {
            summary: 'Completa la vacuna aplicada. Tratamiento, receta y control solo si tambien se usaron.',
        },
        control: {
            summary: 'Registra la revision del paciente y deja proximo control solo si debe volver.',
        },
        desparasitacion: {
            summary: 'Registra la desparasitacion y completa otros bloques solo si corresponde.',
        },
        servicio: {
            summary: 'Esta cita se atendera como servicio no clinico, con foco solo en el servicio realizado y su precio.',
        },
        otro: {
            summary: 'Usa esta opcion para una atencion distinta y completa solo lo necesario.',
        },
    };

    const mascotaBreedCatalog = {
        Perro: ['Labrador', 'Pitbull', 'Pastor Aleman', 'Beagle', 'Pug', 'Otro'],
        Gato: ['Siames', 'Persa', 'Mestizo', 'Angora', 'Otro'],
        Ave: ['Canario', 'Loro', 'Perico', 'Otro'],
        Otro: ['Otro'],
    };

    let reopenMainAfterCliente = false;
    let reopenMainAfterMascota = false;

    function portalModalToBody(modal) {
        if (!modal || modal.dataset.portalMounted === 'true') {
            return;
        }

        document.body.appendChild(modal);
        modal.dataset.portalMounted = 'true';
    }

    function animateOpen(modal, card) {
        if (!modal || !card) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        });

        document.body.classList.add('overflow-hidden');
    }

    function animateClose(modal, card, onClose) {
        if (!modal || !card) {
            return;
        }

        card.classList.add('scale-95', 'opacity-0');
        card.classList.remove('scale-100', 'opacity-100');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (!document.querySelector('.fixed.flex[id$="Modal"]') && document.getElementById('citaModal')?.classList.contains('hidden')) {
                document.body.classList.remove('overflow-hidden');
            }

            if (typeof onClose === 'function') {
                onClose();
            }
        }, 200);
    }

    function getCitaModalElements() {
        const form = document.getElementById('citaForm');

        return {
            modal: document.getElementById('citaModal'),
            title: document.getElementById('citaModalTitle'),
            summary: document.getElementById('citaMascotaSummary'),
            submitLabel: document.getElementById('citaSubmitLabel'),
            mascotaSelect: document.getElementById('cita_mascota_id'),
            mascotaSearch: document.getElementById('cita_mascota_search'),
            mascotaResults: document.getElementById('citaMascotaResults'),
            mascotaResultsList: document.getElementById('citaMascotaResultsList'),
            mascotaSelected: document.getElementById('citaMascotaSelected'),
            mascotaSelectedPhoto: document.getElementById('citaMascotaSelectedPhoto'),
            mascotaSelectedName: document.getElementById('citaMascotaSelectedName'),
            mascotaSelectedMeta: document.getElementById('citaMascotaSelectedMeta'),
            mascotaClear: document.getElementById('citaMascotaClear'),
            veterinarioSelect: document.getElementById('cita_veterinario_id'),
            veterinarioInput: form ? form.querySelector('input[name="veterinario_id"]') : null,
            fechaInput: document.getElementById('cita_fecha'),
            horaInput: document.getElementById('cita_hora'),
            estadoSelect: document.getElementById('cita_estado'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            form,
        };
    }

    function getCitaClienteModalElements() {
        const modal = document.getElementById('citaClienteModal');

        return {
            modal,
            card: modal?.querySelector('.modal-card'),
            form: modal?.querySelector('form'),
        };
    }

    function getCitaMascotaModalElements() {
        const modal = document.getElementById('citaMascotaModal');
        const form = document.getElementById('citaMascotaForm');

        return {
            modal,
            card: modal?.querySelector('.modal-card'),
            form,
            clienteSearch: document.getElementById('cita_modal_cliente_search'),
            clienteId: document.getElementById('cita_modal_cliente_id'),
            clienteSummary: document.getElementById('citaMascotaSummary'),
            tipoAnimal: document.getElementById('cita_modal_tipo_animal'),
            razaSelect: document.getElementById('cita_modal_raza_select'),
            razaInput: document.getElementById('cita_modal_raza'),
            otroContainer: document.getElementById('cita_modal_input_otro_raza'),
            razaOtro: document.getElementById('cita_modal_raza_otro'),
        };
    }

    function getUpdateAction(form, id) {
        const template = form?.dataset.updateTemplate || '/citas/__ID__';
        return template.replace('__ID__', encodeURIComponent(id));
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

    function getTodayValue(form) {
        return form?.dataset.today || new Date().toISOString().slice(0, 10);
    }

    function setDateMin(fechaInput, minDate) {
        if (!fechaInput) {
            return;
        }

        fechaInput.min = minDate || '';
    }

    function normalizeSearchText(value = '') {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function getSelectedCitaMascotaOption() {
        const { mascotaSelect } = getCitaModalElements();

        if (!mascotaSelect || !mascotaSelect.value) {
            return null;
        }

        return mascotaSelect.options[mascotaSelect.selectedIndex] || null;
    }

    function getCitaMascotaItems() {
        const { mascotaSelect } = getCitaModalElements();

        if (!mascotaSelect) {
            return [];
        }

        return Array.from(mascotaSelect.options)
            .filter((option) => option.value)
            .map((option) => {
                const label = option.dataset.label || option.text.split(' - ')[0] || option.text;
                const owner = option.dataset.owner || '';
                const dni = option.dataset.dni || '';
                const phone = option.dataset.phone || '';
                const type = option.dataset.type || '';
                const breed = option.dataset.breed || '';
                const color = option.dataset.color || '';

                return {
                    value: option.value,
                    label,
                    owner,
                    dni,
                    phone,
                    type,
                    breed,
                    color,
                    photo: option.dataset.photo || '/storage/default.png',
                    search: normalizeSearchText([
                        label,
                        owner,
                        dni,
                        phone,
                        type,
                        breed,
                        color,
                        option.text || '',
                    ].join(' ')),
                };
            });
    }

    function hideCitaMascotaResults() {
        const { mascotaResults } = getCitaModalElements();

        if (mascotaResults) {
            mascotaResults.classList.add('hidden');
        }
    }

    function syncCitaMascotaSelection(preserveSearchValue = null) {
        const {
            mascotaSelect,
            mascotaSearch,
            mascotaSelected,
            mascotaSelectedPhoto,
            mascotaSelectedName,
            mascotaSelectedMeta,
        } = getCitaModalElements();

        if (!mascotaSelect) {
            return;
        }

        const option = getSelectedCitaMascotaOption();

        if (!option) {
            if (mascotaSelected) {
                mascotaSelected.classList.add('hidden');
                mascotaSelected.classList.remove('flex');
            }

            if (mascotaSearch && preserveSearchValue !== null) {
                mascotaSearch.value = preserveSearchValue;
            } else if (mascotaSearch) {
                mascotaSearch.value = '';
            }

            return;
        }

        const label = option.dataset.label || option.text.split(' - ')[0] || 'Mascota seleccionada';
        const owner = option.dataset.owner || 'Sin propietario registrado';
        const dni = option.dataset.dni || '';
        const type = option.dataset.type || 'Mascota';
        const breed = option.dataset.breed || '';
        const photo = option.dataset.photo || '/storage/default.png';
        const metaParts = [owner];

        if (dni) metaParts.push(`DNI ${dni}`);
        if (type) metaParts.push(type);
        if (breed) metaParts.push(breed);

        if (mascotaSearch) {
            mascotaSearch.value = owner ? `${label} - ${owner}` : label;
        }

        if (mascotaSelected && mascotaSelectedName && mascotaSelectedMeta) {
            mascotaSelected.classList.remove('hidden');
            mascotaSelected.classList.add('flex');
            mascotaSelectedName.textContent = label;
            mascotaSelectedMeta.textContent = metaParts.filter(Boolean).join(' - ');
        }

        if (mascotaSelectedPhoto) {
            mascotaSelectedPhoto.src = photo;
            mascotaSelectedPhoto.alt = `Foto de ${label}`;
        }
    }

    function renderCitaMascotaResults(query = '') {
        const { mascotaSelect, mascotaResults, mascotaResultsList } = getCitaModalElements();

        if (!mascotaSelect || !mascotaResults || !mascotaResultsList) {
            return;
        }

        const selectedValue = mascotaSelect.value ? String(mascotaSelect.value) : '';
        const normalizedQuery = normalizeSearchText(query.trim());
        const results = getCitaMascotaItems()
            .filter((item) => !normalizedQuery || item.search.includes(normalizedQuery))
            .slice(0, 8);

        mascotaResultsList.innerHTML = '';

        if (!results.length) {
            const emptyState = document.createElement('div');
            emptyState.className = 'rounded-2xl px-4 py-4 text-sm text-slate-500';
            emptyState.textContent = 'No se encontraron pacientes con esa busqueda.';
            mascotaResultsList.appendChild(emptyState);
            mascotaResults.classList.remove('hidden');
            return;
        }

        results.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.value = item.value;
            button.className = `flex w-full items-center justify-between gap-3 rounded-2xl px-4 py-3 text-left transition ${
                selectedValue === item.value
                    ? 'bg-blue-50 text-blue-800'
                    : 'text-slate-700 hover:bg-slate-50'
            }`;

            const left = document.createElement('span');
            left.className = 'flex min-w-0 items-center gap-3';

            const img = document.createElement('img');
            img.src = item.photo;
            img.alt = `Foto de ${item.label}`;
            img.className = 'h-10 w-10 shrink-0 rounded-xl object-cover';
            img.onerror = function () {
                this.onerror = null;
                this.src = '/storage/default.png';
            };

            const textWrap = document.createElement('span');
            textWrap.className = 'min-w-0';

            const label = document.createElement('span');
            label.className = 'block truncate text-sm font-semibold';
            label.textContent = item.label;

            const meta = document.createElement('span');
            meta.className = 'block truncate text-xs text-slate-500';
            meta.textContent = [item.owner, item.dni ? `DNI ${item.dni}` : '', item.phone ? `Cel. ${item.phone}` : '']
                .filter(Boolean)
                .join(' - ') || 'Paciente registrado';

            textWrap.appendChild(label);
            textWrap.appendChild(meta);
            left.appendChild(img);
            left.appendChild(textWrap);

            const badge = document.createElement('span');
            badge.className = 'shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500';
            badge.textContent = item.type || 'Mascota';

            button.appendChild(left);
            button.appendChild(badge);
            button.addEventListener('click', () => {
                setSelectedCitaMascota(item.value);
            });

            mascotaResultsList.appendChild(button);
        });

        mascotaResults.classList.remove('hidden');
    }

    function setSelectedCitaMascota(mascotaId) {
        const { mascotaSelect } = getCitaModalElements();

        if (!mascotaSelect || !mascotaId) {
            return;
        }

        mascotaSelect.value = String(mascotaId);
        hideCitaMascotaResults();
        syncCitaMascotaSelection();
        updateCitaSummary();
    }

    function resetCitaForm(mascotaId = '') {
        const {
            form,
            mascotaSelect,
            veterinarioSelect,
            veterinarioInput,
            fechaInput,
            horaInput,
            estadoSelect,
            editingId,
        } = getCitaModalElements();

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
            syncCitaMascotaSelection();
        }

        if (veterinarioSelect && veterinarioSelect.options.length) {
            veterinarioSelect.selectedIndex = 0;
        }

        if (veterinarioInput && !veterinarioSelect) {
            veterinarioInput.value = '';
        }

        if (fechaInput) {
            const today = getTodayValue(form);
            fechaInput.value = today;
            setDateMin(fechaInput, today);
        }

        if (horaInput) {
            horaInput.value = form.dataset.defaultTime || '09:00';
        }

        if (estadoSelect) {
            estadoSelect.value = form.dataset.defaultStatus || 'pendiente';
        }
    }

    function updateCitaSummary() {
        const { form, title, summary, submitLabel, mascotaSelect } = getCitaModalElements();

        if (!form || !title || !summary || !submitLabel || !mascotaSelect) {
            return;
        }

        const selectedOption = getSelectedCitaMascotaOption();
        const selectedText = selectedOption
            ? [
                selectedOption.dataset.label || selectedOption.text.split(' - ')[0] || selectedOption.text,
                selectedOption.dataset.owner || '',
            ].filter(Boolean).join(' - ')
            : '';
        const isEdit = form.dataset.mode === 'edit';
        const actionContext = form.closest('#citaModal')?.dataset.actionContext || '';

        title.textContent = isEdit ? 'Editar cita' : 'Agendar cita';
        submitLabel.textContent = isEdit ? 'Actualizar cita' : 'Guardar cita';

        if (selectedText) {
            if (!isEdit && actionContext === 'control') {
                summary.textContent = 'Programando control para: ' + selectedText + '.';
            } else {
                summary.textContent = (isEdit ? 'Editando cita para: ' : 'Mascota seleccionada: ') + selectedText + '.';
            }
            return;
        }

        summary.textContent = 'Selecciona la mascota y completa la fecha de la cita.';
    }

    function filterCitaModalClientes() {
        const { clienteSearch, clienteId } = getCitaMascotaModalElements();

        if (!clienteSearch || !clienteId) {
            return;
        }

        const term = normalizeSearchText(clienteSearch.value);

        Array.from(clienteId.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            option.hidden = Boolean(term) && !normalizeSearchText(option.textContent).includes(term);
        });
    }

    function openModal() {
        const { modal } = getCitaModalElements();

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function updateCitaMascotaSummary() {
        const { clienteId, clienteSummary } = getCitaMascotaModalElements();

        if (!clienteId || !clienteSummary) {
            return;
        }

        const selectedOption = clienteId.options[clienteId.selectedIndex];
        const selectedText = selectedOption && clienteId.value ? selectedOption.text : '';

        if (selectedText) {
            clienteSummary.textContent = 'Registraras la mascota para: ' + selectedText + '.';
            return;
        }

        clienteSummary.textContent = 'Registra a la mascota para dejar lista su primera cita.';
    }

    function updateCitaMascotaBreedOptions() {
        const { tipoAnimal, razaSelect, razaInput, otroContainer, razaOtro } = getCitaMascotaModalElements();

        if (!tipoAnimal || !razaSelect || !razaInput || !otroContainer || !razaOtro) {
            return;
        }

        const tipo = tipoAnimal.value;
        const breeds = mascotaBreedCatalog[tipo] || [];
        const currentValue = razaInput.dataset.current || razaInput.value || '';

        razaSelect.innerHTML = '<option value="">Seleccione raza</option>';

        breeds.forEach((breed) => {
            const option = document.createElement('option');
            option.value = breed;
            option.textContent = breed;

            if (breed === currentValue) {
                option.selected = true;
            }

            razaSelect.appendChild(option);
        });

        if (currentValue && !breeds.includes(currentValue)) {
            const option = document.createElement('option');
            option.value = 'Otro';
            option.textContent = 'Otro';
            option.selected = true;
            razaSelect.appendChild(option);

            otroContainer.classList.remove('hidden');
            razaOtro.value = currentValue;
            razaInput.value = currentValue;
            return;
        }

        if (razaSelect.value === 'Otro') {
            otroContainer.classList.remove('hidden');
            razaInput.value = razaOtro.value.trim();
            return;
        }

        otroContainer.classList.add('hidden');
        razaOtro.value = '';
        razaInput.value = razaSelect.value;
    }

    function resetCitaMascotaForm(selectedClienteId = '') {
        const { form, clienteId, clienteSearch, razaInput } = getCitaMascotaModalElements();

        if (!form) {
            return;
        }

        form.reset();

        if (razaInput) {
            razaInput.value = '';
            razaInput.dataset.current = '';
        }

        if (clienteId) {
            clienteId.value = selectedClienteId ? String(selectedClienteId) : '';
        }

        if (clienteSearch) {
            clienteSearch.value = '';
            filterCitaModalClientes();
        }

        updateCitaMascotaSummary();
        updateCitaMascotaBreedOptions();
    }

    function getAttendCitaElements() {
        const form = document.getElementById('attendCitaForm');

        return {
            modal: document.getElementById('attendCitaModal'),
            form,
            title: document.getElementById('attendCitaTitle'),
            summary: document.getElementById('attendCitaSummary'),
            citaId: document.getElementById('attend_cita_id'),
            photo: document.getElementById('attendMascotaPhoto'),
            petName: document.getElementById('attendMascotaName'),
            owner: document.getElementById('attendMascotaOwner'),
            fecha: document.getElementById('attendMascotaFecha'),
            hora: document.getElementById('attendMascotaHora'),
            vet: document.getElementById('attendMascotaVet'),
            tipo: document.getElementById('attend_tipo_atencion'),
            peso: document.getElementById('attend_peso'),
            temperatura: document.getElementById('attend_temperatura'),
            clinicalNarrative: document.getElementById('attendClinicalNarrative'),
            servicioSelect: document.getElementById('attend_servicio_producto_id'),
            servicioPrecio: document.getElementById('attend_precio_servicio'),
            servicioHint: document.getElementById('attendServicioHint'),
            vacunaSelect: document.getElementById('attend_vacuna_nombre_select'),
            vacunaCustomWrap: document.getElementById('attend_vacuna_custom_wrap'),
            vacunaCustomInput: document.getElementById('attend_vacuna_nombre_custom'),
            vacunaSelectedLabel: document.getElementById('attendVacunaSelectedLabel'),
            programarProximaVacuna: document.getElementById('attend_programar_proxima_vacuna'),
            proximaDosisWrap: document.getElementById('attend_vacuna_proxima_dosis_wrap'),
            proximaDosisInput: document.getElementById('attend_vacuna_proxima_dosis'),
        };
    }

    function setAttendFieldValue(form, name, value) {
        const field = form?.querySelector(`[name="${name}"]`);

        if (!field) {
            return;
        }

        field.value = value ?? '';
    }

    function resetAttendCitaForm() {
        const { form, programarProximaVacuna } = getAttendCitaElements();

        if (!form) {
            return;
        }

        const defaultDate = form.dataset.defaultDate || new Date().toISOString().slice(0, 10);

        [
            'cita_id',
            'tipo_atencion',
            'diagnostico',
            'observaciones',
            'peso',
            'temperatura',
            'servicio_producto_id',
            'precio_servicio',
            'vacuna_nombre_select',
            'vacuna_nombre_custom',
            'vacuna_proxima_dosis',
            'tratamiento_descripcion',
            'tratamiento_fecha_fin',
            'receta_medicamentos',
            'receta_indicaciones',
            'seguimiento_motivo',
            'seguimiento_notas',
            'seguimiento_dias_retorno',
            'seguimiento_fecha_proximo_control',
            'seguimiento_hora_proximo_control',
        ].forEach((field) => setAttendFieldValue(form, field, ''));

        setAttendFieldValue(form, 'tipo_atencion', 'consulta');
        setAttendFieldValue(form, 'historia_fecha', defaultDate);
        setAttendFieldValue(form, 'vacuna_fecha_aplicacion', defaultDate);
        setAttendFieldValue(form, 'tratamiento_fecha_inicio', defaultDate);
        setAttendFieldValue(form, 'tratamiento_costo', 0);
        setAttendFieldValue(form, 'seguimiento_hora_proximo_control', '09:00');
        if (programarProximaVacuna) {
            programarProximaVacuna.checked = false;
        }
        updateAttendVacunaMode();
        applyAttendTypeState(true);
    }

    function updateAttendVacunaMode() {
        const {
            vacunaSelect,
            vacunaCustomWrap,
            vacunaCustomInput,
            vacunaSelectedLabel,
            programarProximaVacuna,
            proximaDosisWrap,
            proximaDosisInput,
            form,
        } = getAttendCitaElements();

        if (!vacunaSelect || !vacunaCustomWrap || !vacunaCustomInput) {
            return;
        }

        const isCustom = vacunaSelect.value === '__custom__';
        vacunaCustomWrap.classList.toggle('hidden', !isCustom);

        if (!isCustom) {
            vacunaCustomInput.value = '';
        }

        if (!vacunaSelectedLabel) {
            return;
        }

        const selectedOption = vacunaSelect.options[vacunaSelect.selectedIndex];
        const selectedText = selectedOption ? selectedOption.text.trim() : '';
        const customText = vacunaCustomInput.value.trim();
        const previewText = isCustom ? customText : selectedText;

        if (!previewText || previewText === 'Selecciona una vacuna' || previewText === 'Otra vacuna') {
            vacunaSelectedLabel.textContent = '';
            vacunaSelectedLabel.classList.add('hidden');
        } else {
            vacunaSelectedLabel.textContent = 'Vacuna seleccionada: ' + previewText;
            vacunaSelectedLabel.classList.remove('hidden');
        }

        if (programarProximaVacuna && proximaDosisWrap) {
            proximaDosisWrap.classList.toggle('hidden', !programarProximaVacuna.checked);

            if (!programarProximaVacuna.checked && proximaDosisInput) {
                proximaDosisInput.value = '';
            } else if (programarProximaVacuna.checked && proximaDosisInput && !proximaDosisInput.value) {
                proximaDosisInput.value = form?.dataset.defaultDate || new Date().toISOString().slice(0, 10);
            }
        }
    }

    function getAttendOptionalBlocks() {
        const { modal } = getAttendCitaElements();

        if (!modal) {
            return {};
        }

        return Array.from(modal.querySelectorAll('[data-optional-block]')).reduce((carry, element) => {
            const key = element.dataset.optionalBlock;
            carry[key] = {
                wrapper: element,
                body: element.querySelector('[data-optional-body]'),
                toggle: element.querySelector(`[data-optional-toggle="${key}"]`),
                label: element.querySelector('[data-optional-label]'),
                icon: element.querySelector('[data-optional-toggle] svg'),
            };
            return carry;
        }, {});
    }

    function attendOptionalBlockHasContent(key) {
        const { form } = getAttendCitaElements();

        if (!form) {
            return false;
        }

        if (key === 'servicio') {
            const precio = parseFloat(form.querySelector('[name="precio_servicio"]')?.value || '0');
            return Boolean(
                form.querySelector('[name="servicio_producto_id"]')?.value ||
                precio > 0
            );
        }

        if (key === 'vacuna') {
            return Boolean(
                form.querySelector('[name="vacuna_nombre_select"]')?.value ||
                form.querySelector('[name="vacuna_nombre_custom"]')?.value.trim() ||
                form.querySelector('[name="vacuna_proxima_dosis"]')?.value
            );
        }

        if (key === 'tratamiento') {
            const descripcion = form.querySelector('[name="tratamiento_descripcion"]')?.value.trim();
            const costo = parseFloat(form.querySelector('[name="tratamiento_costo"]')?.value || '0');
            const fechaFin = form.querySelector('[name="tratamiento_fecha_fin"]')?.value;
            return Boolean(descripcion || fechaFin || costo > 0);
        }

        if (key === 'receta') {
            return Boolean(
                form.querySelector('[name="receta_medicamentos"]')?.value.trim() ||
                form.querySelector('[name="receta_indicaciones"]')?.value.trim()
            );
        }

        if (key === 'seguimiento') {
            return Boolean(
                form.querySelector('[name="requiere_seguimiento"]')?.checked ||
                form.querySelector('[name="seguimiento_motivo"]')?.value.trim() ||
                form.querySelector('[name="seguimiento_notas"]')?.value.trim() ||
                form.querySelector('[name="seguimiento_fecha_proximo_control"]')?.value ||
                form.querySelector('[name="seguimiento_dias_retorno"]')?.value
            );
        }

        return false;
    }

    function setAttendBlockState(key, shouldOpen) {
        const block = getAttendOptionalBlocks()[key];

        if (!block || !block.body || !block.toggle || !block.label) {
            return;
        }

        block.body.classList.toggle('hidden', !shouldOpen);
        block.wrapper.dataset.open = shouldOpen ? 'true' : 'false';
        block.label.textContent = shouldOpen ? 'Ocultar' : 'Completar';
        setAttendSectionEnabled(block.body, shouldOpen);

        if (block.icon) {
            block.icon.classList.toggle('rotate-180', shouldOpen);
        }
    }

    function setAttendSectionEnabled(container, enabled) {
        if (!container) {
            return;
        }

        container.querySelectorAll('input, select, textarea').forEach((field) => {
            field.disabled = !enabled;
        });
    }

    function syncAttendServicePrice(force = false) {
        const { servicioSelect, servicioPrecio } = getAttendCitaElements();

        if (!servicioSelect || !servicioPrecio) {
            return;
        }

        const selectedOption = servicioSelect.options[servicioSelect.selectedIndex];
        const suggestedPrice = selectedOption?.dataset.price ?? '';

        if (force || !servicioPrecio.value) {
            servicioPrecio.value = suggestedPrice !== '' ? suggestedPrice : '';
        }
    }

    function syncAttendServiceSuggestion(payload = {}) {
        const { servicioSelect, servicioPrecio, servicioHint, form } = getAttendCitaElements();

        if (!servicioSelect || !servicioPrecio || !servicioHint || !form) {
            return;
        }

        const lastServiceId = payload.servicio_producto_id ?? form.dataset.lastServiceId ?? '';
        const lastServiceName = payload.servicio_nombre ?? form.dataset.lastServiceName ?? '';
        const lastServicePrice = payload.precio_servicio ?? form.dataset.lastServicePrice ?? '';
        const isServiceFlow = (form.querySelector('[name="tipo_atencion"]')?.value || 'consulta') === 'servicio';

        if (isServiceFlow && lastServiceId && !servicioSelect.value) {
            servicioSelect.value = String(lastServiceId);
        }

        if (isServiceFlow && !servicioPrecio.value) {
            syncAttendServicePrice();
            if (!servicioPrecio.value && lastServicePrice !== '') {
                servicioPrecio.value = lastServicePrice;
            }
        }

        if (lastServiceName) {
            const numericPrice = Number(lastServicePrice);
            const priceText = lastServicePrice !== '' && !Number.isNaN(numericPrice)
                ? ' por S/ ' + numericPrice.toFixed(2)
                : '';
            servicioHint.textContent = 'Ultimo servicio registrado: ' + lastServiceName + priceText + '.';
            return;
        }

        servicioHint.textContent = 'Selecciona el servicio realizado y el sistema puede reutilizar el servicio mas frecuente de la mascota.';
    }

    function updateAttendSeguimientoAutoDate() {
        const { form } = getAttendCitaElements();

        if (!form) {
            return;
        }

        const fechaBase = form.querySelector('[name="historia_fecha"]');
        const diasInput = form.querySelector('[name="seguimiento_dias_retorno"]');
        const fechaInput = form.querySelector('[name="seguimiento_fecha_proximo_control"]');
        const horaInput = form.querySelector('[name="seguimiento_hora_proximo_control"]');

        if (!fechaBase || !diasInput || !fechaInput || !fechaBase.value || !diasInput.value) {
            return;
        }

        const dias = parseInt(diasInput.value, 10);
        const baseDate = new Date(fechaBase.value + 'T00:00:00');

        if (Number.isNaN(dias) || dias < 1 || Number.isNaN(baseDate.getTime())) {
            return;
        }

        baseDate.setDate(baseDate.getDate() + dias);
        fechaInput.value = baseDate.toISOString().slice(0, 10);
        if (horaInput && !horaInput.value) {
            horaInput.value = '09:00';
        }
    }

    function applyAttendTypeState(force = false, payload = {}) {
        const { form, tipo, summary, clinicalNarrative } = getAttendCitaElements();

        if (!form || !tipo) {
            return;
        }

        syncAttendTypeShortcuts();

        const blocks = getAttendOptionalBlocks();
        const isServiceFlow = tipo.value === 'servicio';
        const recommended = {
            consulta: [],
            vacunacion: ['vacuna'],
            control: ['seguimiento'],
            desparasitacion: ['tratamiento'],
            servicio: ['servicio'],
            otro: [],
        }[tipo.value] || [];

        if (summary) {
            summary.textContent = attendTypeDefaults[tipo.value]?.summary || attendTypeDefaults.consulta.summary;
        }

        if (clinicalNarrative) {
            clinicalNarrative.classList.toggle('hidden', isServiceFlow);
            setAttendSectionEnabled(clinicalNarrative, !isServiceFlow);
        }

        if (isServiceFlow) {
            Object.entries(blocks).forEach(([key, block]) => {
                const shouldOpen = key === 'servicio';
                setAttendBlockState(key, shouldOpen);
            });

            setAttendFieldValue(form, 'diagnostico', '');
            setAttendFieldValue(form, 'observaciones', '');
            syncAttendServiceSuggestion(payload);
            syncAttendServicePrice();
            return;
        }

        Object.keys(blocks).forEach((key) => {
            const block = blocks[key];
            const hasContent = attendOptionalBlockHasContent(key);

            if (recommended.includes(key)) {
                setAttendBlockState(key, true);
                return;
            }

            if (force || !hasContent) {
                setAttendBlockState(key, hasContent);
            }
        });

        syncAttendServiceSuggestion(payload);
    }

    function syncAttendTypeShortcuts() {
        const { tipo } = getAttendCitaElements();
        const selectedType = tipo?.value || 'consulta';

        document.querySelectorAll('[data-attend-type]').forEach((button) => {
            const isActive = button.dataset.attendType === selectedType;
            button.dataset.active = isActive ? 'true' : 'false';
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function populateAttendCitaModal(payload = {}, preserveCurrentFields = false) {
        const { form, title, summary, citaId, photo, petName, owner, fecha, hora, vet, modal, programarProximaVacuna } = getAttendCitaElements();

        if (!form || !title || !summary || !citaId || !photo || !petName || !owner || !fecha || !hora || !vet || !modal) {
            return;
        }

        const defaultPhoto = modal.dataset.defaultImage || '/storage/default.png';
        const isEditing = Boolean(
            payload.diagnostico ||
            payload.observaciones ||
            payload.tratamiento_descripcion ||
            payload.receta_medicamentos ||
            payload.servicio_producto_id
        );

        form.action = (form.dataset.actionTemplate || '/citas/__ID__/atender').replace('__ID__', encodeURIComponent(payload.id || ''));
        citaId.value = payload.id || '';
        title.textContent = isEditing ? 'Actualizar atencion clinica' : 'Atender cita';
        summary.textContent = isEditing
            ? 'Edita el registro clinico generado desde esta cita.'
            : 'Usa el mismo flujo de Nueva atencion: registra solo lo realizado y completa la cita.';

        photo.src = payload.photo || defaultPhoto;
        petName.textContent = payload.mascota || 'Mascota seleccionada';
        owner.textContent = payload.owner || 'El propietario aparecera aqui.';
        fecha.textContent = payload.fecha_legible || '--/--/----';
        hora.textContent = payload.hora || '--:--';
        vet.textContent = payload.veterinario || 'Pendiente';
        form.dataset.lastServiceId = payload.servicio_producto_id || '';
        form.dataset.lastServiceName = payload.servicio_nombre || '';
        form.dataset.lastServicePrice = payload.precio_servicio || '';

        if (!preserveCurrentFields) {
            setAttendFieldValue(form, 'tipo_atencion', payload.tipo_atencion || 'consulta');
            setAttendFieldValue(form, 'historia_fecha', payload.historia_fecha || form.dataset.defaultDate);
            setAttendFieldValue(form, 'diagnostico', payload.diagnostico || '');
            setAttendFieldValue(form, 'observaciones', payload.observaciones || '');
            setAttendFieldValue(form, 'peso', payload.peso || '');
            setAttendFieldValue(form, 'temperatura', payload.temperatura || '');
            setAttendFieldValue(form, 'servicio_producto_id', payload.servicio_producto_id || '');
            setAttendFieldValue(form, 'precio_servicio', payload.precio_servicio || '');
            setAttendFieldValue(form, 'vacuna_nombre_select', payload.vacuna_nombre_select || '');
            setAttendFieldValue(form, 'vacuna_nombre_custom', payload.vacuna_nombre_custom || '');
            setAttendFieldValue(form, 'vacuna_fecha_aplicacion', payload.vacuna_fecha_aplicacion || payload.historia_fecha || form.dataset.defaultDate);
            setAttendFieldValue(form, 'vacuna_proxima_dosis', payload.vacuna_proxima_dosis || '');
            setAttendFieldValue(form, 'tratamiento_descripcion', payload.tratamiento_descripcion || '');
            setAttendFieldValue(form, 'tratamiento_costo', payload.tratamiento_costo ?? 0);
            setAttendFieldValue(form, 'tratamiento_fecha_inicio', payload.tratamiento_fecha_inicio || form.dataset.defaultDate);
            setAttendFieldValue(form, 'tratamiento_fecha_fin', payload.tratamiento_fecha_fin || '');
            setAttendFieldValue(form, 'receta_medicamentos', payload.receta_medicamentos || '');
            setAttendFieldValue(form, 'receta_indicaciones', payload.receta_indicaciones || '');
            setAttendFieldValue(form, 'seguimiento_motivo', payload.seguimiento_motivo || '');
            setAttendFieldValue(form, 'seguimiento_notas', payload.seguimiento_notas || '');
            setAttendFieldValue(form, 'seguimiento_dias_retorno', payload.seguimiento_dias_retorno || '');
            setAttendFieldValue(form, 'seguimiento_fecha_proximo_control', payload.seguimiento_fecha_proximo_control || '');
            setAttendFieldValue(form, 'seguimiento_hora_proximo_control', payload.seguimiento_hora_proximo_control || '09:00');
        }

        if (programarProximaVacuna) {
            programarProximaVacuna.checked = Boolean((payload.vacuna_proxima_dosis || '').trim());
        }

        updateAttendVacunaMode();
        applyAttendTypeState(preserveCurrentFields, payload);
        updateAttendSeguimientoAutoDate();
    }

    function openAttendModal() {
        const { modal } = getAttendCitaElements();

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    window.openCitaModal = function (mascotaId = '') {
        resetCitaForm(mascotaId);
        updateCitaSummary();
        openModal();
    };

    window.selectCitaMascotaForAppointment = function (mascotaId = '') {
        setSelectedCitaMascota(mascotaId);
    };

    window.openCitaClienteModal = function () {
        const { modal, card, form } = getCitaClienteModalElements();

        if (!modal || !card || !form) {
            return;
        }

        reopenMainAfterCliente = true;
        window.closeCitaModal();
        form.reset();
        animateOpen(modal, card);
    };

    window.closeCitaClienteModal = function () {
        const { modal, card } = getCitaClienteModalElements();

        if (!modal || !card) {
            return;
        }

        const shouldReopen = reopenMainAfterCliente;
        reopenMainAfterCliente = false;

        animateClose(modal, card, () => {
            if (shouldReopen) {
                openModal();
            }
        });
    };

    window.openCitaMascotaModal = function (selectedClienteId = '') {
        const { modal, card } = getCitaMascotaModalElements();

        if (!modal || !card) {
            return;
        }

        reopenMainAfterMascota = true;
        window.closeCitaModal();
        resetCitaMascotaForm(selectedClienteId);
        animateOpen(modal, card);
    };

    window.closeCitaMascotaModal = function () {
        const { modal, card } = getCitaMascotaModalElements();

        if (!modal || !card) {
            return;
        }

        const shouldReopen = reopenMainAfterMascota;
        reopenMainAfterMascota = false;

        animateClose(modal, card, () => {
            if (shouldReopen) {
                openModal();
            }
        });
    };

    window.openAttendCitaModal = function (payload) {
        resetAttendCitaForm();
        populateAttendCitaModal(payload || {});
        openAttendModal();
    };

    window.openEditCitaModal = function (cita) {
        const {
            form,
            mascotaSelect,
            veterinarioSelect,
            veterinarioInput,
            fechaInput,
            horaInput,
            estadoSelect,
            editingId,
        } = getCitaModalElements();

        if (!form) {
            return;
        }

        resetCitaForm();
        form.dataset.mode = 'edit';
        form.action = getUpdateAction(form, cita.id);
        ensureMethodInput(form).value = 'PUT';

        if (editingId) {
            editingId.value = cita.id;
        }

        if (mascotaSelect) {
            mascotaSelect.value = String(cita.mascota_id || '');
            syncCitaMascotaSelection();
        }

        if (veterinarioSelect && cita.veterinario_id) {
            veterinarioSelect.value = String(cita.veterinario_id);
        }

        if (veterinarioInput && !veterinarioSelect) {
            veterinarioInput.value = cita.veterinario_id || '';
        }

        if (fechaInput) {
            fechaInput.value = cita.fecha || getTodayValue(form);
            setDateMin(fechaInput, cita.fecha && cita.fecha < getTodayValue(form) ? cita.fecha : getTodayValue(form));
        }

        if (horaInput) {
            horaInput.value = cita.hora || (form.dataset.defaultTime || '09:00');
        }

        if (estadoSelect) {
            estadoSelect.value = cita.estado || (form.dataset.defaultStatus || 'pendiente');
        }

        updateCitaSummary();
        openModal();
    };

    window.closeCitaModal = function () {
        const { modal } = getCitaModalElements();

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    window.closeAttendCitaModal = function () {
        const { modal } = getAttendCitaElements();

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const {
            modal,
            mascotaSelect,
            mascotaSearch,
            mascotaClear,
            mascotaResults,
            form,
            fechaInput,
            editingId,
        } = getCitaModalElements();
        const attendElements = getAttendCitaElements();
        const clienteModal = getCitaClienteModalElements();
        const mascotaModal = getCitaMascotaModalElements();

        if (!modal || !form) {
            return;
        }

        portalModalToBody(clienteModal.modal);
        portalModalToBody(mascotaModal.modal);

        if (mascotaSelect) {
            mascotaSelect.addEventListener('change', () => {
                syncCitaMascotaSelection();
                updateCitaSummary();
            });
        }

        if (mascotaSearch) {
            mascotaSearch.addEventListener('focus', () => {
                renderCitaMascotaResults(mascotaSearch.value);
            });

            mascotaSearch.addEventListener('input', () => {
                if (mascotaSelect && mascotaSelect.value) {
                    const currentSearch = mascotaSearch.value;
                    mascotaSelect.value = '';
                    syncCitaMascotaSelection(currentSearch);
                    updateCitaSummary();
                }

                renderCitaMascotaResults(mascotaSearch.value);
            });
        }

        if (mascotaClear) {
            mascotaClear.addEventListener('click', () => {
                if (mascotaSelect) {
                    mascotaSelect.value = '';
                }

                syncCitaMascotaSelection();
                updateCitaSummary();

                if (mascotaSearch) {
                    mascotaSearch.focus();
                    renderCitaMascotaResults('');
                }
            });
        }

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeCitaModal();
            }
        });

        if (attendElements.modal) {
            attendElements.modal.addEventListener('click', (event) => {
                if (event.target === attendElements.modal) {
                    window.closeAttendCitaModal();
                }
            });
        }

        document.addEventListener('click', (event) => {
            if (!mascotaResults || mascotaResults.classList.contains('hidden')) {
                return;
            }

            const searchWrapper = mascotaSearch?.closest('.relative');

            if (searchWrapper && !searchWrapper.contains(event.target)) {
                hideCitaMascotaResults();
            }
        });

        if (clienteModal.modal) {
            clienteModal.modal.addEventListener('click', (event) => {
                if (event.target === clienteModal.modal) {
                    window.closeCitaClienteModal();
                }
            });
        }

        if (mascotaModal.modal) {
            mascotaModal.modal.addEventListener('click', (event) => {
                if (event.target === mascotaModal.modal) {
                    window.closeCitaMascotaModal();
                }
            });
        }

        if (attendElements.vacunaSelect) {
            attendElements.vacunaSelect.addEventListener('change', updateAttendVacunaMode);
        }

        if (attendElements.tipo) {
            attendElements.tipo.addEventListener('change', () => {
                applyAttendTypeState(false);
            });
        }

        document.querySelectorAll('[data-attend-type]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!attendElements.tipo) {
                    return;
                }

                attendElements.tipo.value = button.dataset.attendType || 'consulta';
                attendElements.tipo.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        if (attendElements.vacunaCustomInput) {
            attendElements.vacunaCustomInput.addEventListener('input', updateAttendVacunaMode);
        }

        if (attendElements.programarProximaVacuna) {
            attendElements.programarProximaVacuna.addEventListener('change', updateAttendVacunaMode);
        }

        if (attendElements.servicioSelect) {
            attendElements.servicioSelect.addEventListener('change', () => {
                syncAttendServicePrice(true);
                syncAttendServiceSuggestion();
            });
        }

        if (attendElements.form) {
            const seguimientoDias = attendElements.form.querySelector('[name="seguimiento_dias_retorno"]');
            const historiaFecha = attendElements.form.querySelector('[name="historia_fecha"]');

            if (seguimientoDias) {
                seguimientoDias.addEventListener('input', updateAttendSeguimientoAutoDate);
            }

            if (historiaFecha) {
                historiaFecha.addEventListener('change', updateAttendSeguimientoAutoDate);
            }
        }

        Object.entries(getAttendOptionalBlocks()).forEach(([key, block]) => {
            setAttendBlockState(key, block.wrapper.dataset.open === 'true');

            if (block.toggle) {
                block.toggle.addEventListener('click', () => {
                    const isOpen = block.wrapper.dataset.open === 'true';
                    setAttendBlockState(key, !isOpen);
                });
            }
        });

        if (mascotaModal.form && mascotaModal.tipoAnimal && mascotaModal.razaSelect && mascotaModal.razaInput && mascotaModal.otroContainer && mascotaModal.razaOtro) {
            updateCitaMascotaBreedOptions();
            updateCitaMascotaSummary();

            if (mascotaModal.clienteId) {
                mascotaModal.clienteId.addEventListener('change', updateCitaMascotaSummary);
            }

            if (mascotaModal.clienteSearch) {
                mascotaModal.clienteSearch.addEventListener('input', filterCitaModalClientes);
                filterCitaModalClientes();
            }

            mascotaModal.tipoAnimal.addEventListener('change', () => {
                mascotaModal.razaInput.dataset.current = '';
                updateCitaMascotaBreedOptions();
            });

            mascotaModal.razaSelect.addEventListener('change', function () {
                if (this.value === 'Otro') {
                    mascotaModal.otroContainer.classList.remove('hidden');
                    mascotaModal.razaInput.value = mascotaModal.razaOtro.value.trim();
                    return;
                }

                mascotaModal.otroContainer.classList.add('hidden');
                mascotaModal.razaOtro.value = '';
                mascotaModal.razaInput.value = this.value;
            });

            mascotaModal.razaOtro.addEventListener('input', function () {
                mascotaModal.razaInput.value = this.value.trim();
            });

            mascotaModal.form.addEventListener('submit', () => {
                if (mascotaModal.razaSelect.value === 'Otro') {
                    mascotaModal.razaInput.value = mascotaModal.razaOtro.value.trim();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (mascotaModal.modal && !mascotaModal.modal.classList.contains('hidden')) {
                window.closeCitaMascotaModal();
                return;
            }

            if (clienteModal.modal && !clienteModal.modal.classList.contains('hidden')) {
                window.closeCitaClienteModal();
                return;
            }

            if (attendElements.modal && !attendElements.modal.classList.contains('hidden')) {
                window.closeAttendCitaModal();
                return;
            }

            if (!modal.classList.contains('hidden')) {
                window.closeCitaModal();
            }
        });

        const uiState = window.citaUiState || {};

        if (window.citaClienteModalState?.hasErrors) {
            reopenMainAfterCliente = true;
            animateOpen(clienteModal.modal, clienteModal.card);
            return;
        }

        if (window.citaMascotaModalState?.hasErrors) {
            reopenMainAfterMascota = true;
            animateOpen(mascotaModal.modal, mascotaModal.card);

            if (window.citaMascotaModalState.clienteId && mascotaModal.clienteId) {
                mascotaModal.clienteId.value = String(window.citaMascotaModalState.clienteId);
            }

            updateCitaMascotaSummary();
            updateCitaMascotaBreedOptions();
            return;
        }

        if (uiState.open_mascota) {
            reopenMainAfterMascota = true;
            resetCitaMascotaForm(uiState.selected_cliente_id || '');
            animateOpen(mascotaModal.modal, mascotaModal.card);
            return;
        }

        if (uiState.open_main) {
            resetCitaForm(uiState.selected_mascota_id || '');
            updateCitaSummary();
            openModal();
            return;
        }

        if (window.citaModalState?.hasErrors || modal.dataset.openOnLoad === 'true') {
            if (window.citaModalState?.isEdit && window.citaModalState.editingId) {
                form.dataset.mode = 'edit';
                form.action = getUpdateAction(form, window.citaModalState.editingId);
                ensureMethodInput(form).value = 'PUT';

                if (editingId) {
                    editingId.value = window.citaModalState.editingId;
                }

                if (fechaInput) {
                    const currentDate = fechaInput.value || getTodayValue(form);
                    setDateMin(fechaInput, currentDate < getTodayValue(form) ? currentDate : getTodayValue(form));
                }
            } else if (modal.dataset.prefillMascota && mascotaSelect && !mascotaSelect.value) {
                mascotaSelect.value = modal.dataset.prefillMascota;
                syncCitaMascotaSelection();
            } else if (fechaInput) {
                setDateMin(fechaInput, getTodayValue(form));
            }

            syncCitaMascotaSelection();
            updateCitaSummary();
            openModal();
            return;
        }

        if (fechaInput) {
            setDateMin(fechaInput, getTodayValue(form));
        }

        form.dataset.mode = 'create';
        syncCitaMascotaSelection();
        updateCitaSummary();

        if (window.attendCitaModalState?.hasErrors && attendElements.form) {
            const payload = (window.attendCitaPresets || {})[window.attendCitaModalState.citaId] || { id: window.attendCitaModalState.citaId };
            populateAttendCitaModal(payload, true);
            openAttendModal();
        }

        updateAttendVacunaMode();
        applyAttendTypeState(false);
    });
})();
