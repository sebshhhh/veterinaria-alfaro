(function () {
    function els() {
        const form = document.getElementById('ventaForm');

        return {
            modal: document.getElementById('ventaModal'),
            form,
            title: document.getElementById('ventaModalTitle'),
            summary: document.getElementById('ventaModalSummary'),
            submit: document.getElementById('ventaSubmitLabel'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            contextSearch: document.getElementById('venta_context_search'),
            contextHint: document.getElementById('ventaContextHint'),
            clearPatientButton: document.getElementById('ventaClearPatientButton'),
            pacienteOptions: document.getElementById('ventaPacienteOptions'),
            posSearch: document.getElementById('venta_pos_search'),
            posCatalog: document.getElementById('ventaPosCatalog'),
            cliente: document.getElementById('venta_cliente_id'),
            mascota: document.getElementById('venta_mascota_id'),
            historia: document.getElementById('venta_historia_id'),
            metodo: document.getElementById('venta_metodo_pago'),
            estado: document.getElementById('venta_estado'),
            fecha: document.getElementById('venta_fecha'),
            itemsList: document.getElementById('ventaItemsList'),
            itemTemplate: document.getElementById('ventaItemTemplate'),
            addItemButton: document.getElementById('ventaAddItemButton'),
            previewCliente: document.getElementById('ventaPreviewCliente'),
            previewMascota: document.getElementById('ventaPreviewMascota'),
            previewEstado: document.getElementById('ventaPreviewEstado'),
            previewMetodo: document.getElementById('ventaPreviewMetodo'),
            previewItems: document.getElementById('ventaPreviewItems'),
            previewTotal: document.getElementById('ventaPreviewTotal'),
        };
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
        return (form?.dataset.updateTemplate || '/ventas/__ID__').replace('__ID__', encodeURIComponent(id));
    }

    function parseJson(value, fallback = null) {
        try {
            return JSON.parse(value || 'null') ?? fallback;
        } catch (error) {
            return fallback;
        }
    }

    function normalize(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function formatLabel(value) {
        return value ? value.charAt(0).toUpperCase() + value.slice(1) : 'Sin definir';
    }

    function updateItemNames() {
        const { itemsList } = els();
        if (!itemsList) {
            return;
        }

        itemsList.querySelectorAll('.venta-item-row').forEach((row, index) => {
            row.querySelector('[data-role="item-type"]').name = `items[${index}][tipo]`;
            row.querySelector('[data-role="item-producto"]').name = `items[${index}][producto_id]`;
            row.querySelector('[data-role="item-tratamiento"]').name = `items[${index}][tratamiento_id]`;
            row.querySelector('[data-role="item-cantidad"]').name = `items[${index}][cantidad]`;
            row.querySelector('[data-role="item-precio"]').name = `items[${index}][precio]`;
        });
    }

    function getSelectedClienteName() {
        const { cliente } = els();
        const selected = cliente?.options[cliente.selectedIndex];
        return cliente?.value && selected ? (selected.dataset.name || selected.textContent.trim()) : 'Venta rápida / sin cliente';
    }

    function getSelectedMascotaName() {
        const { mascota } = els();
        const selected = mascota?.options[mascota.selectedIndex];
        return mascota?.value && selected ? (selected.dataset.name || selected.textContent.trim()) : 'Sin mascota asociada.';
    }

    function filterMascotasAndHistorias() {
        const { cliente, mascota, historia } = els();
        const clienteId = cliente?.value || '';
        const mascotaId = mascota?.value || '';

        if (mascota) {
            Array.from(mascota.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const visible = !clienteId || option.dataset.clienteId === clienteId;
                option.hidden = !visible;
                option.disabled = !visible;
            });

            const selectedMascota = mascota.options[mascota.selectedIndex];
            if (selectedMascota?.value && selectedMascota.disabled) {
                mascota.value = '';
            }
        }

        if (historia) {
            Array.from(historia.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const visibleByMascota = !mascotaId || option.dataset.mascotaId === mascotaId;
                const visibleByCliente = !clienteId || option.dataset.clienteId === clienteId;
                const visible = visibleByMascota && visibleByCliente;
                option.hidden = !visible;
                option.disabled = !visible;
            });

            const selectedHistoria = historia.options[historia.selectedIndex];
            if (selectedHistoria?.value && selectedHistoria.disabled) {
                historia.value = '';
            }
        }
    }

    function syncContextFromMascota() {
        const { cliente, mascota } = els();
        const selected = mascota?.options[mascota.selectedIndex];

        if (selected?.dataset.clienteId && cliente) {
            cliente.value = selected.dataset.clienteId;
        }

        filterMascotasAndHistorias();
        syncPreview();
    }

    function syncContextFromHistoria() {
        const { cliente, mascota, historia } = els();
        const selected = historia?.options[historia.selectedIndex];

        if (selected?.dataset.clienteId && cliente) {
            cliente.value = selected.dataset.clienteId;
        }

        if (selected?.dataset.mascotaId && mascota) {
            mascota.value = selected.dataset.mascotaId;
        }

        filterMascotasAndHistorias();
        syncPreview();
    }

    function syncContextFromQuickSearch() {
        const { contextSearch, contextHint, pacienteOptions, cliente, mascota } = els();
        const value = contextSearch?.value || '';

        if (!contextSearch || !pacienteOptions) {
            return;
        }

        if (!value.trim()) {
            if (cliente) cliente.value = '';
            if (mascota) mascota.value = '';
            if (contextHint) {
                contextHint.textContent = 'Venta rápida sin cliente por defecto. Busca una mascota solo si deseas asociar el cobro.';
            }
            filterMascotasAndHistorias();
            syncPreview();
            return;
        }

        const match = Array.from(pacienteOptions.options).find((option) => option.value === value);

        if (!match) {
            if (contextHint) {
                contextHint.textContent = value.trim()
                    ? 'Elige una opción exacta de la lista para asociar el paciente.'
                    : 'Venta rápida sin cliente por defecto. Busca una mascota solo si deseas asociar el cobro.';
            }
            return;
        }

        if (cliente && match.dataset.clienteId) {
            cliente.value = match.dataset.clienteId;
        }

        if (mascota && match.dataset.mascotaId) {
            mascota.value = match.dataset.mascotaId;
        }

        if (contextHint) {
            contextHint.textContent = 'Paciente seleccionado. El cobro quedará conectado a su ficha.';
        }

        filterMascotasAndHistorias();
        syncPreview();
    }

    function clearPatientContext() {
        const { contextSearch, contextHint, cliente, mascota, historia } = els();

        if (contextSearch) contextSearch.value = '';
        if (cliente) cliente.value = '';
        if (mascota) mascota.value = '';
        if (historia) historia.value = '';
        if (contextHint) {
            contextHint.textContent = 'Venta rápida sin cliente por defecto. Busca una mascota solo si deseas asociar el cobro.';
        }

        filterMascotasAndHistorias();
        syncPreview();
    }

    function syncMainPreview() {
        const { previewCliente, previewMascota, previewEstado, previewMetodo, estado, metodo } = els();

        if (previewCliente) previewCliente.textContent = getSelectedClienteName();
        if (previewMascota) previewMascota.textContent = getSelectedMascotaName();
        if (previewEstado) previewEstado.textContent = formatLabel(estado?.value);
        if (previewMetodo) previewMetodo.textContent = formatLabel(metodo?.value);
    }

    function syncItemsPreview() {
        const { itemsList, previewItems, previewTotal } = els();

        if (!itemsList) {
            return;
        }

        let total = 0;
        const lines = [];

        itemsList.querySelectorAll('.venta-item-row').forEach((row) => {
            const type = row.querySelector('[data-role="item-type"]').value;
            const qty = Number(row.querySelector('[data-role="item-cantidad"]').value || 0);
            const price = Number(row.querySelector('[data-role="item-precio"]').value || 0);
            const select = type === 'producto'
                ? row.querySelector('[data-role="item-producto"]')
                : row.querySelector('[data-role="item-tratamiento"]');
            const selected = select?.options[select.selectedIndex];

            if (!select?.value || !selected) {
                return;
            }

            const label = selected.dataset.name || selected.textContent.trim();
            const subtotal = qty * price;
            total += subtotal;
            lines.push(`${label} x${qty} · S/ ${subtotal.toFixed(2)}`);
        });

        if (previewItems) previewItems.textContent = lines.length ? lines.join(' | ') : 'Aún no se agregaron items.';
        if (previewTotal) previewTotal.textContent = 'S/ ' + total.toFixed(2);
    }

    function syncPreview() {
        syncMainPreview();
        syncItemsPreview();
    }

    function activeItemSelect(row) {
        const type = row.querySelector('[data-role="item-type"]').value;
        return type === 'producto'
            ? row.querySelector('[data-role="item-producto"]')
            : row.querySelector('[data-role="item-tratamiento"]');
    }

    function filterItemOptions(row) {
        const search = row.querySelector('[data-role="item-search"]');
        const query = normalize(search?.value || '');
        const select = activeItemSelect(row);

        if (!select) {
            return;
        }

        Array.from(select.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const text = normalize(`${option.dataset.name || ''} ${option.textContent || ''}`);
            const visible = !query || text.includes(query);
            option.hidden = !visible;
            option.disabled = !visible;
        });

        const selected = select.options[select.selectedIndex];
        if (selected?.value && selected.disabled) {
            select.value = '';
            setPriceFromSelection(row);
            syncPreview();
        }
    }

    function setPriceFromSelection(row) {
        const activeSelect = activeItemSelect(row);
        const priceInput = row.querySelector('[data-role="item-precio"]');
        const qtyInput = row.querySelector('[data-role="item-cantidad"]');
        const selected = activeSelect?.options[activeSelect.selectedIndex];
        const price = selected?.dataset.price || 0;

        if (priceInput) priceInput.value = Number(price).toFixed(2);
        if (qtyInput) qtyInput.value = qtyInput.value || '1';
    }

    function toggleItemType(row) {
        const type = row.querySelector('[data-role="item-type"]').value;
        const productSelect = row.querySelector('[data-role="item-producto"]');
        const treatmentSelect = row.querySelector('[data-role="item-tratamiento"]');
        const search = row.querySelector('[data-role="item-search"]');

        if (search) {
            search.value = '';
        }

        if (type === 'producto') {
            productSelect.classList.remove('hidden');
            treatmentSelect.classList.add('hidden');
            treatmentSelect.value = '';
        } else {
            treatmentSelect.classList.remove('hidden');
            productSelect.classList.add('hidden');
            productSelect.value = '';
        }

        filterItemOptions(row);
        setPriceFromSelection(row);
        syncPreview();
    }

    function syncContextFromTreatment(row) {
        const treatmentSelect = row.querySelector('[data-role="item-tratamiento"]');
        const selected = treatmentSelect.options[treatmentSelect.selectedIndex];
        const { cliente, mascota, historia } = els();

        if (!treatmentSelect.value || !selected) {
            return;
        }

        if (selected.dataset.clienteId && cliente) {
            cliente.value = selected.dataset.clienteId;
        }

        if (selected.dataset.mascotaId && mascota) {
            mascota.value = selected.dataset.mascotaId;
        }

        if (selected.dataset.historiaId && historia) {
            historia.value = selected.dataset.historiaId;
        }

        filterMascotasAndHistorias();
        syncMainPreview();
    }

    function bindItemRow(row) {
        const type = row.querySelector('[data-role="item-type"]');
        const productSelect = row.querySelector('[data-role="item-producto"]');
        const treatmentSelect = row.querySelector('[data-role="item-tratamiento"]');
        const qty = row.querySelector('[data-role="item-cantidad"]');
        const search = row.querySelector('[data-role="item-search"]');
        const removeButton = row.querySelector('[data-role="remove-item"]');

        type.addEventListener('change', () => toggleItemType(row));
        search?.addEventListener('input', () => filterItemOptions(row));
        productSelect.addEventListener('change', () => {
            setPriceFromSelection(row);
            syncPreview();
        });
        treatmentSelect.addEventListener('change', () => {
            setPriceFromSelection(row);
            syncContextFromTreatment(row);
            syncPreview();
        });
        qty.addEventListener('input', syncPreview);
        removeButton.addEventListener('click', () => {
            row.remove();
            updateItemNames();
            syncPreview();
        });
    }

    function addItemRow(item = null) {
        const { itemsList, itemTemplate } = els();

        if (!itemsList || !itemTemplate) {
            return null;
        }

        const fragment = itemTemplate.content.cloneNode(true);
        const type = fragment.querySelector('[data-role="item-type"]');
        const productSelect = fragment.querySelector('[data-role="item-producto"]');
        const treatmentSelect = fragment.querySelector('[data-role="item-tratamiento"]');
        const qty = fragment.querySelector('[data-role="item-cantidad"]');

        if (item?.tipo) {
            type.value = item.tipo;
        }
        if (item?.producto_id) {
            productSelect.value = String(item.producto_id);
        }
        if (item?.tratamiento_id) {
            treatmentSelect.value = String(item.tratamiento_id);
        }
        if (item?.cantidad) {
            qty.value = String(item.cantidad);
        }

        itemsList.appendChild(fragment);
        const appendedRow = itemsList.lastElementChild;
        bindItemRow(appendedRow);
        toggleItemType(appendedRow);

        if (item?.precio !== undefined && item?.precio !== null && item?.precio !== '') {
            appendedRow.querySelector('[data-role="item-precio"]').value = Number(item.precio).toFixed(2);
        }

        updateItemNames();
        syncPreview();

        return appendedRow;
    }

    function findProductRow(productId) {
        const { itemsList } = els();
        if (!itemsList) {
            return null;
        }

        return Array.from(itemsList.querySelectorAll('.venta-item-row')).find((row) => {
            return row.querySelector('[data-role="item-type"]').value === 'producto'
                && row.querySelector('[data-role="item-producto"]').value === String(productId);
        }) || null;
    }

    function addProductFromPos(button) {
        if (!button || button.disabled) {
            return;
        }

        const productId = button.dataset.id;
        const existingRow = findProductRow(productId);

        if (existingRow) {
            const qty = existingRow.querySelector('[data-role="item-cantidad"]');
            qty.value = String(Number(qty.value || 0) + 1);
            syncPreview();
            return;
        }

        addItemRow({
            tipo: 'producto',
            producto_id: productId,
            cantidad: 1,
            precio: button.dataset.price || 0,
        });
    }

    function filterPosCatalog() {
        const { posSearch, posCatalog } = els();
        const query = normalize(posSearch?.value || '');

        if (!posCatalog) {
            return;
        }

        posCatalog.querySelectorAll('[data-pos-product]').forEach((button) => {
            const text = normalize(button.dataset.search || button.dataset.name || button.textContent);
            button.hidden = query !== '' && !text.includes(query);
        });
    }

    function clearItems() {
        const { itemsList } = els();
        if (itemsList) {
            itemsList.innerHTML = '';
        }
        syncPreview();
    }

    function resetForm(prefill = null) {
        const { form, editingId, contextSearch, contextHint, posSearch, cliente, mascota, historia, metodo, estado, fecha } = els();

        if (!form) {
            return;
        }

        form.dataset.mode = 'create';
        form.action = form.dataset.storeAction || form.action;
        removeMethod(form);

        if (editingId) editingId.value = '';
        if (contextSearch) contextSearch.value = '';
        if (contextHint) contextHint.textContent = 'Venta rápida sin cliente por defecto. Busca una mascota solo si deseas asociar el cobro.';
        if (posSearch) posSearch.value = '';
        if (cliente) cliente.value = prefill?.cliente_id ? String(prefill.cliente_id) : '';
        if (mascota) mascota.value = prefill?.mascota_id ? String(prefill.mascota_id) : '';
        if (historia) historia.value = prefill?.historia_clinica_id ? String(prefill.historia_clinica_id) : '';
        if (metodo) metodo.value = 'efectivo';
        if (estado) estado.value = 'pagado';
        if (fecha) fecha.value = new Date().toISOString().slice(0, 10);

        filterMascotasAndHistorias();
        filterPosCatalog();
        clearItems();

        const items = prefill?.items || [];
        if (items.length) {
            items.forEach((item) => addItemRow(item));
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
    }

    window.openVentaModal = function () {
        const { form, title, summary, submit } = els();
        const prefill = parseJson(form?.dataset.prefill, null);
        resetForm(prefill);
        if (title) title.textContent = 'Nuevo cobro';
        if (summary) summary.textContent = 'Cobra servicios, productos o tratamientos con total calculado y stock sincronizado.';
        if (submit) submit.textContent = 'Guardar cobro';
        syncPreview();
        open();
    };

    window.openEditVentaModal = function (venta) {
        const { form, editingId, cliente, mascota, historia, metodo, estado, fecha, title, summary, submit } = els();

        if (!form) {
            return;
        }

        resetForm();
        form.dataset.mode = 'edit';
        form.action = updateAction(form, venta.id);
        ensureMethod(form).value = 'PUT';
        if (editingId) editingId.value = venta.id;
        if (cliente) cliente.value = String(venta.cliente_id || '');
        if (mascota) mascota.value = String(venta.mascota_id || '');
        if (historia) historia.value = String(venta.historia_clinica_id || '');
        if (metodo) metodo.value = venta.metodo_pago || 'efectivo';
        if (estado) estado.value = venta.estado || 'pagado';
        if (fecha) fecha.value = venta.fecha || new Date().toISOString().slice(0, 10);
        if (title) title.textContent = 'Editar cobro';
        if (summary) summary.textContent = 'Actualiza el estado, método o detalle sin perder la sincronización de stock.';
        if (submit) submit.textContent = 'Actualizar cobro';
        filterMascotasAndHistorias();
        clearItems();
        (venta.items || []).forEach((item) => addItemRow(item));
        syncPreview();
        open();
    };

    window.closeVentaModal = function () {
        const { modal } = els();
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, form, addItemButton, contextSearch, clearPatientButton, posSearch, posCatalog, cliente, mascota, historia, metodo, estado } = els();

        if (!modal || !form) {
            return;
        }

        addItemButton?.addEventListener('click', () => addItemRow());
        contextSearch?.addEventListener('input', syncContextFromQuickSearch);
        contextSearch?.addEventListener('change', syncContextFromQuickSearch);
        clearPatientButton?.addEventListener('click', clearPatientContext);
        posSearch?.addEventListener('input', filterPosCatalog);
        posCatalog?.querySelectorAll('[data-pos-product]').forEach((button) => {
            button.addEventListener('click', () => addProductFromPos(button));
        });

        cliente?.addEventListener('change', () => {
            filterMascotasAndHistorias();
            syncPreview();
        });
        mascota?.addEventListener('change', syncContextFromMascota);
        historia?.addEventListener('change', syncContextFromHistoria);
        [metodo, estado].forEach((element) => {
            element?.addEventListener('change', syncPreview);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeVentaModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                window.closeVentaModal();
            }
        });

        const oldItems = parseJson(form.dataset.oldItems, []);
        const prefill = parseJson(form.dataset.prefill, null);

        filterMascotasAndHistorias();
        filterPosCatalog();

        if (window.ventaModalState?.hasErrors || modal.dataset.openOnLoad === 'true') {
            if (window.ventaModalState?.isEdit && window.ventaModalState.editingId) {
                form.dataset.mode = 'edit';
                form.action = updateAction(form, window.ventaModalState.editingId);
                ensureMethod(form).value = 'PUT';
            }

            clearItems();
            if (oldItems?.length) {
                oldItems.forEach((item) => addItemRow(item));
            } else if (prefill?.items?.length) {
                prefill.items.forEach((item) => addItemRow(item));
            }
            syncPreview();
            open();
            return;
        }

        form.dataset.mode = 'create';
        if (!oldItems?.length && prefill?.items?.length) {
            clearItems();
            prefill.items.forEach((item) => addItemRow(item));
        }
        syncPreview();
    });
})();
