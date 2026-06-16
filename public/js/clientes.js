/**
 * Control Asíncrono - Gestión de Clientes
 * Desarrollado bajo especificaciones de Tailwind CSS v4 y UX de Usuarios
 */

const modal = document.getElementById('modal-cliente');
const form = document.getElementById('form-cliente');
const infoPersonaBlock = document.getElementById('info-persona-encontrada');
const inputPersona = document.getElementById('input-persona'); // Referencia directa al input oculto

function abrirModalCrear() {
    form.reset();
    infoPersonaBlock.classList.add('hidden');
    inputPersona.value = ''; // Limpiamos el ID oculto por seguridad al abrir
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModal() {
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// Cierre perimetral al clickear fuera del contenedor blanco del modal
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

// Búsqueda asíncrona de persona por cédula
function buscarPersona() {
    const cedulaInput = document.getElementById('buscar-cedula').value;
    const cedula = cedulaInput.trim();

    if (!cedula) {
        mostrarToast('Por favor ingrese un número de cédula válido.', 'error');
        return;
    }

    fetch(`help.php?action=buscar_persona&cedula_identidad=${encodeURIComponent(cedula)}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === true && res.data) {
                // AQUÍ ESTÁ LA SOLUCIÓN: Asignamos el ID al input oculto
                inputPersona.value = res.data.id; 
                
                // Llenamos la cajita verde con los datos
                document.getElementById('txt-persona-nombre').textContent = res.data.nombre;
                document.getElementById('txt-persona-correo').textContent = res.data.correo || 'Sin correo registrado';
                
                infoPersonaBlock.classList.remove('hidden');
                mostrarToast('Persona vinculada con éxito. Ya puede generar la inscripción.', 'success');
            } else {
                // Si no existe, escondemos la caja y vaciamos el ID
                infoPersonaBlock.classList.add('hidden');
                inputPersona.value = ''; 
                mostrarToast(res.message || 'No se encontró ninguna persona con esa cédula.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast('Error de comunicación con el servidor al buscar.', 'error');
        });
}

// Procesar el guardado del cliente final
form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Verificación de seguridad en el frontend (Evita el error de tu captura de pantalla)
    if (!inputPersona.value || inputPersona.value.trim() === '') {
        mostrarToast('Debe buscar y asociar una persona obligatoriamente.', 'error');
        return;
    }

    const formData = new FormData(this);

    fetch('help.php?action=crear_cliente', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Fallo de respuesta del servidor.');
        return response.json();
    })
    .then(res => {
        if (res.status === true || res.success === true) {
            mostrarToast(res.message || 'Cliente registrado exitosamente.', 'success');
            cerrarModal();
            
            // Recarga sutil de sincronización de la tabla a los 1.5 segundos
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarToast(res.message || 'No se pudo procesar la inscripción.', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        mostrarToast('Error crítico al intentar guardar el cliente.', 'error');
    });
});