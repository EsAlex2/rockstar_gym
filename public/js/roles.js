/**
 * Gestión Asíncrona de Roles de Sistema
 * Autor: Alex Madrid
 */

const modal = document.getElementById("modal-rol");
const form = document.getElementById("form-rol");
const modalTitulo = document.getElementById("modal-titulo");
const btnGuardar = document.getElementById("btn-guardar");

// Campos del formulario
const inputIdRol = document.getElementById("input-id-rol");
const inputNombreRol = document.getElementById("input-nombre-rol");
const inputDescripcion = document.getElementById("input-descripcion");

// Variable de control de acción activa externa
let accionActual = "crear_rol";

function abrirModalCrear() {
  form.reset();
  accionActual = "crear_rol";
  inputIdRol.value = "";
  modalTitulo.textContent = "Registrar Nuevo Rol";
  btnGuardar.textContent = "Guardar Rol";
  
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function abrirModalEditar(id, nombre, descripcion) {
  form.reset();
  accionActual = "actualizar_rol";
  
  // Poblar los inputs con la data actual de la fila
  inputIdRol.value = id;
  inputNombreRol.value = nombre;
  inputDescripcion.value = descripcion;
  
  modalTitulo.textContent = "Editar Rol Existente";
  btnGuardar.textContent = "Actualizar Cambios";
  
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function cerrarModal() {
  modal.classList.remove("flex");
  modal.classList.add("hidden");
}

// Cerrar al cliquear fuera del contenedor del formulario
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
      : "text-purple-700 dark:text-purple-400 border-purple-100 dark:border-purple-900/30 bg-purple-50/50"
  }`;

  toast.innerHTML =
    tipo === "success"
      ? `<svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>`
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

// Intercepción y procesamiento AJAX del formulario
form.addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch(`help.php?action=${accionActual}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Fallo crítico en la respuesta del servidor local.");
      return response.json();
    })
    .then((res) => {
      // Validar mapeo de respuestas del backend standard ('status' o 'success')
      if (res.status === true || res.success === true) {
        mostrarToast(res.message || "Operación procesada con éxito", "success");
        cerrarModal();

        setTimeout(() => {
          window.location.reload();
        }, 1200);
      } else {
        mostrarToast(res.message || res.error || "Error al procesar la solicitud", "error");
      }
    })
    .catch((err) => {
      mostrarToast(err.message, "error");
    });
});