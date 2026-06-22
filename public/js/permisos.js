/**
 * Gestión Asíncrona de Permisos de Sistema
 * Autor: Alex Madrid
 */

const modal = document.getElementById("modal-permiso");
const form = document.getElementById("form-permiso");

function abrirModalCrear() {
  form.reset();
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

  fetch("help.php?action=crear_permiso", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Fallo crítico en el servidor de base de datos.");
      return response.json();
    })
    .then((res) => {
      // Evaluamos el estatus devuelto por la pasarela de permisosController
      if (res.status === true) {
        mostrarToast(res.message || "Permiso registrado exitosamente", "success");
        cerrarModal();

        // Espera de cortesía para que el operador logre apreciar la notificación antes del reload
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        // En caso de que se intente duplicar una clave string o falte un campo obligatorio
        mostrarToast(res.message || "Error al procesar la solicitud", "error");
      }
    })
    .catch((err) => {
      mostrarToast(err.message, "error");
    });
});