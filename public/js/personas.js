/**
 * Gestión Asíncrona de Personas
 * Rockstar Gym - Control Integral de Personas
 */

document.addEventListener('DOMContentLoaded', () => {
    const modalPersona = document.getElementById('modal-persona');
    const formPersona = document.getElementById('form-persona');
    const modalEliminar = document.getElementById('modal-eliminar');
    const btnConfirmarEliminar = document.getElementById('btn-confirmar-eliminar');
    const eliminarMensajeTexto = document.getElementById('eliminar-mensaje-texto');

    // Filtros
    const inputBusqueda = document.getElementById('filtro-busqueda');
    const filtroEstatus = document.getElementById('filtro-estatus');
    const filtroUsuario = document.getElementById('filtro-usuario');
    const filtroGenero = document.getElementById('filtro-genero');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');

    let personaIdAEliminar = null;

    // =========================================================================
    // 1. GESTIÓN DE MODALES
    // =========================================================================
    window.abrirModalCrear = function () {
        if (!formPersona || !modalPersona) return;
        formPersona.reset();
        document.getElementById('input-id-persona').value = '';
        document.getElementById('modal-persona-titulo').textContent = 'Registrar Nueva Persona';
        document.getElementById('campo-estatus-container').classList.add('hidden');
        document.getElementById('btn-submit-persona').textContent = 'Guardar Registro';

        modalPersona.classList.remove('hidden');
        modalPersona.classList.add('flex');
    };

    window.abrirModalEditar = function (persona) {
        if (!formPersona || !modalPersona) return;
        formPersona.reset();

        document.getElementById('input-id-persona').value = persona.id_persona;
        document.getElementById('modal-persona-titulo').textContent = 'Editar Datos de Persona';
        document.getElementById('campo-estatus-container').classList.remove('hidden');
        document.getElementById('btn-submit-persona').textContent = 'Actualizar Datos';

        document.getElementById('input-cedula').value = persona.cedula_identidad || '';
        document.getElementById('select-genero').value = persona.id_genero || '';
        document.getElementById('input-primer-nombre').value = persona.primer_nombre || '';
        document.getElementById('input-segundo-nombre').value = persona.segundo_nombre || '';
        document.getElementById('input-primer-apellido').value = persona.primer_apellido || '';
        document.getElementById('input-segundo-apellido').value = persona.segundo_apellido || '';
        document.getElementById('input-fecha-nacimiento').value = persona.fecha_nacimiento || '';
        document.getElementById('input-telefono').value = persona.telefono || '';
        document.getElementById('input-email').value = persona.email || '';
        document.getElementById('input-direccion').value = persona.direccion_habitacion || '';
        document.getElementById('select-estatus').value = persona.id_estatus || '1';

        modalPersona.classList.remove('hidden');
        modalPersona.classList.add('flex');
    };

    window.cerrarModal = function () {
        if (!modalPersona) return;
        modalPersona.classList.remove('flex');
        modalPersona.classList.add('hidden');
    };

    window.confirmarEliminarPersona = function (id, nombre, tieneUsuario) {
        personaIdAEliminar = id;
        if (tieneUsuario) {
            eliminarMensajeTexto.innerHTML = `<strong>${nombre}</strong> tiene una cuenta de usuario o dependencias activas.<br><br>Para proteger el historial del sistema, su estado se cambiará a <span class="text-rose-600 font-semibold">Inactivo</span>.`;
            btnConfirmarEliminar.textContent = 'Inactivar Persona';
            btnConfirmarEliminar.className = 'px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-amber-600 hover:bg-amber-500 shadow-md hover:shadow-lg transition-all cursor-pointer';
        } else {
            eliminarMensajeTexto.innerHTML = `¿Estás seguro de que deseas eliminar permanentemente a <strong>${nombre}</strong>?<br><br>Esta acción no se puede deshacer.`;
            btnConfirmarEliminar.textContent = 'Eliminar Registro';
            btnConfirmarEliminar.className = 'px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-500 shadow-md hover:shadow-lg transition-all cursor-pointer';
        }

        modalEliminar.classList.remove('hidden');
        modalEliminar.classList.add('flex');
    };

    window.cerrarModalEliminar = function () {
        if (!modalEliminar) return;
        personaIdAEliminar = null;
        modalEliminar.classList.remove('flex');
        modalEliminar.classList.add('hidden');
    };

    // Cierre con click en backdrop
    window.addEventListener('click', (e) => {
        if (e.target === modalPersona) cerrarModal();
        if (e.target === modalEliminar) cerrarModalEliminar();
    });

    // =========================================================================
    // 2. SISTEMA DE NOTIFICACIONES TOAST
    // =========================================================================
    window.mostrarToast = function (mensaje, tipo = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `p-4 text-sm font-medium rounded-xl border shadow-lg transition-all transform translate-y-2 opacity-0 flex items-center gap-2.5 pointer-events-auto bg-white dark:bg-gray-800 ${
            tipo === 'error'
                ? 'text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/40 bg-rose-50/70 dark:bg-rose-950/30'
                : tipo === 'warning'
                ? 'text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-900/40 bg-amber-50/70 dark:bg-amber-950/30'
                : 'text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/40 bg-emerald-50/70 dark:bg-emerald-950/30'
        }`;

        const iconSvg = tipo === 'error'
            ? `<svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
            : tipo === 'warning'
            ? `<svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`
            : `<svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;

        toast.innerHTML = `${iconSvg} <span>${mensaje}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 10);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    // =========================================================================
    // 3. ENVÍO ASÍNCRONO DEL FORMULARIO (CREAR O ACTUALIZAR)
    // =========================================================================
    if (formPersona) {
        formPersona.addEventListener('submit', function (e) {
            e.preventDefault();

            const idPersona = document.getElementById('input-id-persona').value;
            const accion = idPersona ? 'actualizar_persona' : 'crear_persona';
            const formData = new FormData(this);

            const btnSubmit = document.getElementById('btn-submit-persona');
            const textoOriginal = btnSubmit.textContent;
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Procesando...';

            fetch(`help.php?action=${accion}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;

                if (data.status === true || data.success === true) {
                    mostrarToast(data.message || 'Operación realizada exitosamente', 'success');
                    cerrarModal();
                    actualizarTablaPersonas();
                } else {
                    mostrarToast(data.message || data.error || 'Ocurrió un error al procesar el registro', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
                mostrarToast('Error de conexión con el servidor', 'error');
            });
        });
    }

    // =========================================================================
    // 4. CONFIRMACIÓN Y ELIMINACIÓN ASÍNCRONA
    // =========================================================================
    if (btnConfirmarEliminar) {
        btnConfirmarEliminar.addEventListener('click', () => {
            if (!personaIdAEliminar) return;

            const formData = new FormData();
            formData.append('id_persona', personaIdAEliminar);

            btnConfirmarEliminar.disabled = true;
            btnConfirmarEliminar.textContent = 'Procesando...';

            fetch('help.php?action=eliminar_persona', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnConfirmarEliminar.disabled = false;
                cerrarModalEliminar();

                if (data.status === true || data.success === true) {
                    const tipo = data.data && data.data.inactivated ? 'warning' : 'success';
                    mostrarToast(data.message || 'Acción completada exitosamente', tipo);
                    actualizarTablaPersonas();
                } else {
                    mostrarToast(data.message || data.error || 'No se pudo completar la acción', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btnConfirmarEliminar.disabled = false;
                cerrarModalEliminar();
                mostrarToast('Error de conexión al eliminar', 'error');
            });
        });
    }

    // =========================================================================
    // 5. CAMBIO RÁPIDO DE ESTADO (TOGGLE SWITCH)
    // =========================================================================
    window.toggleEstatusPersona = function (idPersona, isChecked) {
        const nuevoEstatus = isChecked ? 1 : 2;
        const formData = new FormData();
        formData.append('id_persona', idPersona);
        formData.append('nuevo_id_estatus', nuevoEstatus);

        fetch('help.php?action=cambiar_estatus_persona', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === true || data.success === true) {
                mostrarToast(data.message || 'Estado actualizado', 'success');
                actualizarTablaPersonas();
            } else {
                mostrarToast(data.message || data.error || 'Error al cambiar estado', 'error');
                actualizarTablaPersonas();
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast('Error de conexión al cambiar estado', 'error');
            actualizarTablaPersonas();
        });
    };

    // =========================================================================
    // 6. FILTRADO Y BÚSQUEDA EN TIEMPO REAL
    // =========================================================================
    function aplicarFiltros() {
        const query = (inputBusqueda ? inputBusqueda.value : '').toLowerCase().trim();
        const estatusVal = (filtroEstatus ? filtroEstatus.value : '').toLowerCase();
        const usuarioVal = (filtroUsuario ? filtroUsuario.value : '');
        const generoVal = (filtroGenero ? filtroGenero.value : '').toLowerCase();

        const filas = document.querySelectorAll('.fila-persona');
        let visibles = 0;

        filas.forEach(fila => {
            const cedula = fila.getAttribute('data-cedula') || '';
            const nombre = fila.getAttribute('data-nombre') || '';
            const email = fila.getAttribute('data-email') || '';
            const telefono = fila.getAttribute('data-telefono') || '';
            const estatus = (fila.getAttribute('data-estatus') || '').toLowerCase();
            const usuario = fila.getAttribute('data-usuario') || '';
            const genero = (fila.getAttribute('data-genero') || '').toLowerCase();

            // Coincidencia de texto
            const coincideTexto = !query || 
                cedula.includes(query) || 
                nombre.includes(query) || 
                email.includes(query) || 
                telefono.includes(query);

            // Coincidencia de estado
            const coincideEstatus = !estatusVal || estatus === estatusVal;

            // Coincidencia de usuario
            const coincideUsuario = !usuarioVal || usuario === usuarioVal;

            // Coincidencia de género
            const coincideGenero = !generoVal || genero === generoVal;

            if (coincideTexto && coincideEstatus && coincideUsuario && coincideGenero) {
                fila.classList.remove('hidden');
                visibles++;
            } else {
                fila.classList.add('hidden');
            }
        });

        // Manejo de fila "sin resultados"
        let filaSinResultados = document.getElementById('sin-resultados-busqueda-row');
        const tbody = document.getElementById('tabla-personas-body');

        if (visibles === 0 && filas.length > 0) {
            if (!filaSinResultados && tbody) {
                filaSinResultados = document.createElement('tr');
                filaSinResultados.id = 'sin-resultados-busqueda-row';
                filaSinResultados.innerHTML = `
                    <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                        No se encontraron registros que coincidan con los filtros aplicados.
                    </td>
                `;
                tbody.appendChild(filaSinResultados);
            }
        } else if (filaSinResultados) {
            filaSinResultados.remove();
        }
    }

    if (inputBusqueda) inputBusqueda.addEventListener('input', aplicarFiltros);
    if (filtroEstatus) filtroEstatus.addEventListener('change', aplicarFiltros);
    if (filtroUsuario) filtroUsuario.addEventListener('change', aplicarFiltros);
    if (filtroGenero) filtroGenero.addEventListener('change', aplicarFiltros);

    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', () => {
            if (inputBusqueda) inputBusqueda.value = '';
            if (filtroEstatus) filtroEstatus.value = '';
            if (filtroUsuario) filtroUsuario.value = '';
            if (filtroGenero) filtroGenero.value = '';
            aplicarFiltros();
        });
    }

    // =========================================================================
    // 7. ACTUALIZACIÓN DINÁMICA DE LA TABLA Y KPIs SIN RECARGAR LA PÁGINA
    // =========================================================================
    function actualizarTablaPersonas() {
        fetch(window.location.href)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const nuevoTbody = doc.getElementById('tabla-personas-body');
                const actualTbody = document.getElementById('tabla-personas-body');
                if (nuevoTbody && actualTbody) {
                    actualTbody.innerHTML = nuevoTbody.innerHTML;
                }

                // Actualizar KPIs
                ['kpi-total', 'kpi-activas', 'kpi-con-usuario', 'kpi-sin-usuario'].forEach(id => {
                    const nuevoKpi = doc.getElementById(id);
                    const actualKpi = document.getElementById(id);
                    if (nuevoKpi && actualKpi) {
                        actualKpi.textContent = nuevoKpi.textContent;
                    }
                });

                // Re-aplicar filtros si había texto escrito
                aplicarFiltros();
            })
            .catch(err => {
                console.error('Error al actualizar tabla de personas:', err);
            });
    }
});