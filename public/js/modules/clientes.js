const clienteMascotaModal = document.getElementById('mascotaModal');
const clienteMascotaContent = document.getElementById('modalContent');
const defaultMascotaImage = '/storage/default.png';
const createMascotaModal = document.getElementById('createMascotaModal');
const mascotaBreedCatalog = {
    Perro: ['Labrador', 'Pitbull', 'Pastor Aleman', 'Beagle', 'Pug', 'Otro'],
    Gato: ['Siames', 'Persa', 'Mestizo', 'Angora', 'Otro'],
    Ave: ['Canario', 'Loro', 'Perico', 'Otro'],
    Otro: ['Otro'],
};

function toggleMascotas(id) {
    const row = document.getElementById('mascotas-' + id);

    if (row) {
        row.classList.toggle('hidden');
    }
}

function openModal(id) {
    fetch('/mascotas/show-json/' + encodeURIComponent(id))
        .then((response) => response.json())
        .then((data) => {
            if (!clienteMascotaContent || !clienteMascotaModal) {
                return;
            }

            const photo = data.foto ? '/storage/' + data.foto : defaultMascotaImage;

            clienteMascotaContent.innerHTML = `
                <div class="space-y-5">
                    <img src="${photo}" alt="Foto de ${data.nombre}" class="h-56 w-full rounded-[22px] object-cover" onerror="this.onerror=null;this.src='${defaultMascotaImage}';">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">${data.nombre}</h2>
                        <p class="mt-1 text-sm text-slate-500">${data.raza || data.tipo_animal || 'Mascota registrada'}</p>
                    </div>
                    <div class="grid gap-3 rounded-3xl bg-slate-50 p-4 text-sm text-slate-600 sm:grid-cols-2">
                        <div>
                            <p class="font-medium text-slate-400">Due&ntilde;o</p>
                            <p class="mt-1 font-semibold text-slate-900">${data.cliente.nombre}</p>
                        </div>
                        <div>
                            <p class="font-medium text-slate-400">Edad</p>
                            <p class="mt-1 font-semibold text-slate-900">${data.edad} a&ntilde;os</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="font-medium text-slate-400">Especie</p>
                            <p class="mt-1 font-semibold text-slate-900">${data.tipo_animal || '-'}</p>
                        </div>
                    </div>
                </div>`;

            clienteMascotaModal.classList.remove('hidden');
            clienteMascotaModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        });
}

function closeModal() {
    if (!clienteMascotaModal) {
        return;
    }

    clienteMascotaModal.classList.add('hidden');
    clienteMascotaModal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function getMascotaCreateElements() {
    const form = document.getElementById('createMascotaForm');

    return {
        modal: createMascotaModal,
        card: createMascotaModal?.querySelector('.modal-card'),
        form,
        clienteId: document.getElementById('modal_cliente_id'),
        clienteMode: document.getElementById('modal_cliente_mode'),
        clienteModeButtons: Array.from(document.querySelectorAll('[data-cliente-mode-button]')),
        clienteSummary: document.getElementById('mascotaClienteSummary'),
        clienteSearch: document.getElementById('modal_cliente_search'),
        clienteResults: document.getElementById('mascotaClienteResults'),
        clienteSelectedCard: document.getElementById('mascotaClienteSelectedCard'),
        clienteSelectedName: document.getElementById('mascotaClienteSelectedName'),
        clienteSelectedMeta: document.getElementById('mascotaClienteSelectedMeta'),
        existingPanel: document.getElementById('mascotaClienteExistingPanel'),
        newPanel: document.getElementById('mascotaClienteNewPanel'),
        tipoAnimal: document.getElementById('modal_tipo_animal'),
        razaSelect: document.getElementById('modal_raza_select'),
        razaInput: document.getElementById('modal_raza'),
        otroContainer: document.getElementById('modal_input_otro_raza'),
        razaOtro: document.getElementById('modal_raza_otro'),
    };
}

function getMascotaClientCatalog() {
    return Array.isArray(window.mascotaClienteCatalog) ? window.mascotaClienteCatalog : [];
}

function setMascotaClientSummary(text) {
    const { clienteSummary } = getMascotaCreateElements();

    if (clienteSummary) {
        clienteSummary.textContent = text;
    }
}

function clearMascotaSelectedClient({ clearSearch = false } = {}) {
    const {
        clienteId,
        clienteSearch,
        clienteSelectedCard,
        clienteSelectedName,
        clienteSelectedMeta,
    } = getMascotaCreateElements();

    if (clienteId) {
        clienteId.value = '';
    }

    if (clearSearch && clienteSearch) {
        clienteSearch.value = '';
    }

    if (clienteSelectedCard) {
        clienteSelectedCard.classList.add('hidden');
    }

    if (clienteSelectedName) {
        clienteSelectedName.textContent = '';
    }

    if (clienteSelectedMeta) {
        clienteSelectedMeta.textContent = '';
    }
}

function renderMascotaClientResults() {
    const { clienteResults, clienteSearch, clienteId } = getMascotaCreateElements();

    if (!clienteResults) {
        return;
    }

    const catalog = getMascotaClientCatalog();
    const term = (clienteSearch?.value || '').trim().toLowerCase();
    const filtered = catalog
        .filter((cliente) => {
            if (!term) {
                return true;
            }

            return cliente.nombre.toLowerCase().includes(term) || (cliente.dni || '').includes(term);
        })
        .slice(0, term ? 8 : 6);

    if (!filtered.length) {
        clienteResults.innerHTML = `
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center">
                <p class="text-sm font-semibold text-slate-700">No encontramos clientes con esa busqueda.</p>
                <p class="mt-1 text-xs text-slate-500">Puedes registrarlo de inmediato desde la opcion Cliente nuevo.</p>
                <button type="button" id="mascotaUseNewClientTrigger" class="mt-3 inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    Registrar cliente nuevo
                </button>
            </div>
        `;

        const useNewClientTrigger = document.getElementById('mascotaUseNewClientTrigger');
        useNewClientTrigger?.addEventListener('click', () => {
            setMascotaCreateMode('new');
        });

        return;
    }

    clienteResults.innerHTML = filtered.map((cliente) => {
        const isSelected = String(cliente.id) === String(clienteId?.value || '');

        return `
            <button type="button"
                    data-mascota-cliente-id="${cliente.id}"
                    class="w-full rounded-2xl border px-4 py-3 text-left transition ${
                        isSelected
                            ? 'border-emerald-200 bg-emerald-50 shadow-sm'
                            : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'
                    }">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">${cliente.nombre}</p>
                        <p class="mt-1 text-xs text-slate-500">DNI ${cliente.dni || 'sin registrar'}</p>
                    </div>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold ${
                        isSelected ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'
                    }">
                        ${isSelected ? 'Seleccionado' : 'Usar'}
                    </span>
                </div>
            </button>
        `;
    }).join('');

    clienteResults.querySelectorAll('[data-mascota-cliente-id]').forEach((button) => {
        button.addEventListener('click', () => {
            const cliente = catalog.find((item) => String(item.id) === String(button.dataset.mascotaClienteId));
            if (cliente) {
                selectMascotaClient(cliente);
            }
        });
    });
}

function selectMascotaClient(cliente) {
    const {
        clienteId,
        clienteSearch,
        clienteSelectedCard,
        clienteSelectedName,
        clienteSelectedMeta,
    } = getMascotaCreateElements();

    if (clienteId) {
        clienteId.value = cliente.id;
    }

    if (clienteSearch) {
        clienteSearch.value = cliente.nombre;
    }

    if (clienteSelectedCard) {
        clienteSelectedCard.classList.remove('hidden');
    }

    if (clienteSelectedName) {
        clienteSelectedName.textContent = cliente.nombre;
    }

    if (clienteSelectedMeta) {
        clienteSelectedMeta.textContent = cliente.dni ? `DNI ${cliente.dni}` : 'Cliente listo para asociar a la mascota.';
    }

    setMascotaClientSummary('Estas registrando una mascota para ' + cliente.nombre + '.');
    renderMascotaClientResults();
}

function setMascotaCreateMode(mode, options = {}) {
    const {
        clienteMode,
        clienteModeButtons,
        existingPanel,
        newPanel,
    } = getMascotaCreateElements();

    if (!clienteMode) {
        return;
    }

    clienteMode.value = mode;

    clienteModeButtons.forEach((button) => {
        const isActive = button.dataset.clienteModeButton === mode;
        button.className = 'cliente-mode-button inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition ' + (
            isActive
                ? 'bg-emerald-600 text-white shadow-sm'
                : 'bg-transparent text-slate-600 hover:bg-slate-100'
        );
    });

    if (existingPanel) {
        existingPanel.classList.toggle('hidden', mode !== 'existing');
    }

    if (newPanel) {
        newPanel.classList.toggle('hidden', mode !== 'new');
    }

    if (mode === 'existing') {
        setMascotaClientSummary('Busca al cliente por nombre o DNI y continua con la mascota.');
        renderMascotaClientResults();
        return;
    }

    clearMascotaSelectedClient({ clearSearch: !options.preserveSearch });
    setMascotaClientSummary('Registra primero al cliente y la mascota quedara vinculada en el mismo guardado.');
}

function updateMascotaBreedOptions() {
    const { tipoAnimal, razaSelect, razaInput, otroContainer, razaOtro } = getMascotaCreateElements();

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

function bindMascotaBreedSelector() {
    const {
        form,
        tipoAnimal,
        razaSelect,
        razaInput,
        otroContainer,
        razaOtro,
        clienteSearch,
        clienteModeButtons,
    } = getMascotaCreateElements();

    if (!form || !tipoAnimal || !razaSelect || !razaInput || !otroContainer || !razaOtro) {
        return;
    }

    updateMascotaBreedOptions();

    tipoAnimal.addEventListener('change', () => {
        razaInput.dataset.current = '';
        updateMascotaBreedOptions();
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

    form.addEventListener('submit', () => {
        if (razaSelect.value === 'Otro') {
            razaInput.value = razaOtro.value.trim();
        }
    });

    if (clienteSearch) {
        clienteSearch.addEventListener('input', () => {
            renderMascotaClientResults();
        });
    }

    if (clienteModeButtons.length) {
        clienteModeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                setMascotaCreateMode(button.dataset.clienteModeButton);
            });
        });
    }
}

function resetMascotaCreateForm(form) {
    const { razaInput, clienteMode, clienteSearch } = getMascotaCreateElements();

    Array.from(form.querySelectorAll('input, select')).forEach((field) => {
        if (field.type === 'hidden' || field.type === 'file') {
            return;
        }

        field.value = '';
    });

    Array.from(form.querySelectorAll('input[type="file"]')).forEach((field) => {
        field.value = '';
    });

    if (razaInput) {
        razaInput.value = '';
        razaInput.dataset.current = '';
    }

    if (clienteMode) {
        clienteMode.value = 'existing';
    }

    clearMascotaSelectedClient({ clearSearch: true });

    if (clienteSearch) {
        clienteSearch.value = '';
    }

    updateMascotaBreedOptions();
    setMascotaCreateMode('existing');
}

window.openMascotaCreateModal = function (cliente) {
    const { modal, card, form, clienteId, razaInput } = getMascotaCreateElements();

    if (!modal || !card || !form || !clienteId) {
        return;
    }

    resetMascotaCreateForm(form);

    if (razaInput) {
        razaInput.dataset.current = '';
    }

    updateMascotaBreedOptions();

    if (cliente?.id) {
        setMascotaCreateMode('existing');
        selectMascotaClient(cliente);
    } else {
        setMascotaCreateMode('existing');
        renderMascotaClientResults();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    });
};

function closeMascotaCreateModal() {
    const { modal, card } = getMascotaCreateElements();

    if (!modal || !card) {
        return;
    }

    card.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 200);
}

function openCreateModal() {
    const modal = document.getElementById('createClienteModal');
    const card = modal?.querySelector('.modal-card');
    const form = document.getElementById('clienteForm');

    if (!modal || !card || !form) {
        return;
    }

    resetClienteForm(form);

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    });
}

function resetClienteForm(form) {
    form.action = form.dataset.storeAction || form.action;

    const method = form.querySelector('input[name="_method"]');
    if (method) {
        method.remove();
    }

    const editingId = form.querySelector('input[name="editing_id"]');
    if (editingId) {
        editingId.value = '';
    }

    getValidatableInputs(form).forEach((input) => {
        input.value = '';
        input.classList.remove('border-red-500', 'border-green-500');
        removeFieldMessage(input);
    });
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

function getUpdateAction(form, id) {
    const template = form.dataset.updateTemplate || '/clientes/__ID__';
    return template.replace('__ID__', encodeURIComponent(id));
}

function getValidatableInputs(form) {
    return Array.from(form.querySelectorAll('input')).filter((input) => input.type !== 'hidden');
}

function closeCreateModal() {
    const modal = document.getElementById('createClienteModal');
    const card = modal?.querySelector('.modal-card');

    if (!modal || !card) {
        return;
    }

    card.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 200);
}

function editCliente(cliente) {
    const form = document.getElementById('clienteForm');
    const modal = document.getElementById('createClienteModal');
    const card = modal?.querySelector('.modal-card');

    if (!form || !modal || !card) {
        return;
    }

    resetClienteForm(form);
    form.action = getUpdateAction(form, cliente.id);

    const method = ensureMethodInput(form);
    method.value = 'PUT';

    const editingId = form.querySelector('input[name="editing_id"]');
    if (editingId) {
        editingId.value = cliente.id;
    }

    form.dni.value = cliente.dni || '';
    form.nombre.value = cliente.nombre || '';
    form.telefono.value = cliente.telefono || '';
    form.email.value = cliente.email || '';
    form.direccion.value = cliente.direccion || '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    requestAnimationFrame(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    });
}

function validateField(input) {
    const value = input.value.trim();

    input.classList.remove('border-red-500', 'border-green-500');

    if (input.name === 'dni') {
        if (value === '' || value.length !== 8 || !/^\d+$/.test(value)) {
            setInvalid(input, 'DNI invalido (8 digitos)');
            return false;
        }
        setValid(input);
        return true;
    }

    if (input.name === 'telefono') {
        const digits = value.replace(/\D/g, '');

        if (value === '' || digits.length !== 9) {
            setInvalid(input, 'Celular invalido (9 digitos)');
            return false;
        }
        setValid(input);
        return true;
    }

    if (input.name === 'email') {
        if (value === '') {
            setValid(input);
            return true;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            setInvalid(input, 'Email invalido');
            return false;
        }
        setValid(input);
        return true;
    }

    if (value === '') {
        setInvalid(input, 'Campo obligatorio');
        return false;
    }

    setValid(input);
    return true;
}

function setInvalid(input, message) {
    input.classList.add('border-red-500');
    showFieldMessage(input, message);
}

function setValid(input) {
    input.classList.add('border-green-500');
    removeFieldMessage(input);
}

function showFieldMessage(input, message) {
    let error = input.parentElement.querySelector('.input-error');

    if (!error) {
        error = document.createElement('p');
        error.className = 'input-error mt-1 text-xs text-red-500';
        input.parentElement.appendChild(error);
    }

    error.innerText = message;
}

function removeFieldMessage(input) {
    const error = input.parentElement.querySelector('.input-error');
    if (error) {
        error.remove();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('clienteForm');
    const inputs = form ? getValidatableInputs(form) : [];

    inputs.forEach((input) => {
        input.addEventListener('input', () => {
            validateField(input);
        });
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            let hasError = false;

            inputs.forEach((input) => {
                if (!validateField(input)) {
                    hasError = true;
                }
            });

            if (hasError) {
                event.preventDefault();
                showToast('Completa los campos obligatorios', 'error');
            }
        });

        if (window.clienteModalState?.hasErrors) {
            const modal = document.getElementById('createClienteModal');
            const card = modal?.querySelector('.modal-card');

            if (window.clienteModalState.isEdit && window.clienteModalState.editingId) {
                form.action = getUpdateAction(form, window.clienteModalState.editingId);
                ensureMethodInput(form).value = 'PUT';

                const editingId = form.querySelector('input[name="editing_id"]');
                if (editingId) {
                    editingId.value = window.clienteModalState.editingId;
                }
            }

            if (modal && card) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }
        }
    }

    if (clienteMascotaModal) {
        clienteMascotaModal.addEventListener('click', (event) => {
            if (event.target === clienteMascotaModal) {
                closeModal();
            }
        });
    }

    if (createMascotaModal) {
        bindMascotaBreedSelector();

        createMascotaModal.addEventListener('click', (event) => {
            if (event.target === createMascotaModal) {
                closeMascotaCreateModal();
            }
        });

        if (window.mascotaCreateModalState?.hasErrors) {
            const { modal, card, clienteId } = getMascotaCreateElements();

            if (modal && card) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }

            if (window.mascotaCreateModalState.clienteMode === 'new') {
                setMascotaCreateMode('new', { preserveSearch: true });
            } else {
                setMascotaCreateMode('existing', { preserveSearch: true });

                if (clienteId?.value) {
                    const cliente = getMascotaClientCatalog().find((item) => String(item.id) === String(clienteId.value));

                    if (cliente) {
                        selectMascotaClient(cliente);
                    } else {
                        setMascotaClientSummary('Estas registrando una mascota para el cliente seleccionado.');
                        renderMascotaClientResults();
                    }
                } else {
                    renderMascotaClientResults();
                }
            }

            updateMascotaBreedOptions();
        }
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
            closeCreateModal();
            closeMascotaCreateModal();
        }
    });
});
