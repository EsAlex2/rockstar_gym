/**
 * Gestión Asíncrona y Auditoría de Pagos
 * Autor: Alex Madrid
 */

const modalPago = document.getElementById("modal-pago");
const modalEstatus = document.getElementById("modal-estatus");
const formPago = document.getElementById("form-pago");
const formEstatus = document.getElementById("form-estatus");

// --- MODAL: REGISTRAR PAGO ---
function abrirModalCrear() {
  formPago.reset();
  modalPago.classList.remove("hidden");
  modalPago.classList.add("flex");
}

function cerrarModal() {
  modalPago.classList.remove("flex");
  modalPago.classList.add("hidden");
}

// --- MODAL: CAMBIAR ESTATUS ---
function abrirModalEstatus(idPago, estatusActual) {
  formEstatus.reset();
  document.getElementById("estatus-id-pago").value = idPago;
  document.getElementById("txt-estatus-actual").textContent = estatusActual;
  modalEstatus.classList.remove("hidden");
  modalEstatus.classList.add("flex");
}

function cerrarModalEstatus() {
  modalEstatus.classList.remove("flex");
  modalEstatus.classList.add("hidden");
}

// Cerrar modales si se hace click fuera de su caja contenedora
window.addEventListener("click", (e) => {
  if (e.target === modalPago) cerrarModal();
  if (e.target === modalEstatus) cerrarModalEstatus();
});

// Mensajes Toast personalizados y estilizados según diseño base
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

// Interceptar envío del formulario de Registro de Pago
formPago.addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("help.php?action=registrar_pago", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Error crítico en el servidor de pasarela contable.");
      return response.json();
    })
    .then((res) => {
      if (res.status === true) {
        mostrarToast(res.message || "Pago procesado y resguardado con éxito", "success");
        cerrarModal();
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        mostrarToast(res.message || "La pasarela rechazó la inserción del pago.", "error");
      }
    })
    .catch((err) => {
      mostrarToast(err.message, "error");
    });
});

// Interceptar envío del formulario para Cambio de Estatus
formEstatus.addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("help.php?action=cambiar_estatus_pago", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Fallo crítico al alterar el estatus contable.");
      return response.json();
    })
    .then((res) => {
      if (res.status === true) {
        mostrarToast(res.message || "Estatus de auditoría modificado correctamente", "success");
        cerrarModalEstatus();
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        mostrarToast(res.message || "No se pudo cambiar el estado del registro.", "error");
      }
    })
    .catch((err) => {
      mostrarToast(err.message, "error");
    });
});