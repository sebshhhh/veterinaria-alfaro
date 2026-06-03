(function () {
    const typeDefaults = {
        consulta: {
            badge: 'Consulta sin cita',
            summary: 'Registra solo lo realizado. Tratamiento, receta y control son opcionales.',
            diagnostico: '',
            observaciones: '',
        },
        vacunacion: {
            badge: 'Vacunacion preventiva',
            summary: 'Completa la vacuna aplicada. Tratamiento, receta y control solo si tambien se usaron.',
            diagnostico: 'Vacunacion preventiva',
            observaciones: 'Atencion preventiva sin cita programada.',
        },
        control: {
            badge: 'Control sin cita',
            summary: 'Registra la revision del paciente y deja proximo control solo si debe volver.',
            diagnostico: 'Control clinico',
            observaciones: 'Paciente atendido en control clinico sin cita programada.',
        },
        desparasitacion: {
            badge: 'Desparasitacion preventiva',
            summary: 'Registra la desparasitacion y completa otros bloques solo si corresponde.',
            diagnostico: 'Desparasitacion preventiva',
            observaciones: 'Atencion preventiva para control antiparasitario sin cita programada.',
        },
        servicio: {
            badge: 'Servicio no clinico',
            summary: 'Usa esta opcion para bano, corte de pelo u otros servicios rapidos sin procedimiento clinico.',
            diagnostico: '',
            observaciones: '',
        },
        otro: {
            badge: 'Otra atencion',
            summary: 'Usa esta opcion para cualquier servicio directo que no entre en consulta, vacuna o control.',
            diagnostico: '',
            observaciones: '',
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

            if (!document.querySelector('.fixed.flex[id$="Modal"]')) {
                document.body.classList.remove('overflow-hidden');
            }

            if (typeof onClose === 'function') {
                onClose();
            }
        }, 200);
    }

    function els() {
        const form = document.getElementById('atencionRapidaForm');

        return {
            modal: document.getElementById('atencionRapidaModal'),
            card: document.querySelector('#atencionRapidaModal .modal-card'),
            form,
            summary: document.getElementById('atencionRapidaSummary'),
            mascota: document.getElementById('atencion_rapida_mascota_id'),
            mascotaSearch: document.getElementById('atencion_rapida_mascota_search'),
            mascotaResults: document.getElementById('atencionRapidaMascotaResults'),
            mascotaResultsList: document.getElementById('atencionRapidaMascotaResultsList'),
            mascotaSelected: document.getElementById('atencionRapidaSelectedMascota'),
            mascotaSelectedName: document.getElementById('atencionRapidaSelectedMascotaName'),
            mascotaSelectedMeta: document.getElementById('atencionRapidaSelectedMascotaMeta'),
            mascotaClear: document.getElementById('atencionRapidaClearMascota'),
            tipo: document.getElementById('atencion_rapida_tipo'),
            vet: document.getElementById('atencion_rapida_veterinario_id'),
            historiaFecha: document.getElementById('atencion_rapida_historia_fecha'),
            peso: document.getElementById('atencion_rapida_peso'),
            temperatura: document.getElementById('atencion_rapida_temperatura'),
            diagnostico: document.getElementById('atencion_rapida_diagnostico'),
            observaciones: document.getElementById('atencion_rapida_observaciones'),
            clinicalNarrative: document.getElementById('atencionRapidaClinicalNarrative'),
            vacunaSelect: document.getElementById('atencion_rapida_vacuna_nombre_select'),
            vacunaCustomWrap: document.getElementById('atencion_rapida_vacuna_custom_wrap'),
            vacunaCustom: document.getElementById('atencion_rapida_vacuna_nombre_custom'),
            vacunaFecha: document.getElementById('atencion_rapida_vacuna_fecha_aplicacion'),
            servicioSelect: document.getElementById('atencion_rapida_servicio_producto_id'),
            servicioPrecio: document.getElementById('atencion_rapida_precio_servicio'),
            servicioHint: document.getElementById('atencionRapidaServicioHint'),
            tratamientoInicio: document.getElementById('atencion_rapida_tratamiento_fecha_inicio'),
            photo: document.getElementById('atencionRapidaMascotaPhoto'),
            petName: document.getElementById('atencionRapidaMascotaName'),
            owner: document.getElementById('atencionRapidaMascotaOwner'),
            petType: document.getElementById('atencionRapidaMascotaTipo'),
            petColor: document.getElementById('atencionRapidaMascotaColor'),
            vetLabel: document.getElementById('atencionRapidaVet'),
            typeBadge: document.getElementById('atencionRapidaTypeBadge'),
        };
    }

    function clienteModalEls() {
        const modal = document.getElementById('atencionRapidaClienteModal');

        return {
            modal,
            card: modal?.querySelector('.modal-card'),
            form: modal?.querySelector('form'),
        };
    }

    function mascotaModalEls() {
        const modal = document.getElementById('atencionRapidaMascotaModal');
        const form = document.getElementById('atencionRapidaMascotaForm');

        return {
            modal,
            card: modal?.querySelector('.modal-card'),
            form,
            clienteSearch: document.getElementById('atencion_rapida_modal_cliente_search'),
            clienteId: document.getElementById('atencion_rapida_modal_cliente_id'),
            clienteSummary: document.getElementById('atencionRapidaMascotaSummary'),
            tipoAnimal: document.getElementById('atencion_rapida_modal_tipo_animal'),
            razaSelect: document.getElementById('atencion_rapida_modal_raza_select'),
            razaInput: document.getElementById('atencion_rapida_modal_raza'),
            otroContainer: document.getElementById('atencion_rapida_modal_input_otro_raza'),
            razaOtro: document.getElementById('atencion_rapida_modal_raza_otro'),
        };
    }

    function today(form) {
        return form?.dataset.defaultDate || new Date().toISOString().slice(0, 10);
    }

    function normalizeSearchText(value = '') {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function filterMascotaModalClientes() {
        const { clienteSearch, clienteId } = mascotaModalEls();

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

    function updateVacunaMode() {
        const { vacunaSelect, vacunaCustomWrap, vacunaCustom, form } = els();

        if (!vacunaSelect || !vacunaCustomWrap || !vacunaCustom) {
            return;
        }

        const isCustom = vacunaSelect.value === '__custom__';
        vacunaCustomWrap.classList.toggle('hidden', !isCustom);

        if (!isCustom) {
            vacunaCustom.value = '';
        }

        const toggle = document.getElementById('atencion_rapida_programar_proxima_vacuna');
        const wrap = document.getElementById('atencion_rapida_vacuna_proxima_dosis_wrap');
        const input = document.getElementById('atencion_rapida_vacuna_proxima_dosis');

        if (toggle && wrap) {
            wrap.classList.toggle('hidden', !toggle.checked);

            if (!toggle.checked && input) {
                input.value = '';
            } else if (toggle.checked && input && !input.value) {
                input.value = today(form);
            }
        }
    }

    function optionalBlockElements() {
        return Array.from(document.querySelectorAll('[data-optional-block]')).reduce((carry, element) => {
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

    function optionalBlockHasContent(key) {
        const { form } = els();

        if (!form) {
            return false;
        }

        if (key === 'vacuna') {
            return Boolean(
                form.querySelector('[name="vacuna_nombre_select"]')?.value ||
                form.querySelector('[name="vacuna_nombre_custom"]')?.value.trim() ||
                form.querySelector('[name="vacuna_proxima_dosis"]')?.value
            );
        }

        if (key === 'servicio') {
            const precio = parseFloat(form.querySelector('[name="precio_servicio"]')?.value || '0');
            return Boolean(
                form.querySelector('[name="servicio_producto_id"]')?.value ||
                precio > 0
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

    function setOptionalBlockState(key, shouldOpen) {
        const block = optionalBlockElements()[key];

        if (!block || !block.body || !block.toggle || !block.label) {
            return;
        }

        block.body.classList.toggle('hidden', !shouldOpen);
        block.wrapper.dataset.open = shouldOpen ? 'true' : 'false';
        block.label.textContent = shouldOpen ? 'Ocultar' : 'Completar';
        setSectionEnabled(block.body, shouldOpen);

        if (block.icon) {
            block.icon.classList.toggle('rotate-180', shouldOpen);
        }
    }

    function setSectionEnabled(container, enabled) {
        if (!container) {
            return;
        }

        container.querySelectorAll('input, select, textarea').forEach((field) => {
            field.disabled = !enabled;
        });
    }

    function syncServicioPrice(force = false) {
        const { servicioSelect, servicioPrecio } = els();

        if (!servicioSelect || !servicioPrecio) {
            return;
        }

        const selectedOption = servicioSelect.options[servicioSelect.selectedIndex];
        const suggestedPrice = selectedOption?.dataset.price ?? '';

        if (force || !servicioPrecio.value) {
            servicioPrecio.value = suggestedPrice !== '' ? suggestedPrice : '';
        }
    }

    function syncServicioSuggestion() {
        const { mascota, servicioSelect, servicioPrecio, servicioHint, tipo } = els();

        if (!mascota || !servicioSelect || !servicioPrecio || !servicioHint) {
            return;
        }

        const selectedMascota = mascota.options[mascota.selectedIndex];
        const lastServiceId = selectedMascota?.dataset.lastServiceId || '';
        const lastServiceName = selectedMascota?.dataset.lastServiceName || '';
        const lastServicePrice = selectedMascota?.dataset.lastServicePrice || '';

        if (!selectedMascota || !mascota.value) {
            servicioHint.textContent = 'Si la mascota ya tiene un servicio previo frecuente, el sistema lo puede sugerir automaticamente.';
            return;
        }

        if (tipo?.value === 'servicio' && lastServiceId && !servicioSelect.value) {
            servicioSelect.value = lastServiceId;
        }

        if (tipo?.value === 'servicio' && !servicioPrecio.value) {
            syncServicioPrice();
            if (!servicioPrecio.value && lastServicePrice !== '') {
                servicioPrecio.value = lastServicePrice;
            }
        }

        if (lastServiceName) {
            const priceText = lastServicePrice !== '' && lastServicePrice !== null
                ? ' por S/ ' + Number(lastServicePrice).toFixed(2)
                : '';
            servicioHint.textContent = 'Ultimo servicio frecuente: ' + lastServiceName + priceText + '.';
            return;
        }

        servicioHint.textContent = 'Aun no hay un servicio previo registrado para esta mascota.';
    }

    function updateSeguimientoAutoDate() {
        const { historiaFecha, form } = els();

        if (!form || !historiaFecha) {
            return;
        }

        const diasInput = form.querySelector('[name="seguimiento_dias_retorno"]');
        const fechaInput = form.querySelector('[name="seguimiento_fecha_proximo_control"]');
        const horaInput = form.querySelector('[name="seguimiento_hora_proximo_control"]');

        if (!diasInput || !fechaInput || !diasInput.value || !historiaFecha.value) {
            return;
        }

        const dias = parseInt(diasInput.value, 10);

        if (Number.isNaN(dias) || dias < 1) {
            return;
        }

        const baseDate = new Date(historiaFecha.value + 'T00:00:00');

        if (Number.isNaN(baseDate.getTime())) {
            return;
        }

        baseDate.setDate(baseDate.getDate() + dias);
        fechaInput.value = baseDate.toISOString().slice(0, 10);
        if (horaInput && !horaInput.value) {
            horaInput.value = '09:00';
        }
    }

    function applyOptionalBlockRecommendation(force = false) {
        const { tipo, clinicalNarrative, diagnostico, observaciones, form } = els();

        if (!tipo || !form) {
            return;
        }

        const recommended = {
            consulta: [],
            vacunacion: ['vacuna'],
            control: ['seguimiento'],
            desparasitacion: ['tratamiento'],
            servicio: ['servicio'],
            otro: [],
        }[tipo.value] || [];

        const blocks = optionalBlockElements();
        const isServiceFlow = tipo.value === 'servicio';

        if (clinicalNarrative) {
            clinicalNarrative.classList.toggle('hidden', isServiceFlow);
            setSectionEnabled(clinicalNarrative, !isServiceFlow);
        }

        if (isServiceFlow) {
            Object.entries(blocks).forEach(([key, block]) => {
                const shouldOpen = key === 'servicio';
                setOptionalBlockState(key, shouldOpen);
            });

            if (diagnostico) {
                diagnostico.value = '';
            }

            if (observaciones) {
                observaciones.value = '';
            }

            syncServicioSuggestion();
            syncServicioPrice();
            return;
        }

        Object.keys(blocks).forEach((key) => {
            const block = blocks[key];
            const hasContent = optionalBlockHasContent(key);
            if (recommended.includes(key)) {
                setOptionalBlockState(key, true);
                return;
            }

            if (force || !hasContent) {
                setOptionalBlockState(key, hasContent);
            }
        });
    }

    function applyTypeDefaults(force = false) {
        const { tipo, diagnostico, observaciones, summary, typeBadge } = els();

        if (!tipo || !diagnostico || !observaciones || !summary || !typeBadge) {
            return;
        }

        const config = typeDefaults[tipo.value] || typeDefaults.consulta;

        typeBadge.textContent = config.badge;
        summary.textContent = config.summary;

        if (force || !diagnostico.value.trim()) {
            diagnostico.value = config.diagnostico;
        }

        if (force || !observaciones.value.trim()) {
            observaciones.value = config.observaciones;
        }

        syncTypeShortcuts();
    }

    function syncTypeShortcuts() {
        const { tipo } = els();
        const selectedType = tipo?.value || 'consulta';

        document.querySelectorAll('[data-atencion-rapida-type]').forEach((button) => {
            const isActive = button.dataset.atencionRapidaType === selectedType;
            button.dataset.active = isActive ? 'true' : 'false';
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function syncPreview(preserveSearchValue = null) {
        const {
            mascota,
            vet,
            photo,
            petName,
            owner,
            petType,
            petColor,
            vetLabel,
            modal,
            mascotaSearch,
            mascotaSelected,
            mascotaSelectedName,
            mascotaSelectedMeta,
        } = els();

        if (!mascota || !photo || !petName || !owner || !petType || !petColor || !vetLabel || !modal) {
            return;
        }

        const selectedMascota = mascota.options[mascota.selectedIndex];
        const selectedVet = vet ? vet.options[vet.selectedIndex] : null;
        const defaultPhoto = modal.dataset.defaultImage || '/storage/default.png';

        if (!selectedMascota || !mascota.value) {
            photo.src = defaultPhoto;
            petName.textContent = 'Selecciona una mascota';
            owner.textContent = 'El propietario aparecera aqui.';
            petType.textContent = '--';
            petColor.textContent = '--';
            if (mascotaSelected) mascotaSelected.classList.add('hidden');
            if (mascotaSearch) {
                mascotaSearch.value = preserveSearchValue !== null ? preserveSearchValue : '';
            }
        } else {
            photo.src = selectedMascota.dataset.photo || defaultPhoto;
            petName.textContent = selectedMascota.dataset.label || selectedMascota.text.split(' - ')[0] || 'Mascota seleccionada';
            owner.textContent = selectedMascota.dataset.owner || 'Sin propietario registrado';
            petType.textContent = selectedMascota.dataset.type || '--';
            petColor.textContent = selectedMascota.dataset.color || 'Sin color registrado';
            if (mascotaSearch) {
                mascotaSearch.value = selectedMascota.dataset.owner
                    ? `${selectedMascota.dataset.label || ''} - ${selectedMascota.dataset.owner}`.trim()
                    : (selectedMascota.dataset.label || selectedMascota.text);
            }
            if (mascotaSelected && mascotaSelectedName && mascotaSelectedMeta) {
                mascotaSelected.classList.remove('hidden');
                mascotaSelected.classList.add('flex');
                mascotaSelectedName.textContent = selectedMascota.dataset.label || selectedMascota.text;
                const metaParts = [];
                if (selectedMascota.dataset.owner) metaParts.push(selectedMascota.dataset.owner);
                if (selectedMascota.dataset.dni) metaParts.push(`DNI ${selectedMascota.dataset.dni}`);
                mascotaSelectedMeta.textContent = metaParts.join(' - ') || 'Paciente seleccionado para esta atencion';
            }
        }

        vetLabel.textContent = selectedVet && vet.value
            ? (selectedVet.dataset.name || selectedVet.text)
            : 'Se asignara al guardar';
    }

    function getMascotaSearchItems() {
        const { mascota } = els();

        if (!mascota) {
            return [];
        }

        return Array.from(mascota.options)
            .filter((option) => option.value)
            .map((option) => ({
                value: option.value,
                label: option.dataset.label || option.text.split(' - ')[0] || option.text,
                owner: option.dataset.owner || '',
                dni: option.dataset.dni || '',
                type: option.dataset.type || '',
                option,
                search: [
                    option.dataset.label || '',
                    option.dataset.owner || '',
                    option.dataset.dni || '',
                    option.text || '',
                ].join(' ').toLowerCase(),
            }));
    }

    function hideMascotaResults() {
        const { mascotaResults } = els();

        if (!mascotaResults) {
            return;
        }

        mascotaResults.classList.add('hidden');
    }

    function renderMascotaResults(query = '') {
        const { mascotaResults, mascotaResultsList, mascota } = els();

        if (!mascotaResults || !mascotaResultsList || !mascota) {
            return;
        }

        const normalizedQuery = query.trim().toLowerCase();
        const selectedValue = mascota.value ? String(mascota.value) : '';
        const results = getMascotaSearchItems()
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
            button.className = `flex w-full items-start justify-between gap-3 rounded-2xl px-4 py-3 text-left transition ${
                selectedValue === item.value
                    ? 'bg-emerald-50 text-emerald-800'
                    : 'hover:bg-slate-50 text-slate-700'
            }`;

            const metaParts = [];
            if (item.owner) metaParts.push(item.owner);
            if (item.dni) metaParts.push(`DNI ${item.dni}`);

            button.innerHTML = `
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">${item.label}</p>
                    <p class="truncate text-xs text-slate-500">${metaParts.join(' - ') || 'Paciente registrado'}</p>
                </div>
                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">${item.type || 'Mascota'}</span>
            `;

            button.addEventListener('click', () => {
                setSelectedMascota(item.value);
                hideMascotaResults();
            });

            mascotaResultsList.appendChild(button);
        });

        mascotaResults.classList.remove('hidden');
    }

    function setSelectedMascota(mascotaId) {
        const { mascota } = els();

        if (!mascota || !mascotaId) {
            return;
        }

        mascota.value = String(mascotaId);
        hideMascotaResults();
        syncPreview();
        syncServicioSuggestion();
        if (els().tipo?.value === 'servicio') {
            syncServicioPrice();
        }
    }

    function resetForm() {
        const {
            form,
            mascota,
            mascotaSearch,
            mascotaSelected,
            tipo,
            vet,
            historiaFecha,
            peso,
            temperatura,
            diagnostico,
            observaciones,
            servicioSelect,
            servicioPrecio,
            vacunaSelect,
            vacunaCustom,
            vacunaFecha,
            tratamientoInicio,
        } = els();

        if (!form) {
            return;
        }

        if (mascota) mascota.value = '';
        if (mascotaSearch) mascotaSearch.value = '';
        if (mascotaSelected) mascotaSelected.classList.add('hidden');
        if (tipo) tipo.value = 'consulta';
        if (vet) vet.value = '';
        if (historiaFecha) historiaFecha.value = today(form);
        if (peso) peso.value = '';
        if (temperatura) temperatura.value = '';
        if (diagnostico) diagnostico.value = '';
        if (observaciones) observaciones.value = '';
        if (servicioSelect) servicioSelect.value = '';
        if (servicioPrecio) servicioPrecio.value = '';
        if (vacunaSelect) vacunaSelect.value = '';
        if (vacunaCustom) vacunaCustom.value = '';
        if (vacunaFecha) vacunaFecha.value = today(form);
        if (tratamientoInicio) tratamientoInicio.value = today(form);

        const programarProximaVacuna = document.getElementById('atencion_rapida_programar_proxima_vacuna');
        if (programarProximaVacuna) {
            programarProximaVacuna.checked = false;
        }

        [
            'vacuna_proxima_dosis',
            'tratamiento_descripcion',
            'tratamiento_costo',
            'tratamiento_fecha_fin',
            'receta_medicamentos',
            'receta_indicaciones',
            'seguimiento_motivo',
            'seguimiento_notas',
            'seguimiento_dias_retorno',
            'seguimiento_fecha_proximo_control',
            'seguimiento_hora_proximo_control',
        ].forEach((name) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field) field.value = name === 'tratamiento_costo' ? 0 : '';
        });

        const seguimientoHora = form.querySelector('[name="seguimiento_hora_proximo_control"]');
        if (seguimientoHora) seguimientoHora.value = '09:00';

        updateVacunaMode();
        applyTypeDefaults(true);
        applyOptionalBlockRecommendation(true);
        syncServicioSuggestion();
        syncPreview();
    }

    function updateMascotaBreedOptions() {
        const { tipoAnimal, razaSelect, razaInput, otroContainer, razaOtro } = mascotaModalEls();

        if (!tipoAnimal || !razaSelect || !razaInput || !otroContainer || !razaOtro) {
            return;
        }

        const tipo = tipoAnimal.value;
        const currentBreed = razaInput.dataset.current || razaInput.value || '';
        const breeds = mascotaBreedCatalog[tipo] || [];
        const normalizedBreed = currentBreed.trim();
        const hasPresetBreed = breeds.includes(normalizedBreed);

        razaSelect.innerHTML = '<option value="">Seleccione raza</option>';

        breeds.forEach((breed) => {
            const option = document.createElement('option');
            option.value = breed;
            option.textContent = breed;
            razaSelect.appendChild(option);
        });

        if (normalizedBreed && !hasPresetBreed) {
            razaSelect.value = 'Otro';
            otroContainer.classList.remove('hidden');
            razaOtro.value = normalizedBreed;
            razaInput.value = normalizedBreed;
            return;
        }

        otroContainer.classList.add('hidden');
        razaOtro.value = '';
        razaSelect.value = normalizedBreed || '';
        razaInput.value = normalizedBreed || '';
    }

    function resetMascotaForm(selectedClienteId = '') {
        const { form, clienteId, clienteSearch, razaInput, clienteSummary } = mascotaModalEls();

        if (!form) {
            return;
        }

        Array.from(form.querySelectorAll('input, select')).forEach((field) => {
            if (field.type === 'hidden' || field.type === 'file') {
                return;
            }

            field.value = '';
        });

        Array.from(form.querySelectorAll('input[type="file"]')).forEach((field) => {
            field.value = '';
        });

        if (clienteId) {
            clienteId.value = selectedClienteId ? String(selectedClienteId) : '';
        }

        if (clienteSearch) {
            clienteSearch.value = '';
            filterMascotaModalClientes();
        }

        if (clienteSummary) {
            clienteSummary.textContent = selectedClienteId
                ? 'La mascota quedara vinculada al cliente seleccionado para continuar con la atencion.'
                : 'Registra a la mascota para continuar con la atencion sin cita.';
        }

        if (razaInput) {
            razaInput.value = '';
            razaInput.dataset.current = '';
        }

        updateMascotaBreedOptions();
    }

    function openMainModal(selectedMascotaId = '') {
        const { modal, card } = els();

        if (!modal || !card) {
            return;
        }

        if (selectedMascotaId) {
            setSelectedMascota(selectedMascotaId);
        }

        updateVacunaMode();
        applyTypeDefaults(false);
        applyOptionalBlockRecommendation(false);
        syncServicioSuggestion();
        syncPreview();
        animateOpen(modal, card);
    }

    function closeMainModal() {
        const { modal, card } = els();

        if (!modal || !card) {
            return;
        }

        animateClose(modal, card);
    }

    window.openAtencionRapidaModal = function (selectedMascotaId = '') {
        resetForm();

        if (selectedMascotaId) {
            setSelectedMascota(selectedMascotaId);
        }

        openMainModal(selectedMascotaId);
    };

    window.selectAtencionRapidaMascota = function (selectedMascotaId = '') {
        if (!selectedMascotaId) {
            return;
        }

        setSelectedMascota(selectedMascotaId);
    };

    window.closeAtencionRapidaModal = function () {
        closeMainModal();
    };

    window.openAtencionRapidaClienteModal = function () {
        const { modal, card, form } = clienteModalEls();

        if (!modal || !card || !form) {
            return;
        }

        reopenMainAfterCliente = true;
        closeMainModal();
        form.reset();
        animateOpen(modal, card);
    };

    window.closeAtencionRapidaClienteModal = function () {
        const { modal, card } = clienteModalEls();

        if (!modal || !card) {
            return;
        }

        const shouldReopen = reopenMainAfterCliente;
        reopenMainAfterCliente = false;
        animateClose(modal, card, () => {
            if (shouldReopen) {
                openMainModal();
            }
        });
    };

    window.openAtencionRapidaMascotaModal = function (selectedClienteId = '') {
        const { modal, card } = mascotaModalEls();

        if (!modal || !card) {
            return;
        }

        reopenMainAfterMascota = true;
        closeMainModal();
        resetMascotaForm(selectedClienteId);
        animateOpen(modal, card);
    };

    window.closeAtencionRapidaMascotaModal = function () {
        const { modal, card } = mascotaModalEls();

        if (!modal || !card) {
            return;
        }

        const shouldReopen = reopenMainAfterMascota;
        reopenMainAfterMascota = false;
        animateClose(modal, card, () => {
            if (shouldReopen) {
                openMainModal();
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, mascota, mascotaSearch, mascotaClear, mascotaResults, tipo, vet, vacunaSelect, vacunaCustom, form } = els();
        const clienteModal = clienteModalEls();
        const mascotaModal = mascotaModalEls();

        if (!modal || !form) {
            return;
        }

        portalModalToBody(modal);
        portalModalToBody(clienteModal.modal);
        portalModalToBody(mascotaModal.modal);

        if (mascota) mascota.addEventListener('change', syncPreview);
        if (mascotaSearch) {
            mascotaSearch.addEventListener('focus', () => {
                renderMascotaResults(mascotaSearch.value);
            });

            mascotaSearch.addEventListener('input', () => {
                if (mascota && mascota.value) {
                    const currentSearch = mascotaSearch.value;
                    mascota.value = '';
                    syncPreview(currentSearch);
                }
                renderMascotaResults(mascotaSearch.value);
            });
        }
        if (mascotaClear) {
            mascotaClear.addEventListener('click', () => {
                if (mascota) {
                    mascota.value = '';
                }
                syncPreview();
                if (mascotaSearch) {
                    mascotaSearch.focus();
                    renderMascotaResults('');
                }
            });
        }
        if (vet) vet.addEventListener('change', syncPreview);
        if (tipo) {
            tipo.addEventListener('change', () => {
                applyTypeDefaults(false);
                applyOptionalBlockRecommendation(false);
                syncServicioSuggestion();
                syncTypeShortcuts();
            });
        }

        document.querySelectorAll('[data-atencion-rapida-type]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!tipo) {
                    return;
                }

                tipo.value = button.dataset.atencionRapidaType || 'consulta';
                tipo.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
        const { servicioSelect, historiaFecha } = els();
        if (servicioSelect) {
            servicioSelect.addEventListener('change', () => {
                syncServicioPrice(true);
                syncServicioSuggestion();
            });
        }
        if (vacunaSelect) vacunaSelect.addEventListener('change', updateVacunaMode);
        if (vacunaCustom) vacunaCustom.addEventListener('input', updateVacunaMode);
        const programarProximaVacuna = document.getElementById('atencion_rapida_programar_proxima_vacuna');
        if (programarProximaVacuna) {
            programarProximaVacuna.addEventListener('change', updateVacunaMode);
        }
        const seguimientoDias = form.querySelector('[name="seguimiento_dias_retorno"]');
        if (seguimientoDias) {
            seguimientoDias.addEventListener('input', updateSeguimientoAutoDate);
        }
        if (historiaFecha) {
            historiaFecha.addEventListener('change', updateSeguimientoAutoDate);
        }

        Object.entries(optionalBlockElements()).forEach(([key, block]) => {
            setOptionalBlockState(key, block.wrapper.dataset.open === 'true');

            if (block.toggle) {
                block.toggle.addEventListener('click', () => {
                    const isOpen = block.wrapper.dataset.open === 'true';
                    setOptionalBlockState(key, !isOpen);
                });
            }
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeAtencionRapidaModal();
            }
        });

        document.addEventListener('click', (event) => {
            if (!mascotaResults || mascotaResults.classList.contains('hidden')) {
                return;
            }

            const searchWrapper = mascotaSearch?.closest('.relative');
            if (searchWrapper && !searchWrapper.contains(event.target)) {
                hideMascotaResults();
            }
        });

        if (clienteModal.modal) {
            clienteModal.modal.addEventListener('click', (event) => {
                if (event.target === clienteModal.modal) {
                    window.closeAtencionRapidaClienteModal();
                }
            });
        }

        if (mascotaModal.modal) {
            mascotaModal.modal.addEventListener('click', (event) => {
                if (event.target === mascotaModal.modal) {
                    window.closeAtencionRapidaMascotaModal();
                }
            });
        }

        if (mascotaModal.form && mascotaModal.tipoAnimal && mascotaModal.razaSelect && mascotaModal.razaInput && mascotaModal.otroContainer && mascotaModal.razaOtro) {
            updateMascotaBreedOptions();

            if (mascotaModal.clienteSearch) {
                mascotaModal.clienteSearch.addEventListener('input', filterMascotaModalClientes);
                filterMascotaModalClientes();
            }

            mascotaModal.tipoAnimal.addEventListener('change', () => {
                mascotaModal.razaInput.dataset.current = '';
                updateMascotaBreedOptions();
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
                window.closeAtencionRapidaMascotaModal();
                return;
            }

            if (clienteModal.modal && !clienteModal.modal.classList.contains('hidden')) {
                window.closeAtencionRapidaClienteModal();
                return;
            }

            if (!modal.classList.contains('hidden')) {
                window.closeAtencionRapidaModal();
            }
        });

        const uiState = window.atencionRapidaUiState || {};

        if (window.atencionRapidaClienteModalState?.hasErrors) {
            reopenMainAfterCliente = true;
            animateOpen(clienteModal.modal, clienteModal.card);
            return;
        }

        if (window.atencionRapidaMascotaModalState?.hasErrors) {
            reopenMainAfterMascota = true;
            animateOpen(mascotaModal.modal, mascotaModal.card);
            if (window.atencionRapidaMascotaModalState.clienteId && mascotaModal.clienteId) {
                mascotaModal.clienteId.value = String(window.atencionRapidaMascotaModalState.clienteId);
            }
            updateMascotaBreedOptions();
            return;
        }

        if (uiState.open_mascota) {
            reopenMainAfterMascota = true;
            resetMascotaForm(uiState.selected_cliente_id || '');
            animateOpen(mascotaModal.modal, mascotaModal.card);
            return;
        }

        if (uiState.open_main) {
            resetForm();
            if (uiState.selected_mascota_id) {
                setSelectedMascota(uiState.selected_mascota_id);
            }
            openMainModal(uiState.selected_mascota_id || '');
            return;
        }

        if (window.atencionRapidaModalState?.hasErrors || modal.dataset.openOnLoad === 'true') {
            updateVacunaMode();
            applyTypeDefaults(false);
            applyOptionalBlockRecommendation(false);
            syncServicioSuggestion();
            updateSeguimientoAutoDate();
            syncPreview();
            openMainModal();
            return;
        }

        applyTypeDefaults(false);
        applyOptionalBlockRecommendation(false);
        syncServicioSuggestion();
        syncPreview();
    });
})();
