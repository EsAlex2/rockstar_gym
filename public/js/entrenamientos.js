/**
 * Gestión Asíncrona de Entrenamientos - CRUD Completo
 * Autor: Alex Madrid
 */

const modal = document.getElementById('modal-entrenamiento');
const form = document.getElementById('form-entrenamiento');

let accionActual = 'crear_entrenamiento';

function abrirModalCrear() {
    accionActual = 'crear_entrenamiento';
    form.reset();
    document.getElementById('id_entrenamiento').value = '';
    document.getElementById('modal-titulo').innerText = 'Registrar Nuevo Entrenamiento';
    document.getElementById('btn-submit').innerText = 'Guardar Planificación';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function abrirModalEditar(entrenamiento) {
    accionActual = 'actualizar_entrenamiento';
    form.reset();
    document.getElementById('id_entrenamiento').value = entrenamiento.id;
    document.getElementById('modal-titulo').innerText = 'Editar Entrenamiento';
    document.getElementById('btn-submit').innerText = 'Actualizar Entrenamiento';

    // Cargar datos en los campos
    document.getElementById('input-nombre').value = entrenamiento.nombre_entrenamiento;
    document.getElementById('input-descripcion').value = entrenamiento.descripcion || '';

    // Seleccionar entrenador
    const selectEntrenador = document.getElementById('select-entrenador');
    if (selectEntrenador) {
        Array.from(selectEntrenador.options).forEach(opt => {
            opt.selected = (opt.value == entrenamiento.id_entrenador);
        });
    }

    // Seleccionar sede
    const selectSede = document.getElementById('select-sede');
    if (selectSede) {
        Array.from(selectSede.options).forEach(opt => {
            opt.selected = (opt.value == entrenamiento.id_sede);
        });
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function eliminarEntrenamiento(id) {
    if (confirm("¿Estás seguro de que deseas eliminar este entrenamiento? Esta acción es irreversible.")) {
        const formData = new FormData();
        formData.append('id_entrenamiento', id);

        fetch('help.php?action=eliminar_entrenamiento', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.status === true || res.success === true) {
                mostrarToast(res.message || "Entrenamiento eliminado exitosamente.", "success");
                actualizarTabla();
            } else {
                mostrarToast(res.message || "Error al eliminar el entrenamiento.", "error");
            }
        })
        .catch(err => {
            mostrarToast("Error de conexión al eliminar.", "error");
        });
    }
}

function cerrarModal() {
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// Cierra el modal si se realiza click fuera del layout contenedor principal
window.addEventListener('click', (e) => {
    if (e.target === modal) cerrarModal();
});

// Sistema Unificado de Feedback Dinámico mediante Notificaciones en Pantalla (Toasts)
function mostrarToast(mensaje, tipo = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `p-4 text-sm font-medium rounded-xl border shadow-lg transition-all transform translate-y-2 opacity-0 flex items-center gap-2 pointer-events-auto bg-white dark:bg-gray-800 ${         
    tipo === 'error'              
        ? 'text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30 bg-rose-50/50'              
        : 'text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/50'     
    }`;

    toast.innerHTML = tipo === 'success' 
        ? `<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`
        : `<svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    }, 10);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Intercepción del formulario - crear o actualizar según accionActual
form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(`help.php?action=${accionActual}`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Fallo de respuesta crítica del backend.');
        return response.json();
    })
    .then(res => {
        if (res.status === true || res.success === true) {
            const msg = accionActual === 'crear_entrenamiento' 
                ? "Entrenamiento registrado con éxito." 
                : "Entrenamiento actualizado con éxito.";
            mostrarToast(res.message || msg, 'success');
            cerrarModal();
            actualizarTabla();
        } else {
            mostrarToast(res.error || res.message || "No se pudo procesar el entrenamiento.", 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        mostrarToast("Fallo de conexión o error crítico de sintaxis de datos.", 'error');
    });
});

// Actualizar tabla dinámicamente sin recargar la página
function actualizarTabla() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nuevoTbody = doc.getElementById('tabla-entrenamientos-body');
            const actualTbody = document.getElementById('tabla-entrenamientos-body');
            
            if (nuevoTbody && actualTbody) {
                actualTbody.innerHTML = nuevoTbody.innerHTML;
            }
        })
        .catch(error => console.error('Error al actualizar la tabla:', error));
}