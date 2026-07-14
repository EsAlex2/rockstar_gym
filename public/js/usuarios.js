/**
 * Gestión Asíncrona de Usuarios
 * Autor: Alex Madrid
 */

const modal = document.getElementById("modal-usuario");
const form = document.getElementById("form-usuario");

let accionActual = 'crear_usuario';

function abrirModalCrear() {
  accionActual = 'crear_usuario';
  form.reset();
  document.getElementById('id_usuario').value = '';
  document.getElementById('modal-titulo').innerText = 'Registrar Nuevo Usuario';
  document.getElementById('select-persona').disabled = false;

  const labelPass = document.getElementById('label-password');
  const inputPass = document.getElementById('input-password');
  if (labelPass && inputPass) {
    labelPass.innerText = 'Contraseña por Defecto';
    inputPass.value = 'Cliente2026*';
    inputPass.type = 'text';
    inputPass.readOnly = true;
    inputPass.placeholder = '';
    inputPass.className = "w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed focus:outline-hidden";
  }

  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function abrirModalEditar(usuario) {
  accionActual = 'actualizar_usuario';
  form.reset();
  document.getElementById('id_usuario').value = usuario.id_usuario;
  document.getElementById('modal-titulo').innerText = 'Editar Usuario';
  
  // Asignamos la persona
  const selectPersona = document.getElementById('select-persona');
  Array.from(selectPersona.options).forEach(opt => {
      if (opt.text.includes(usuario.cedula_identidad)) {
          opt.selected = true;
      }
  });

  document.getElementById('input-usuario').value = usuario.email_user;
  
  // Asignar el rol
  const selectRol = document.querySelector('select[name="id_rol"]');
  Array.from(selectRol.options).forEach(opt => {
      if (opt.text === usuario.rol) {
          opt.selected = true;
      }
  });

  const labelPass = document.getElementById('label-password');
  const inputPass = document.getElementById('input-password');
  if (labelPass && inputPass) {
    labelPass.innerText = 'Nueva Contraseña (Opcional)';
    inputPass.value = '';
    inputPass.type = 'password';
    inputPass.readOnly = false;
    inputPass.placeholder = 'Dejar vacío para mantener la actual...';
    inputPass.className = "w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500";
  }

  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function eliminarUsuario(id_usuario) {
    if (confirm("¿Estás seguro de que deseas eliminar este usuario? Esta acción es irreversible.")) {
        const formData = new FormData();
        formData.append('id_usuario', id_usuario);

        fetch('help.php?action=eliminar_usuario', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.status === true || res.success === true) {
                mostrarToast(res.message || "Usuario eliminado exitosamente.", "success");
                actualizarTabla();
            } else {
                mostrarToast(res.message || "Error al eliminar el usuario.", "error");
            }
        })
        .catch(err => {
            mostrarToast("Error de conexión al eliminar.", "error");
        });
    }
}

function cerrarModal() {
  modal.classList.remove("flex");
  modal.classList.add("hidden");
}

window.addEventListener("click", (e) => {
  if (e.target === modal) cerrarModal();
});

function mostrarToast(mensaje, tipo = "success") {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `p-4 text-sm font-medium rounded-xl border shadow-lg transition-all transform translate-y-2 opacity-0 flex items-center gap-2 pointer-events-auto bg-white dark:bg-gray-800 ${
    tipo === "error"
      ? "text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30 bg-rose-50/50"
      : "text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/50"
  }`;

  toast.innerHTML =
    tipo === "success"
      ? `<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`
      : `<svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.remove("translate-y-2", "opacity-0");
  }, 10);

  setTimeout(() => {
    toast.classList.add("opacity-0", "translate-y-2");
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// Interceptamos el envío del formulario de usuarios
form.addEventListener("submit", function (e) {
  e.preventDefault();

  // Habilitar campos temporalmente para que viajen en el FormData
  const selectPersona = document.getElementById('select-persona');
  const wasDisabled = selectPersona.disabled;
  if(wasDisabled) selectPersona.disabled = false;

  const formData = new FormData(this);

  if(wasDisabled) selectPersona.disabled = true; // Restaurar

  fetch(`help.php?action=${accionActual}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Fallo crítico en el servidor.");
      return response.json();
    })
    .then((res) => {
      // Validamos si el estatus devuelto por el controlador es exitoso
      if (res.status === true || res.success === true) {
        mostrarToast(res.message || (accionActual === 'crear_usuario' ? "Usuario creado con éxito" : "Usuario actualizado con éxito"), "success");
        cerrarModal();
        actualizarTabla();
      } else {
        // Si el controlador devolvió false (ej: el correo ya existe)
        mostrarToast(res.message || res.error || "Error al procesar la solicitud", "error");
      }
    })
    .catch((err) => {
      mostrarToast(err.message, "error");
    });
});

// Automatización: Cargar el correo de la persona seleccionada en el campo de usuario
const selectPersona = document.getElementById("select-persona");
const inputUsuario = document.getElementById("input-usuario");

if (selectPersona && inputUsuario) {
  selectPersona.addEventListener("change", function () {
    // Obtenemos la opción que fue seleccionada
    const opcionSeleccionada = this.options[this.selectedIndex];
    // Extraemos el valor del atributo data-email
    const emailAsociado = opcionSeleccionada.getAttribute("data-email");

    if (emailAsociado) {
      inputUsuario.value = emailAsociado;
    } else {
      inputUsuario.value = "";
    }
  });
}

// Actualizar tabla dinámicamente sin recargar la página
function actualizarTabla() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nuevoTbody = doc.getElementById('tabla-usuarios-body');
            const actualTbody = document.getElementById('tabla-usuarios-body');
            
            if (nuevoTbody && actualTbody) {
                actualTbody.innerHTML = nuevoTbody.innerHTML;
            }
        })
        .catch(error => console.error('Error al actualizar la tabla:', error));
}

function toggleEstatusUsuario(id_usuario, isChecked) {
    // Si isChecked es true, el nuevo estatus es 1 (Activo), si es false es 2 (Inactivo)
    // Asumimos que 1 = Activo y 2 = Inactivo basándonos en la inserción de DB por defecto.
    const nuevo_id_estatus = isChecked ? 1 : 2; 
    
    const formData = new FormData();
    formData.append('id_usuario', id_usuario);
    formData.append('nuevo_id_estatus', nuevo_id_estatus);

    fetch('help.php?action=cambiar_estatus_usuario', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        if (res.status === true || res.success === true) {
            mostrarToast("Estado del usuario actualizado.", "success");
            actualizarTabla(); // Recargamos para reflejar el texto y color correcto
        } else {
            mostrarToast(res.message || "Error al cambiar el estado.", "error");
            // Si falla, revertimos el toggle visualmente actualizando la tabla
            actualizarTabla();
        }
    })
    .catch(err => {
        mostrarToast("Error de conexión al cambiar estado.", "error");
        actualizarTabla();
    });
}
