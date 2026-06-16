// public/js/usuarios.js

let urlAccion = '';

function abrirModalCrear() {
    urlAccion = 'procesar_usuarios.php?action=crear';
    document.getElementById('modal-titulo').innerText = 'Registrar Nuevo Usuario';
    document.getElementById('form-usuario').reset();

    document.getElementById('buscar-cedula').value = '';
    document.getElementById('input-persona').value = '';
    document.getElementById('info-persona-encontrada').classList.add('hidden');

    document.getElementById('grupo-persona').classList.remove('hidden');
    document.getElementById('grupo-rol').classList.remove('hidden');
    document.getElementById('grupo-username').classList.add('hidden');
    document.getElementById('grupo-estatus').classList.add('hidden');
    document.getElementById('grupo-password').classList.add('hidden');

    document.getElementById('modal-usuario').classList.remove('hidden');
}

function mostrarAlerta(tipo, mensaje) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `transform translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto p-4 rounded-xl shadow-lg border flex items-start gap-3 bg-white dark:bg-gray-800 `;

    if (tipo === 'success') {
        toast.className += 'border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-400';
    } else {
        toast.className += 'border-rose-100 dark:border-rose-900/30 bg-rose-50/50 dark:bg-rose-950/20 text-rose-800 dark:text-rose-400';
    }

    const icono = tipo === 'success' ?
        `<svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>` :
        `<svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;

    toast.innerHTML = `
        ${icono}
        <div class="flex-1">
            <p class="text-sm font-semibold">${tipo === 'success' ? 'Éxito' : 'Operación Fallida'}</p>
            <p class="text-xs mt-0.5 opacity-90">${mensaje}</p>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    }, 10);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-[-10px]');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function buscarPersona() {
    const cedulaInput = document.getElementById('buscar-cedula');
    const cedula = cedulaInput ? cedulaInput.value.trim() : '';

    if (!cedula) {
        mostrarAlerta('error', "Por favor, ingrese un número de cédula para buscar.");
        return;
    }

    fetch(`help.php?action=buscar_persona&cedula_identidad=${encodeURIComponent(cedula)}`)
        .then(res => {
            if (!res.ok) throw new Error("Error en la comunicación con el servidor.");
            return res.json();
        })
        .then(res => {
            if (res && res.data && Object.keys(res.data).length > 0) {
                const persona = res.data;
                document.getElementById('input-persona').value = persona.id || '';
                document.getElementById('txt-persona-nombre').innerText = persona.nombre || 'Persona sin nombre';
                document.getElementById('txt-persona-correo').innerText = "Correo: " + (persona.correo || 'No registrado');

                const inputEmail = document.getElementById('input-email');
                if (inputEmail && persona.correo) {
                    inputEmail.value = persona.correo;
                }

                document.getElementById('info-persona-encontrada').classList.remove('hidden');
                mostrarAlerta('success', "Persona localizada correctamente.");
            } else {
                mostrarAlerta('error', res.message || "No se encontró ningún registro.");
                limpiarContenedorPersona();
            }
        })
        .catch(err => {
            console.error(err);
            mostrarAlerta('error', "Error al procesar la consulta.");
            limpiarContenedorPersona();
        });
}

function limpiarContenedorPersona() {
    document.getElementById('input-persona').value = '';
    document.getElementById('info-persona-encontrada').classList.add('hidden');
}

function abrirModalEditar(user) {
    urlAccion = 'procesar_usuarios.php?action=actualizar';
    document.getElementById('modal-titulo').innerText = 'Modificar Parámetros de Usuario';

    document.getElementById('input-id-usuario').value = user.id_usuario || user.id || '';
    document.getElementById('input-username').value = user.username || '';
    document.getElementById('input-email').value = user.email_user || '';
    document.getElementById('input-rol').value = user.id_rol || '';
    document.getElementById('input-estatus').value = user.id_estatus || '1';

    document.getElementById('grupo-persona').classList.add('hidden');
    document.getElementById('grupo-username').classList.remove('hidden');
    document.getElementById('grupo-rol').classList.remove('hidden');
    document.getElementById('grupo-estatus').classList.remove('hidden');
    document.getElementById('grupo-password').classList.add('hidden');

    document.getElementById('modal-usuario').classList.remove('hidden');
}

function abrirModalPassword(username, email) {
    urlAccion = 'procesar_usuarios.php?action=cambiar_pass';
    document.getElementById('modal-titulo').innerText = 'Restablecer Seguridad / Contraseña';
    document.getElementById('form-usuario').reset();

    document.getElementById('input-username').value = username;
    document.getElementById('input-email').value = email;

    document.getElementById('grupo-persona').classList.add('hidden');
    document.getElementById('grupo-username').classList.add('hidden');
    document.getElementById('grupo-rol').classList.add('hidden');
    document.getElementById('grupo-estatus').classList.add('hidden');
    document.getElementById('grupo-password').classList.remove('hidden');

    document.getElementById('modal-usuario').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modal-usuario').classList.add('hidden');
}

// INYECTOR EXCLUSIVO DE INTERCEPCIÓN SUBMIT
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-usuario');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Detiene de raíz la recarga por defecto del HTML nativo

            const formData = new FormData(this);

            fetch(urlAccion, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error("Error en la respuesta de red.");
                return res.json();
            })
            .then(data => {
                const esExitoso = (data.success === true || data.status === true || data.status === "true");
                const mensaje = data.message || "Operación realizada con éxito.";

                if (esExitoso) {
                    mostrarAlerta('success', mensaje);
                    cerrarModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    mostrarAlerta('error', mensaje);
                }
            })
            .catch(err => {
                console.error(err);
                mostrarAlerta('error', "No se pudo conectar con el controlador de usuarios.");
            });
        });
    }
});