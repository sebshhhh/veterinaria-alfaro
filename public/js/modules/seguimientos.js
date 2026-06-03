(function () {
    let closeTimer = null;
    let vaccineCloseTimer = null;

    function els() {
        const form = document.getElementById('seguimientoForm');
        const modal = document.getElementById('seguimientoModal');

        return {
            modal,
            card: modal ? modal.querySelector('.modal-card') : null,
            form,
            title: document.getElementById('seguimientoModalTitle'),
            summary: document.getElementById('seguimientoModalSummary'),
            submit: document.getElementById('seguimientoSubmitLabel'),
            historia: document.getElementById('seguimiento_historia_id'),
            vet: document.getElementById('seguimiento_veterinario_id'),
            tipo: document.getElementById('seguimiento_tipo'),
            titulo: document.getElementById('seguimiento_titulo'),
            estado: document.getElementById('seguimiento_estado'),
            motivo: document.getElementById('seguimiento_motivo'),
            notas: document.getElementById('seguimiento_notas'),
            evolucion: document.getElementById('seguimiento_evolucion'),
            fechaInicio: document.getElementById('seguimiento_fecha_inicio'),
            diasRetorno: document.getElementById('seguimiento_dias_retorno'),
            fechaControl: document.getElementById('seguimiento_fecha_proximo_control'),
            horaControl: document.getElementById('seguimiento_hora_proximo_control'),
            editingId: form ? form.querySelector('input[name="editing_id"]') : null,
            origin: form ? form.querySelector('input[name="origen"]') : null,
            mascotaName: document.getElementById('seguimientoMascotaName'),
            mascotaOwner: document.getElementById('seguimientoMascotaOwner'),
            historiaDate: document.getElementById('seguimientoHistoriaDate'),
            diagnostico: document.getElementById('seguimientoDiagnostico'),
            profesional: document.getElementById('seguimientoProfesional'),
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
        return (form?.dataset.updateTemplate || '/seguimientos/__ID__').replace('__ID__', encodeURIComponent(id));
    }

    function today(form) {
        return form?.dataset.today || new Date().toISOString().slice(0, 10);
    }

    function defaultHour(form) {
        return form?.dataset.defaultHour || '09:00';
    }

    function vaccineEls() {
        const form = document.getElementById('seguimientoVacunaForm');
        const modal = document.getElementById('seguimientoVacunaModal');

        return {
            modal,
            card: modal ? modal.querySelector('.modal-card') : null,
            form,
            id: document.getElementById('seguimientoVacunaId'),
            summary: document.getElementById('seguimientoVacunaSummary'),
            mascota: document.getElementById('seguimientoVacunaMascota'),
            cliente: document.getElementById('seguimientoVacunaCliente'),
            vacuna: document.getElementById('seguimientoVacunaNombre'),
            fechaProgramada: document.getElementById('seguimientoVacunaFecha'),
            fechaAplicacion: document.getElementById('seguimientoVacunaFechaAplicacion'),
            proximaDosis: document.getElementById('seguimientoVacunaProximaDosis'),
            evolucion: document.getElementById('seguimientoVacunaEvolucion'),
        };
    }

    function addDays(value, days) {
        if (!value || !days) {
            return '';
        }

        const baseDate = new Date(value + 'T00:00:00');
        const numericDays = parseInt(days, 10);

        if (Number.isNaN(baseDate.getTime()) || Number.isNaN(numericDays) || numericDays < 1) {
            return '';
        }

        baseDate.setDate(baseDate.getDate() + numericDays);
        return baseDate.toISOString().slice(0, 10);
    }

    function syncContext() {
        const { form, historia, vet, title, summary, submit, mascotaName, mascotaOwner, historiaDate, diagnostico, profesional } = els();

        if (!form || !historia || !title || !summary || !submit) {
            return;
        }

        const selectedHistoria = historia.options[historia.selectedIndex];
        const selectedVet = vet?.options[vet.selectedIndex];
        const isEdit = form.dataset.mode === 'edit';

        title.textContent = isEdit ? 'Actualizar control' : 'Agregar control de retorno';
        submit.textContent = isEdit ? 'Actualizar control' : 'Guardar control';

        if (selectedHistoria && historia.value) {
            const pet = selectedHistoria.dataset.pet || 'Paciente por seleccionar';
            const owner = selectedHistoria.dataset.owner || 'Sin propietario registrado';
            const date = selectedHistoria.dataset.date || '--/--/----';
            const diagnosis = selectedHistoria.dataset.diagnosis || 'Sin resumen clínico disponible.';

            if (mascotaName) mascotaName.textContent = pet;
            if (mascotaOwner) mascotaOwner.textContent = owner;
            if (historiaDate) historiaDate.textContent = date;
            if (diagnostico) diagnostico.textContent = diagnosis;
            summary.textContent = isEdit
                ? 'Actualiza evolución, fecha o estado del control de ' + pet + '.'
                : 'Programa un control manual para ' + pet + ' cuando no se generó desde la atención.';
        } else {
            if (mascotaName) mascotaName.textContent = 'Paciente por seleccionar';
            if (mascotaOwner) mascotaOwner.textContent = 'La atención relacionada cargará el paciente.';
            if (historiaDate) historiaDate.textContent = '--/--/----';
            if (diagnostico) diagnostico.textContent = 'Selecciona una atención para ver el resumen.';
            summary.textContent = 'Selecciona la atención de origen y programa la cita de retorno.';
        }

        if (profesional) {
            profesional.textContent = selectedVet && vet?.value
                ? (selectedVet.dataset.name || selectedVet.textContent || 'Profesional asignado')
                : 'Se asigna al guardar';
        }
    }

    function updateAutoControlDate() {
        const { form, fechaInicio, diasRetorno, fechaControl, horaControl } = els();

        if (!fechaInicio || !diasRetorno || !fechaControl) {
            return;
        }

        const calculatedDate = addDays(fechaInicio.value, diasRetorno.value);

        if (calculatedDate) {
            fechaControl.value = calculatedDate;
            if (horaControl && !horaControl.value) {
                horaControl.value = defaultHour(form);
            }
        }
    }

    function resetForm(prefillHistoriaId = '') {
        const { form, historia, vet, tipo, titulo, estado, motivo, notas, evolucion, fechaInicio, diasRetorno, fechaControl, horaControl, editingId, origin } = els();

        if (!form) {
            return;
        }

        form.action = form.dataset.storeAction || form.action;
        form.dataset.mode = 'create';
        removeMethod(form);

        if (editingId) editingId.value = '';
        if (origin) origin.value = 'manual';
        if (historia) historia.value = prefillHistoriaId ? String(prefillHistoriaId) : '';
        if (vet) vet.value = '';
        if (tipo) tipo.value = 'clinico';
        if (titulo) titulo.value = '';
        if (estado) estado.value = 'activo';
        if (motivo) motivo.value = '';
        if (notas) notas.value = '';
        if (evolucion) evolucion.value = '';
        if (fechaInicio) fechaInicio.value = today(form);
        if (diasRetorno) diasRetorno.value = '';
        if (fechaControl) fechaControl.value = '';
        if (horaControl) horaControl.value = defaultHour(form);

        syncContext();
    }

    function openModal() {
        const { modal, card } = els();

        if (!modal) {
            return;
        }

        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        if (card) {
            requestAnimationFrame(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            });
        }
    }

    window.openSeguimientoModal = function (historiaId = '') {
        resetForm(historiaId);
        openModal();
    };

    window.openEditSeguimientoModal = function (seguimiento) {
        const { form, historia, vet, tipo, titulo, estado, motivo, notas, evolucion, fechaInicio, diasRetorno, fechaControl, horaControl, editingId, origin } = els();

        if (!form) {
            return;
        }

        resetForm();
        form.dataset.mode = 'edit';
        form.action = updateAction(form, seguimiento.id);
        ensureMethod(form).value = 'PUT';

        if (editingId) editingId.value = seguimiento.id;
        if (origin) origin.value = seguimiento.origen || 'manual';
        if (historia) historia.value = String(seguimiento.historia_clinica_id || '');
        if (vet) vet.value = String(seguimiento.veterinario_id || '');
        if (tipo) tipo.value = seguimiento.tipo || 'clinico';
        if (titulo) titulo.value = seguimiento.titulo || '';
        if (estado) estado.value = seguimiento.estado || 'activo';
        if (motivo) motivo.value = seguimiento.motivo || '';
        if (notas) notas.value = seguimiento.notas || '';
        if (evolucion) evolucion.value = seguimiento.evolucion || '';
        if (fechaInicio) fechaInicio.value = seguimiento.fecha_inicio || today(form);
        if (diasRetorno) diasRetorno.value = seguimiento.dias_retorno || '';
        if (fechaControl) fechaControl.value = seguimiento.fecha_proximo_control || '';
        if (horaControl) horaControl.value = seguimiento.hora_proximo_control || defaultHour(form);

        syncContext();
        openModal();
    };

    window.closeSeguimientoModal = function () {
        const { modal, card } = els();

        if (!modal) {
            return;
        }

        if (card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
        }

        closeTimer = window.setTimeout(() => {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            closeTimer = null;
        }, 180);
    };

    window.openApplySeguimientoVacunaModal = function (payload) {
        const { modal, card, form, id, summary, mascota, cliente, vacuna, fechaProgramada, fechaAplicacion, proximaDosis, evolucion } = vaccineEls();

        if (!modal || !form) {
            return;
        }

        if (vaccineCloseTimer) {
            window.clearTimeout(vaccineCloseTimer);
            vaccineCloseTimer = null;
        }

        form.action = payload.url || form.action;
        if (id) id.value = payload.id || '';
        if (mascota) mascota.textContent = payload.mascota || 'Paciente';
        if (cliente) cliente.textContent = payload.cliente || 'Sin propietario';
        if (vacuna) vacuna.textContent = payload.vacuna || 'Vacuna programada';
        if (fechaProgramada) fechaProgramada.textContent = payload.fecha_programada || '--/--/----';
        if (summary) summary.textContent = 'Aplicar ' + (payload.vacuna || 'la vacuna programada') + ' a ' + (payload.mascota || 'este paciente') + '.';
        if (fechaAplicacion) fechaAplicacion.value = payload.fecha_aplicacion || new Date().toISOString().slice(0, 10);
        if (proximaDosis) proximaDosis.value = '';
        if (evolucion) evolucion.value = '';

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        if (card) {
            requestAnimationFrame(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            });
        }
    };

    window.closeSeguimientoVacunaModal = function () {
        const { modal, card } = vaccineEls();

        if (!modal) {
            return;
        }

        if (card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
        }

        vaccineCloseTimer = window.setTimeout(() => {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            vaccineCloseTimer = null;
        }, 180);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const { modal, form, historia, vet, fechaInicio, diasRetorno } = els();
        const vaccine = vaccineEls();

        if (!modal || !form) {
            return;
        }

        historia?.addEventListener('change', syncContext);
        vet?.addEventListener('change', syncContext);
        fechaInicio?.addEventListener('change', updateAutoControlDate);
        diasRetorno?.addEventListener('input', updateAutoControlDate);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.closeSeguimientoModal();
            }
        });

        vaccine.modal?.addEventListener('click', (event) => {
            if (event.target === vaccine.modal) {
                window.closeSeguimientoVacunaModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                window.closeSeguimientoModal();
            }

            if (event.key === 'Escape' && vaccine.modal && !vaccine.modal.classList.contains('hidden')) {
                window.closeSeguimientoVacunaModal();
            }
        });

        if (window.seguimientoModalState?.hasErrors) {
            if (window.seguimientoModalState.editingId) {
                form.dataset.mode = 'edit';
                form.action = updateAction(form, window.seguimientoModalState.editingId);
                ensureMethod(form).value = 'PUT';
            }

            syncContext();
            openModal();
            return;
        }

        if (modal.dataset.openOnLoad === 'true') {
            syncContext();
            openModal();
            return;
        }

        if (vaccine.modal?.dataset.openOnLoad === 'true') {
            vaccine.modal.classList.remove('hidden');
            vaccine.modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');

            if (vaccine.card) {
                requestAnimationFrame(() => {
                    vaccine.card.classList.remove('scale-95', 'opacity-0');
                    vaccine.card.classList.add('scale-100', 'opacity-100');
                });
            }
        }

        syncContext();
    });
})();
