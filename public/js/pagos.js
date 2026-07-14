/**
 * Gestión Asíncrona y Auditoría de Pagos - CRUD Completo
 * Autor: Alex Madrid
 */

const modalPago = document.getElementById("modal-pago");
const formPago = document.getElementById("form-pago");

let accionActual = 'registrar_pago';

// --- MODAL: REGISTRAR PAGO ---
function abrirModalCrear() {
  accionActual = 'registrar_pago';
  formPago.reset();
  document.getElementById("id_pago").value = "";
  document.getElementById("modal-titulo").textContent = "Registrar Transacción de Pago Seguro";
  document.getElementById("btn-submit").textContent = "Guardar Transacción";
  modalPago.classList.remove("hidden");
  modalPago.classList.add("flex");
}

// --- MODAL: EDITAR PAGO ---
function abrirModalEditar(pago) {
  accionActual = 'actualizar_pago';
  formPago.reset();
  
  document.getElementById("id_pago").value = pago.id;
  document.getElementById("modal-titulo").textContent = "Editar Transacción de Pago";
  document.getElementById("btn-submit").textContent = "Actualizar Transacción";

  // Pre-cargar valores
  document.getElementById("select-cliente").value = pago.id_cliente;
  document.getElementById("select-plan").value = pago.id_plan;
  document.getElementById("select-banco").value = pago.id_banco;
  document.getElementById("select-estatus").value = pago.id_estatus;
  document.getElementById("input-monto").value = pago.monto;
  document.getElementById("input-fecha").value = pago.fecha_pago;
  document.getElementById("input-referencia").value = pago.cod_referencia;

  modalPago.classList.remove("hidden");
  modalPago.classList.add("flex");
}

function cerrarModal() {
  modalPago.classList.remove("flex");
  modalPago.classList.add("hidden");
}

// --- ACCION: ELIMINAR PAGO ---
function eliminarPago(idPago) {
  if (confirm("¿Estás seguro de que deseas eliminar este registro de pago? Esto también podría eliminar o alterar el estado de la membresía del cliente de forma permanente.")) {
    const formData = new FormData();
    formData.append("id_pago", idPago);

    fetch("help.php?action=eliminar_pago", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((res) => {
        if (res.status === true || res.success === true) {
          mostrarToast(res.message || "Pago y membresía asociados eliminados.", "success");
          actualizarTabla();
        } else {
          mostrarToast(res.message || res.error || "No se pudo eliminar el registro de pago.", "error");
        }
      })
      .catch((err) => {
        mostrarToast("Error de conexión al intentar eliminar.", "error");
      });
  }
}

// Cerrar modales si se hace click fuera de su caja contenedora
window.addEventListener("click", (e) => {
  if (e.target === modalPago) cerrarModal();
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

// Interceptar envío del formulario de Registro / Actualización de Pago
formPago.addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch(`help.php?action=${accionActual}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Error en el servidor durante la transacción.");
      return response.json();
    })
    .then((res) => {
      if (res.status === true || res.success === true) {
        const msg = accionActual === 'registrar_pago' ? "Pago registrado exitosamente." : "Pago actualizado exitosamente.";
        mostrarToast(res.message || msg, "success");
        cerrarModal();
        actualizarTabla();
      } else {
        mostrarToast(res.message || res.error || "La transacción no pudo ser guardada.", "error");
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
            const nuevoTbody = doc.getElementById('tabla-pagos-body');
            const actualTbody = document.getElementById('tabla-pagos-body');
            
            if (nuevoTbody && actualTbody) {
                actualTbody.innerHTML = nuevoTbody.innerHTML;
            }
        })
        .catch(error => console.error('Error al actualizar la tabla:', error));
}