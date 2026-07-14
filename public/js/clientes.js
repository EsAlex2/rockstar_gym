/**
 * Control Asíncrono - Gestión de Clientes (CRUD Completo)
 * Desarrollado bajo especificaciones de Tailwind CSS v4 y UX de Usuarios
 */

const modal = document.getElementById('modal-cliente');
const form = document.getElementById('form-cliente');
const modalTitle = modal.querySelector('h3');
const divEstatus = document.getElementById('div-estatus');
const btnSubmit = document.getElementById('btn-submit-cliente');

let accionActual = 'crear_cliente';

function abrirModalCrear() {
    form.reset();
    document.getElementById('input-id-cliente').value = '';
    
    // Títulos y estados por defecto para creación
    accionActual = 'crear_cliente';
    modalTitle.textContent = 'Inscribir Nuevo Cliente';
    btnSubmit.textContent = 'Inscribir Cliente';
    divEstatus.classList.add('hidden');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function abrirModalEditar(client) {
    form.reset();
    
    // Configurar acción de edición
    accionActual = 'actualizar_cliente';
    modalTitle.textContent = 'Editar Datos de Cliente';
    btnSubmit.textContent = 'Actualizar Cliente';
    divEstatus.classList.remove('hidden');

    // Precargar campos con los datos del cliente
    document.getElementById('input-id-cliente').value = client.id;
    document.getElementById('input-cedula').value = client.cedula_identidad;
    document.getElementById('select-genero').value = client.id_genero;
    document.getElementById('input-nombre1').value = client.primer_nombre;
    document.getElementById('input-nombre2').value = client.segundo_nombre || '';
    document.getElementById('input-apellido1').value = client.primer_apellido;
    document.getElementById('input-apellido2').value = client.segundo_apellido || '';
    document.getElementById('input-nacimiento').value = client.fecha_nacimiento;
    document.getElementById('input-telefono').value = client.telefono;
    document.getElementById('input-email').value = client.email;
    document.getElementById('input-direccion').value = client.direccion_habitacion;
    document.getElementById('select-estatus').value = client.id_estatus;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModal() {
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// Cierre al cliquear fuera del contenedor blanco del modal
window.addEventListener('click', (e) => {
    if (e.target === modal) cerrarModal();
});

// Generador e inyector dinámico de alertas flotantes (Toasts CSS v4)
function mostrarToast(mensaje, tipo = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `p-4 text-sm font-medium rounded-xl border shadow-lg transition-all transform translate-y-2 opacity-0 flex items-center gap-2 pointer-events-auto bg-white dark:bg-gray-800 ${
        tipo === 'success' 
            ? 'text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/50' 
            : 'text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30 bg-rose-50/50'
    }`;

    toast.innerHTML = tipo === 'success' 
        ? `<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`
        : `<svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`;

    container.appendChild(toast);

    setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Eliminar un cliente permanentemente
function eliminarCliente(idCliente) {
    if (!confirm('¿Está seguro de que desea eliminar permanentemente a este cliente y toda su información asociada?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id_cliente', idCliente);

    fetch('help.php?action=eliminar_cliente', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Fallo al conectar con el servidor.');
        return response.json();
    })
    .then(res => {
        if (res.status === true || res.success === true) {
            mostrarToast(res.message || 'Cliente eliminado con éxito.', 'success');
            actualizarTabla();
        } else {
            mostrarToast(res.message || 'No se pudo eliminar el cliente.', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        mostrarToast('Error crítico al intentar eliminar al cliente.', 'error');
    });
}

// Procesar el envío del formulario (Creación o Edición)
form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`help.php?action=${accionActual}`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Fallo de respuesta del servidor.');
        return response.json();
    })
    .then(res => {
        if (res.status === true || res.success === true) {
            mostrarToast(res.message || 'Operación completada exitosamente.', 'success');
            cerrarModal();
            actualizarTabla();
        } else {
            mostrarToast(res.message || 'No se pudo procesar la solicitud.', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        mostrarToast('Error crítico al procesar el formulario del cliente.', 'error');
    });
});

// Actualizar tabla dinámicamente sin recargar la página
function actualizarTabla() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nuevoTbody = doc.getElementById('tabla-clientes-body');
            const actualTbody = document.getElementById('tabla-clientes-body');
            
            if (nuevoTbody && actualTbody) {
                actualTbody.innerHTML = nuevoTbody.innerHTML;
            }
        })
        .catch(error => console.error('Error al actualizar la tabla:', error));
}