/**
 * Gestión Asíncrona de Permisos de Sistema - CRUD Completo
 * Autor: Alex Madrid
 */

const modal = document.getElementById("modal-permiso");
const form = document.getElementById("form-permiso");
const modalTitulo = document.getElementById("modal-titulo");
const btnGuardar = document.getElementById("btn-guardar");

// Campos del formulario
const inputIdPermiso = document.getElementById("input-id-permiso");
const inputNombrePermiso = document.getElementById("input-permiso");
const inputDescripcion = document.getElementById("input-descripcion");

// Variable de control de acción activa externa
let accionActual = "crear_permiso";

function abrirModalCrear() {
  form.reset();
  accionActual = "crear_permiso";
  inputIdPermiso.value = "";
  modalTitulo.textContent = "Registrar Nuevo Permiso";
  btnGuardar.textContent = "Guardar Permiso";
  
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function abrirModalEditar(id, nombre, descripcion) {
  form.reset();
  accionActual = "actualizar_permiso";
  
  // Poblar los inputs con la data actual de la fila
  inputIdPermiso.value = id;
  inputNombrePermiso.value = nombre;
  inputDescripcion.value = descripcion;
  
  modalTitulo.textContent = "Editar Permiso Existente";
  btnGuardar.textContent = "Actualizar Cambios";
  
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function cerrarModal() {
  modal.classList.remove("flex");
  modal.classList.add("hidden");
}

// Cerrar la interfaz modal si el usuario hace clic fuera de la caja contenedora
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

// Interceptamos el envío del formulario de permisos
form.addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch(`help.php?action=${accionActual}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Fallo crítico en el servidor de base de datos.");
      return response.json();
    })
    .then((res) => {
      // Evaluamos el estatus devuelto por la pasarela de permisosController
      if (res.status === true || res.success === true) {
        const msg = accionActual === "crear_permiso" ? "Permiso registrado exitosamente." : "Permiso actualizado exitosamente.";
        mostrarToast(res.message || msg, "success");
        cerrarModal();
        actualizarTabla();
      } else {
        mostrarToast(res.message || res.error || "Error al procesar la solicitud", "error");
      }
    })
    .catch((err) => {
      mostrarToast(err.message, "error");
    });
});

// Actualizar tabla dinámicamente sin recargar la página
function actualizarTabla() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nuevoTbody = doc.getElementById('tabla-permisos-body');
            const actualTbody = document.getElementById('tabla-permisos-body');
            
            if (nuevoTbody && actualTbody) {
                actualTbody.innerHTML = nuevoTbody.innerHTML;
            }
        })
        .catch(error => console.error('Error al actualizar la tabla:', error));
}

function eliminarPermiso(idPermiso) {
  if (confirm("¿Estás seguro de que deseas eliminar este permiso? Esto revocará este privilegio de todos los roles asociados de inmediato.")) {
    const formData = new FormData();
    formData.append("id_permiso", idPermiso);

    fetch("help.php?action=eliminar_permiso", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((res) => {
        if (res.status === true || res.success === true) {
          mostrarToast(res.message || "Permiso eliminado exitosamente.", "success");
          actualizarTabla();
        } else {
          mostrarToast(res.message || res.error || "No se pudo eliminar el permiso.", "error");
        }
      })
      .catch((err) => {
        mostrarToast("Error de conexión al intentar eliminar.", "error");
      });
  }
}