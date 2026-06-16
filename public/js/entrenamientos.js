/**
 * Gestión Asíncrona de Entrenamientos
 * Sigue de manera estricta los patrones arquitectónicos UI de personas.js
 */

const modal = document.getElementById('modal-entrenamiento');
const form = document.getElementById('form-entrenamiento');

function abrirModalCrear() {
    form.reset();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
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

    // Renderización de ícono condicional estructurado
    toast.innerHTML = tipo === 'success' 
        ? `<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`
        : `<svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`;

    container.appendChild(toast);

    // Animación fluida de entrada por hardware
    setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    }, 10);

    // Degradación progresiva de opacidad y remoción de nodo a los 4 segundos
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Intercepción táctica del procesamiento tradicional de formularios
form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Despacho asíncrono controlado hacia nuestro gateway perimetral
    fetch('help.php?action=crear_entrenamiento', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Fallo de respuesta crítica del backend.');
        return response.json();
    })
    .then(res => {
        // Tu API devuelve un indicador booleano de éxito (true / false o success)
        if (res.status === true || res.success === true) {
            mostrarToast(res.message || "Planificación de entrenamiento registrada con éxito.", 'success');
            cerrarModal();
            // Recarga controlada de la grilla a los 1.2 segundos para sincronizar la UI
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarToast(res.error || res.message || "No se pudo procesar el entrenamiento.", 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        mostrarToast("Fallo de conexión o error crítico de sintaxis de datos.", 'error');
    });
});