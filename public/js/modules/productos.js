(function () {
    const PRODUCT_DEFAULT_CATEGORY = 'medicamento';
    const SERVICE_DEFAULT_CATEGORY = 'consulta';

    function els() {
        const form = document.getElementById('productoForm');

        return {
            modal: document.getElementById('productoModal'),
            form,
            title: document.getElementById('productoModalTitle'),
            summary: document.getElementById('productoModalSummary'),
            submit: document.getElementById('productoSubmitLabel'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            nombre: document.getElementById('producto_nombre'),
            descripcion: document.getElementById('producto_descripcion'),
            precio: document.getElementById('producto_precio'),
            stock: document.getElementById('producto_stock'),
            stockHelp: document.getElementById('productoStockHelp'),
            tipo: document.getElementById('producto_es_servicio'),
            categoria: document.getElementById('producto_categoria'),
            previewName: document.getElementById('productoPreviewName'),
            previewType: document.getElementById('productoPreviewType'),
            previewCategory: document.getElementById('productoPreviewCategory'),
            previewPrice: document.getElementById('productoPreviewPrice'),
            previewStock: document.getElementById('productoPreviewStock'),
            previewAutomation: document.getElementById('productoPreviewAutomation'),
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
        return (form?.dataset.updateTemplate || '/productos/__ID__').replace('__ID__', encodeURIComponent(id));
    }

    function optionKind(option) {
        return option?.dataset?.kind || '';
    }

    function syncCategoryOptions() {
        const { tipo, categoria } = els();
        if (!tipo || !categoria) return;

        const wantedKind = tipo.value === '1' ? 'servicio' : 'producto';
        let selectedStillValid = false;

        Array.from(categoria.options).forEach((option) => {
            const kind = optionKind(option);
            if (!kind) return;

            const isValid = kind === wantedKind;
            option.hidden = !isValid;
            option.disabled = !isValid;

            if (isValid && option.selected) {
                selectedStillValid = true;
            }
        });

        if (!selectedStillValid) {
            categoria.value = wantedKind === 'servicio' ? SERVICE_DEFAULT_CATEGORY : PRODUCT_DEFAULT_CATEGORY;
        }
    }

    function syncPreview() {
        const {
            form,
            nombre,
            precio,
            stock,
            stockHelp,
            tipo,
            categoria,
            previewName,
            previewType,
            previewCategory,
            previewPrice,
            previewStock,
            previewAutomation,
            title,
            submit,
            summary,
        } = els();

        if (!form) return;

        syncCategoryOptions();

        const isEdit = form.dataset.mode === 'edit';
        const isService = tipo?.value === '1';
        const price = Number(precio?.value || 0);
        const stockValue = Number(stock?.value || 0);
        const selectedCategory = categoria?.selectedOptions?.[0]?.textContent?.trim() || 'Sin categoría';

        if (title) title.textContent = isEdit ? 'Editar registro' : 'Nuevo registro';
        if (submit) submit.textContent = isEdit ? 'Actualizar registro' : 'Guardar registro';
        if (summary) {
            summary.textContent = isEdit
                ? 'Actualiza este registro para mantener precios, stock y cobros sincronizados.'
                : 'Registra un producto físico o servicio cobrable para usarlo en atención, tratamientos y caja.';
        }

        if (previewName) previewName.textContent = nombre?.value?.trim() || 'Producto o servicio';
        if (previewType) {
            previewType.textContent = isService ? 'Servicio' : 'Producto físico';
            previewType.className = isService
                ? 'inline-flex rounded-full bg-cyan-600 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-white'
                : 'inline-flex rounded-full bg-blue-600 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-white';
        }
        if (previewCategory) previewCategory.textContent = selectedCategory;
        if (previewPrice) previewPrice.textContent = 'S/ ' + price.toFixed(2);
        if (previewStock) previewStock.textContent = isService ? 'No aplica' : stockValue + ' unidades';
        if (previewAutomation) {
            previewAutomation.textContent = isService
                ? 'Se usará como servicio cobrable desde la atención o caja, sin afectar inventario.'
                : 'Se usará como producto con stock. Al cobrarse en una venta pagada, el sistema podrá descontar unidades.';
        }

        if (stock && isService) {
            stock.value = '0';
            stock.setAttribute('readonly', 'readonly');
            stock.classList.add('bg-slate-100', 'text-slate-400');
        } else if (stock) {
            stock.removeAttribute('readonly');
            stock.classList.remove('bg-slate-100', 'text-slate-400');
        }

        if (stockHelp) {
            stockHelp.textContent = isService
                ? 'Los servicios no necesitan stock. Solo se usará el precio base.'
                : 'Este stock se usará para controlar productos físicos.';
        }
    }

    function resetForm() {
        const { form, editingId, nombre, descripcion, precio, stock, tipo, categoria } = els();

        if (!form) return;

        form.dataset.mode = 'create';
        form.action = form.dataset.storeAction || form.action;
        removeMethod(form);

        if (editingId) editingId.value = '';
        if (nombre) nombre.value = '';
        if (descripcion) descripcion.value = '';
        if (precio) precio.value = '0';
        if (stock) stock.value = '0';
        if (tipo) tipo.value = '0';
        if (categoria) categoria.value = PRODUCT_DEFAULT_CATEGORY;
    }

    function open() {
        const { modal } = els();
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    window.openProductoModal = function () {
        resetForm();
        syncPreview();
        open();
    };

    window.openEditProductoModal = function (producto) {
        const { form, editingId, nombre, descripcion, precio, stock, tipo, categoria } = els();

        if (!form) return;

        resetForm();
        form.dataset.mode = 'edit';
        form.action = updateAction(form, producto.id);
        ensureMethod(form).value = 'PUT';
        if (editingId) editingId.value = producto.id;
        if (nombre) nombre.value = producto.nombre || '';
        if (descripcion) descripcion.value = producto.descripcion || '';
        if (precio) precio.value = producto.precio || '0';
        if (stock) stock.value = producto.stock ?? '0';
        if (tipo) tipo.value = producto.es_servicio || '0';
        syncCategoryOptions();
        if (categoria) categoria.value = producto.categoria || (tipo?.value === '1' ? SERVICE_DEFAULT_CATEGORY : PRODUCT_DEFAULT_CATEGORY);
        syncPreview();
        open();
    };

    window.closeProductoModal = function () {
        const { modal } = els();
        if (!modal) return;

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, nombre, descripcion, precio, stock, tipo, categoria, form } = els();

        if (!modal || !form) return;

        [nombre, descripcion, precio, stock, tipo, categoria].forEach((element) => {
            element?.addEventListener('input', syncPreview);
            element?.addEventListener('change', syncPreview);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeProductoModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                window.closeProductoModal();
            }
        });

        if (window.productoModalState?.hasErrors) {
            if (window.productoModalState.isEdit && window.productoModalState.editingId) {
                form.dataset.mode = 'edit';
                form.action = updateAction(form, window.productoModalState.editingId);
                ensureMethod(form).value = 'PUT';
            }

            syncPreview();
            open();
            return;
        }

        form.dataset.mode = 'create';
        syncPreview();
    });
})();