/**
 * Gestión Asíncrona de Membresías y Planes
 * Sigue los patrones de interfaz UI de personas.js adaptados al flujo de planes
 */

let modal;
let form;

// Esperamos a que el DOM se cargue por completo antes de mapear los elementos
document.addEventListener("DOMContentLoaded", () => {
  modal = document.getElementById("modal-membresia");
  form = document.getElementById("form-membresia");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch("help.php?action=asignar_membresia", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) throw new Error("Fallo crítico en el servidor de membresías.");
          return response.json();
        })
        .then((res) => {
          if (res.status === true) {
            mostrarToast(res.message || "Membresía asignada con éxito.", "success");
            cerrarModal();
            actualizarTabla();
          } else {
            mostrarToast(res.message || "Ocurrió un error al procesar el plan.", "error");
          }
        })
        .catch((error) => {
          mostrarToast(error.message, "error");
        });
    });
  }

  // Cierra el modal si se hace click fuera del recuadro del formulario
  window.addEventListener("click", (e) => {
    if (e.target === modal) cerrarModal();
  });
});

// Actualizar tabla dinámicamente sin recargar la página
function actualizarTabla() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nuevoTbody = doc.getElementById('tabla-membresias-body');
            const actualTbody = document.getElementById('tabla-membresias-body');
            
            if (nuevoTbody && actualTbody) {
                actualTbody.innerHTML = nuevoTbody.innerHTML;
            }
        })
        .catch(error => console.error('Error al actualizar la tabla:', error));
}

// Funciones globales vinculadas al onclick del botón HTML
function abrirModalCrear() {
  if (form) form.reset();
  if (modal) {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }
}

function cerrarModal() {
  if (modal) {
    modal.classList.remove("flex");
    modal.classList.add("hidden");
  }
}

function mostrarToast(mensaje, tipo = "success") {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `p-4 text-sm font-medium rounded-xl border shadow-lg transition-all transform translate-y-2 opacity-0 flex items-center gap-2 pointer-events-auto bg-white dark:bg-gray-800 ${
    tipo === "error"
      ? "text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30 bg-rose-50/50"
      : "text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/50"
  }`;

  toast.innerHTML = tipo === "success" 
      ? `<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>${mensaje}</span>` 
      : `<svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> <span>${mensaje}</span>`;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.remove("translate-y-2", "opacity-0");
  }, 10);

  setTimeout(() => {
    toast.classList.add("opacity-0", "translate-y-2");
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}