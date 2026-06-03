(function () {
    const treatmentTemplates = {
        dermatologico: {
            description: 'Tratamiento dermatologico con control de piel, limpieza de zona afectada y seguimiento de respuesta al manejo indicado.',
            days: 10,
            control: 'Control sugerido en 7 dias para revisar evolucion cutanea.',
        },
        otitis: {
            description: 'Manejo de otitis con limpieza auricular, medicacion topica y control de secrecion o irritacion.',
            days: 7,
            control: 'Control sugerido en 5 dias para revisar conducto auditivo.',
        },
        gastrointestinal: {
            description: 'Tratamiento de soporte digestivo con dieta blanda, medicacion y control de hidratacion.',
            days: 5,
            control: 'Control sugerido en 48 horas si persisten vomitos o diarrea.',
        },
        postoperatorio: {
            description: 'Seguimiento postoperatorio con vigilancia de herida, analgesia indicada y control de recuperacion.',
            days: 10,
            control: 'Control sugerido entre 5 y 7 dias para revisar cicatrizacion.',
        },
        desparasitacion: {
            description: 'Plan antiparasitario con seguimiento preventivo y recomendaciones de control ambiental.',
            days: 1,
            control: 'Control sugerido segun calendario preventivo del paciente.',
        },
        control: {
            description: 'Tratamiento o manejo de control general con observacion clinica y seguimiento corto.',
            days: 3,
            control: 'Control sugerido en 3 dias para verificar respuesta al manejo.',
        },
    };

    function els() {
        const form = document.getElementById('tratamientoForm');

        return {
            modal: document.getElementById('tratamientoModal'),
            form,
            title: document.getElementById('tratamientoModalTitle'),
            summary: document.getElementById('tratamientoModalSummary'),
            submit: document.getElementById('tratamientoSubmitLabel'),
            historia: document.getElementById('tratamiento_historia_id'),
            template: document.getElementById('tratamiento_template'),
            vet: document.getElementById('tratamiento_veterinario_id'),
            desc: document.getElementById('tratamiento_descripcion'),
            costo: document.getElementById('tratamiento_costo'),
            inicio: document.getElementById('tratamiento_fecha_inicio'),
            fin: document.getElementById('tratamiento_fecha_fin'),
            proximoControl: document.getElementById('tratamiento_proximo_control'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            photo: document.getElementById('tratamientoMascotaPhoto'),
            petName: document.getElementById('tratamientoMascotaName'),
            petOwner: document.getElementById('tratamientoMascotaOwner'),
            petType: document.getElementById('tratamientoMascotaType'),
            petColor: document.getElementById('tratamientoMascotaColor'),
            historiaDate: document.getElementById('tratamientoHistoriaDate'),
            diagnostico: document.getElementById('tratamientoDiagnostico'),
            profesional: document.getElementById('tratamientoProfesional'),
            estado: document.getElementById('tratamientoEstado'),
            duracion: document.getElementById('tratamientoDuracion'),
            control: document.getElementById('tratamientoControl'),
            productosList: document.getElementById('tratamientoProductosList'),
            productosTemplate: document.getElementById('tratamientoProductoTemplate'),
            addProductButton: document.getElementById('tratamientoAddProductButton'),
            productosPreview: document.getElementById('tratamientoProductosPreview'),
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
        return (form?.dataset.updateTemplate || '/tratamientos/__ID__').replace('__ID__', encodeURIComponent(id));
    }

    function today(form) {
        return form?.dataset.today || new Date().toISOString().slice(0, 10);
    }

    function parseDate(value) {
        if (!value) {
            return null;
        }

        const date = new Date(value + 'T00:00:00');
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function addDays(value, days) {
        const baseDate = parseDate(value);

        if (!baseDate) {
            return '';
        }

        baseDate.setDate(baseDate.getDate() + days);
        return baseDate.toISOString().slice(0, 10);
    }

    function diffDays(start, end) {
        const startDate = parseDate(start);
        const endDate = parseDate(end);

        if (!startDate || !endDate) {
            return null;
        }

        const diff = Math.round((endDate - startDate) / 86400000);
        return diff >= 0 ? diff + 1 : null;
    }

    function getProjectedStatus(startValue, endValue, form) {
        const todayDate = parseDate(today(form));
        const startDate = parseDate(startValue);
        const endDate = parseDate(endValue);

        if (!startDate) {
            return 'Sin definir';
        }

        if (startDate > todayDate) {
            return 'Programado para iniciar';
        }

        if (endDate && endDate < todayDate) {
            return 'Finalizado';
        }

        if (endDate) {
            const remaining = Math.round((endDate - todayDate) / 86400000);
            if (remaining <= 3) {
                return 'Activo y por vencer';
            }
        }

        return 'Activo';
    }

    function updateProductInputNames() {
        const { productosList } = els();

        if (!productosList) {
            return;
        }

        productosList.querySelectorAll('.tratamiento-product-row').forEach((row, index) => {
            const select = row.querySelector('[data-role="producto-select"]');
            const qty = row.querySelector('[data-role="producto-cantidad"]');

            if (select) {
                select.name = `productos[${index}][producto_id]`;
            }

            if (qty) {
                qty.name = `productos[${index}][cantidad]`;
            }
        });
    }

    function updateProductPreview() {
        const { productosList, productosPreview } = els();

        if (!productosList || !productosPreview) {
            return;
        }

        const lines = [];

        productosList.querySelectorAll('.tratamiento-product-row').forEach((row) => {
            const select = row.querySelector('[data-role="producto-select"]');
            const qty = row.querySelector('[data-role="producto-cantidad"]');
            const selected = select?.options[select.selectedIndex];

            if (!select?.value || !selected) {
                return;
            }

            const name = selected.dataset.name || selected.textContent.trim();
            const unitPrice = Number(selected.dataset.price || 0);
            const quantity = Number(qty?.value || 0);
            const isService = selected.dataset.service === '1';
            const subtotal = unitPrice * quantity;

            lines.push(`${name} x${quantity} (${isService ? 'Servicio' : 'Producto'}) · S/ ${subtotal.toFixed(2)}`);
        });

        productosPreview.textContent = lines.length
            ? lines.join(' | ')
            : 'Aun no se agregaron productos o servicios a este tratamiento.';
    }

    function bindProductRow(row) {
        const select = row.querySelector('[data-role="producto-select"]');
        const qty = row.querySelector('[data-role="producto-cantidad"]');
        const removeButton = row.querySelector('[data-role="remove-product"]');

        select?.addEventListener('change', updateProductPreview);
        qty?.addEventListener('input', updateProductPreview);
        removeButton?.addEventListener('click', () => {
            row.remove();
            updateProductInputNames();
            updateProductPreview();
        });
    }

    function addProductRow(item = null) {
        const { productosList, productosTemplate } = els();

        if (!productosList || !productosTemplate) {
            return;
        }

        const fragment = productosTemplate.content.cloneNode(true);
        const row = fragment.querySelector('.tratamiento-product-row');
        const select = fragment.querySelector('[data-role="producto-select"]');
        const qty = fragment.querySelector('[data-role="producto-cantidad"]');

        if (item?.producto_id && select) {
            select.value = String(item.producto_id);
        }

        if (item?.cantidad && qty) {
            qty.value = String(item.cantidad);
        }

        productosList.appendChild(fragment);
        const appendedRow = productosList.lastElementChild;
        bindProductRow(appendedRow);
        updateProductInputNames();
        updateProductPreview();
    }

    function clearProductRows() {
        const { productosList } = els();

        if (productosList) {
            productosList.innerHTML = '';
        }

        updateProductPreview();
    }

    function applyTemplate() {
        const { template, desc, inicio, fin, proximoControl, control, form } = els();

        if (!template || !template.value) {
            return;
        }

        const config = treatmentTemplates[template.value];

        if (!config) {
            return;
        }

        if (desc && !desc.value.trim()) {
            desc.value = config.description;
        }

        if (inicio && !inicio.value) {
            inicio.value = today(form);
        }

        if (fin && !fin.value && config.days) {
            fin.value = addDays(inicio.value || today(form), Math.max(config.days - 1, 0));
        }

        if (proximoControl && !proximoControl.value && fin?.value) {
            proximoControl.value = fin.value;
        }

        if (control) {
            control.textContent = config.control;
        }
    }

    function syncPreview() {
        const {
            historia,
            vet,
            title,
            summary,
            form,
            photo,
            petName,
            petOwner,
            petType,
            petColor,
            historiaDate,
            diagnostico,
            profesional,
            submit,
            inicio,
            fin,
            proximoControl,
            estado,
            duracion,
            control,
        } = els();

        if (!historia || !title || !form) {
            return;
        }

        const selected = historia.options[historia.selectedIndex];
        const selectedVet = vet ? vet.options[vet.selectedIndex] : null;
        const isEdit = form.dataset.mode === 'edit';
        const durationDays = diffDays(inicio?.value, fin?.value);
        const projectedStatus = getProjectedStatus(inicio?.value, fin?.value, form);

        title.textContent = isEdit ? 'Editar tratamiento' : 'Nuevo tratamiento';
        submit.textContent = isEdit ? 'Actualizar tratamiento' : 'Guardar tratamiento';
        profesional.textContent = selectedVet && vet.value ? (selectedVet.dataset.name || selectedVet.text) : 'Se asignara al guardar.';
        estado.textContent = projectedStatus;
        duracion.textContent = durationDays ? durationDays + ' dias estimados' : (fin?.value ? 'Rango invalido' : 'Tratamiento abierto');

        if (proximoControl?.value) {
            control.textContent = 'Control sugerido para ' + proximoControl.value;
        } else if (fin?.value) {
            control.textContent = 'Control sugerido al cerrar el tratamiento: ' + fin.value;
        } else {
            control.textContent = 'Definelo con la fecha de fin.';
        }

        if (!selected || !historia.value) {
            photo.src = '/storage/default.png';
            petName.textContent = 'Selecciona una historia';
            petOwner.textContent = 'El dueno aparecera aqui.';
            petType.textContent = 'Paciente veterinario';
            petColor.textContent = 'Color pendiente';
            historiaDate.textContent = 'Sin fecha';
            diagnostico.textContent = 'Se mostrara el diagnostico asociado.';
            summary.textContent = 'Selecciona la atencion clinica de origen y deja el seguimiento bien organizado.';
            return;
        }

        photo.src = selected.dataset.foto || '/storage/default.png';
        petName.textContent = selected.dataset.mascota || 'Mascota seleccionada';
        petOwner.textContent = selected.dataset.cliente || 'Sin dueno registrado';
        petType.textContent = selected.dataset.tipo || 'Paciente veterinario';
        petColor.textContent = selected.dataset.color || 'Color sin registrar';
        historiaDate.textContent = selected.dataset.fecha || 'Sin fecha';
        diagnostico.textContent = selected.dataset.diagnostico || 'Sin diagnostico registrado';
        summary.textContent = isEdit
            ? 'Actualizando el tratamiento de ' + petName.textContent + '.'
            : 'Registrando un tratamiento para ' + petName.textContent + '.';
    }

    function resetForm(historiaId = '') {
        const { form, historia, template, vet, desc, costo, inicio, fin, proximoControl, editingId } = els();

        if (!form) {
            return;
        }

        form.action = form.dataset.storeAction || form.action;
        form.dataset.mode = 'create';
        removeMethod(form);

        if (editingId) editingId.value = '';
        if (historia) historia.value = historiaId ? String(historiaId) : '';
        if (template) template.value = '';
        if (vet) vet.value = '';
        if (desc) desc.value = '';
        if (costo) costo.value = '0';
        if (inicio) inicio.value = today(form);
        if (fin) fin.value = '';
        if (proximoControl) proximoControl.value = '';
        clearProductRows();
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

    window.openTratamientoModal = function (historiaId = '') {
        resetForm(historiaId);
        syncPreview();
        open();
    };

    window.openEditTratamientoModal = function (tratamiento) {
        const { form, historia, template, vet, desc, costo, inicio, fin, proximoControl, editingId } = els();

        if (!form) {
            return;
        }

        resetForm();
        form.dataset.mode = 'edit';
        form.action = updateAction(form, tratamiento.id);
        ensureMethod(form).value = 'PUT';
        if (editingId) editingId.value = tratamiento.id;
        if (historia) historia.value = String(tratamiento.historia_clinica_id || '');
        if (template) template.value = '';
        if (vet) vet.value = String(tratamiento.veterinario_id || '');
        if (desc) desc.value = tratamiento.descripcion || '';
        if (costo) costo.value = tratamiento.costo || '0';
        if (inicio) inicio.value = tratamiento.fecha_inicio || today(form);
        if (fin) fin.value = tratamiento.fecha_fin || '';
        if (proximoControl) proximoControl.value = tratamiento.proximo_control || tratamiento.fecha_fin || '';

        clearProductRows();
        (tratamiento.productos || []).forEach((producto) => addProductRow({
            producto_id: producto.id || producto.producto_id,
            cantidad: producto.cantidad || producto.pivot?.cantidad || 1,
        }));

        syncPreview();
        open();
    };

    window.closeTratamientoModal = function () {
        const { modal } = els();

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, historia, template, vet, inicio, fin, proximoControl, form, addProductButton } = els();

        if (!modal || !form) {
            return;
        }

        historia?.addEventListener('change', syncPreview);
        vet?.addEventListener('change', syncPreview);
        inicio?.addEventListener('change', syncPreview);
        fin?.addEventListener('change', () => {
            const { proximoControl } = els();
            if (proximoControl && !proximoControl.value && fin.value) {
                proximoControl.value = fin.value;
            }
            syncPreview();
        });
        proximoControl?.addEventListener('change', syncPreview);

        template?.addEventListener('change', () => {
            applyTemplate();
            syncPreview();
        });

        addProductButton?.addEventListener('click', () => addProductRow());

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeTratamientoModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                window.closeTratamientoModal();
            }
        });

        const oldProducts = JSON.parse(form.dataset.oldProducts || '[]');

        if (window.tratamientoModalState?.hasErrors || modal.dataset.openOnLoad === 'true') {
            if (window.tratamientoModalState?.isEdit && window.tratamientoModalState.editingId) {
                form.dataset.mode = 'edit';
                form.action = updateAction(form, window.tratamientoModalState.editingId);
                ensureMethod(form).value = 'PUT';
            } else if (modal.dataset.prefillHistoria && historia && !historia.value) {
                historia.value = modal.dataset.prefillHistoria;
            }

            clearProductRows();
            if (oldProducts.length) {
                oldProducts.forEach((item) => addProductRow(item));
            }
            syncPreview();
            open();
            return;
        }

        form.dataset.mode = 'create';
        syncPreview();
    });
})();
