/**
 * Gestión Asíncrona del Módulo de Horarios y Cronogramas
 * Autor: Alex Madrid
 */

let tabActiva = "cronogramas";
let accionBloque = "crear_horario";

// Cambiar de pestaña (Vista Administrador)
function switchTab(tabName) {
  tabActiva = tabName;
  const tabs = ["cronogramas", "inscripciones", "bloques"];
  
  tabs.forEach((tab) => {
    const content = document.getElementById(`tab-content-${tab}`);
    const btn = document.getElementById(`tab-btn-${tab}`);
    
    if (content && btn) {
      if (tab === tabName) {
        content.classList.remove("hidden");
        btn.classList.replace("border-transparent", "border-blue-600");
        btn.classList.replace("text-gray-500", "text-blue-600");
        btn.classList.add("dark:text-blue-400");
      } else {
        content.classList.add("hidden");
        btn.classList.replace("border-blue-600", "border-transparent");
        btn.classList.replace("text-blue-600", "text-gray-500");
        btn.classList.remove("dark:text-blue-400");
      }
    }
  });
}

// Modales
function abrirModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }
}

function cerrarModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.remove("flex");
    modal.classList.add("hidden");
  }
}

function abrirModalAsignarHorario() {
  const form = document.getElementById("form-asignar-horario");
  if (form) form.reset();
  abrirModal("modal-asignar-horario");
}

function abrirModalAsignarCliente() {
  const form = document.getElementById("form-asignar-cliente");
  if (form) form.reset();
  abrirModal("modal-asignar-cliente");
}

function abrirModalCrearBloque() {
  accionBloque = "crear_horario";
  const form = document.getElementById("form-bloque-horario");
  if (form) form.reset();
  
  const idInput = document.getElementById("input-id-horario");
  if (idInput) idInput.value = "";
  
  const titulo = document.getElementById("modal-bloque-titulo");
  if (titulo) titulo.textContent = "Registrar Nuevo Bloque Horario";
  
  const btn = document.getElementById("btn-bloque-guardar");
  if (btn) btn.textContent = "Guardar Bloque";
  
  abrirModal("modal-bloque-horario");
}

function abrirModalEditarBloque(id, horaInicio, horaFin) {
  accionBloque = "actualizar_horario";
  const form = document.getElementById("form-bloque-horario");
  if (form) form.reset();
  
  const idInput = document.getElementById("input-id-horario");
  const iniInput = document.getElementById("input-hora-inicio");
  const finInput = document.getElementById("input-hora-fin");
  
  if (idInput) idInput.value = id;
  if (iniInput) iniInput.value = horaInicio;
  if (finInput) finInput.value = horaFin;
  
  const titulo = document.getElementById("modal-bloque-titulo");
  if (titulo) titulo.textContent = "Editar Bloque Horario";
  
  const btn = document.getElementById("btn-bloque-guardar");
  if (btn) btn.textContent = "Actualizar Bloque";
  
  abrirModal("modal-bloque-horario");
}

// Toasts
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

// Actualizar Vista
function actualizarVista() {
  fetch(window.location.href)
    .then((response) => response.text())
    .then((html) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      
      const elementsToUpdate = [
        "tabla-cronogramas-body",
        "tabla-inscripciones-body",
        "tabla-bloques-body",
        "modal-asignar-horario",
        "modal-asignar-cliente",
        "main"
      ];
      
      elementsToUpdate.forEach((id) => {
        const nuevo = doc.getElementById(id);
        const actual = document.getElementById(id);
        if (nuevo && actual && id !== "main") {
          actual.innerHTML = nuevo.innerHTML;
        } else if (id === "main" && nuevo && actual) {
          // Si estamos en la vista de cliente/entrenador, recargar el main completo
          // Pero preservamos la pestaña activa si es administrador
          const rol = nuevo.querySelector(".border-b");
          if (!rol) {
            actual.innerHTML = nuevo.innerHTML;
          } else {
            // Es vista admin, actualizamos tablas sin romper los botones/pestañas
            const subIds = ["tabla-cronogramas-body", "tabla-inscripciones-body", "tabla-bloques-body"];
            subIds.forEach(subId => {
              const nSub = doc.getElementById(subId);
              const aSub = document.getElementById(subId);
              if (nSub && aSub) aSub.innerHTML = nSub.innerHTML;
            });
          }
        }
      });

      // Asegurar que la pestaña activa se mantenga visualmente correcta
      if (document.getElementById(`tab-btn-${tabActiva}`)) {
        switchTab(tabActiva);
      }
    })
    .catch((err) => console.error("Error al refrescar la pantalla:", err));
}

// Envíos de Formulario (Admin)
document.addEventListener("DOMContentLoaded", () => {
  const formHorario = document.getElementById("form-asignar-horario");
  if (formHorario) {
    formHorario.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch("help.php?action=asignar_entrenamiento_horario", {
        method: "POST",
        body: formData
      })
        .then(r => r.json())
        .then(res => {
          if (res.status === true || res.success === true) {
            mostrarToast(res.message || "Horario asignado con éxito", "success");
            cerrarModal("modal-asignar-horario");
            actualizarVista();
          } else {
            mostrarToast(res.message || res.error || "No se pudo realizar la asignación", "error");
          }
        })
        .catch(() => mostrarToast("Error de red", "error"));
    });
  }

  const formCliente = document.getElementById("form-asignar-cliente");
  if (formCliente) {
    formCliente.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch("help.php?action=inscribir_cliente_entrenamiento", {
        method: "POST",
        body: formData
      })
        .then(r => r.json())
        .then(res => {
          if (res.status === true || res.success === true) {
            mostrarToast(res.message || "Cliente inscrito con éxito", "success");
            cerrarModal("modal-asignar-cliente");
            actualizarVista();
          } else {
            mostrarToast(res.message || res.error || "Error al inscribir cliente", "error");
          }
        })
        .catch(() => mostrarToast("Error de red", "error"));
    });
  }

  const formBloque = document.getElementById("form-bloque-horario");
  if (formBloque) {
    formBloque.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch(`help.php?action=${accionBloque}`, {
        method: "POST",
        body: formData
      })
        .then(r => r.json())
        .then(res => {
          if (res.status === true || res.success === true) {
            mostrarToast(res.message || "Bloque de horario guardado con éxito", "success");
            cerrarModal("modal-bloque-horario");
            actualizarVista();
          } else {
            mostrarToast(res.message || res.error || "Error al procesar bloque", "error");
          }
        })
        .catch(() => mostrarToast("Error de red", "error"));
    });
  }
});

// Acciones de Fila (Admin / Clientes)
function eliminarHorarioEntrenamiento(idEnt, idHor, dia) {
  if (confirm(`¿Estás seguro de remover este horario de este entrenamiento para los días ${dia}?`)) {
    const formData = new FormData();
    formData.append("id_entrenamiento", idEnt);
    formData.append("id_horario", idHor);
    formData.append("dia_semana", dia);
    
    fetch("help.php?action=desasignar_entrenamiento_horario", {
      method: "POST",
      body: formData
    })
      .then(r => r.json())
      .then(res => {
        if (res.status === true || res.success === true) {
          mostrarToast(res.message || "Horario desasignado", "success");
          actualizarVista();
        } else {
          mostrarToast(res.message || res.error || "Error", "error");
        }
      })
      .catch(() => mostrarToast("Error de red", "error"));
  }
}

function eliminarInscripcion(idCli, idEnt) {
  if (confirm("¿Estás seguro de desinscribir a este cliente de este entrenamiento?")) {
    const formData = new FormData();
    formData.append("id_cliente", idCli);
    formData.append("id_entrenamiento", idEnt);
    
    fetch("help.php?action=desinscribir_cliente_entrenamiento", {
      method: "POST",
      body: formData
    })
      .then(r => r.json())
      .then(res => {
        if (res.status === true || res.success === true) {
          mostrarToast(res.message || "Inscripción cancelada", "success");
          actualizarVista();
        } else {
          mostrarToast(res.message || res.error || "Error", "error");
        }
      })
      .catch(() => mostrarToast("Error de red", "error"));
  }
}

function eliminarBloqueHorario(idHor) {
  if (confirm("¿Estás seguro de eliminar este bloque de horas? Esta acción desvinculará todos los cronogramas que lo usen.")) {
    const formData = new FormData();
    formData.append("id_horario", idHor);
    
    fetch("help.php?action=eliminar_horario", {
      method: "POST",
      body: formData
    })
      .then(r => r.json())
      .then(res => {
        if (res.status === true || res.success === true) {
          mostrarToast(res.message || "Bloque de horario eliminado", "success");
          actualizarVista();
        } else {
          mostrarToast(res.message || res.error || "Error", "error");
        }
      })
      .catch(() => mostrarToast("Error de red", "error"));
  }
}

// Acciones Vista Cliente
function inscribirEntrenamiento(idEnt) {
  const formData = new FormData();
  formData.append("id_entrenamiento", idEnt);
  
  fetch("help.php?action=inscribir_cliente_entrenamiento", {
    method: "POST",
    body: formData
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === true || res.success === true) {
        mostrarToast(res.message || "Inscripción exitosa", "success");
        actualizarVista();
      } else {
        mostrarToast(res.message || res.error || "No se pudo realizar la inscripción", "error");
      }
    })
    .catch(() => mostrarToast("Error de red", "error"));
}

function desinscribirEntrenamiento(idEnt) {
  if (confirm("¿Seguro que deseas retirarte de este entrenamiento? Perderás tu lugar en este horario.")) {
    const formData = new FormData();
    formData.append("id_entrenamiento", idEnt);
    
    fetch("help.php?action=desinscribir_cliente_entrenamiento", {
      method: "POST",
      body: formData
    })
      .then(r => r.json())
      .then(res => {
        if (res.status === true || res.success === true) {
          mostrarToast(res.message || "Inscripción retirada", "success");
          actualizarVista();
        } else {
          mostrarToast(res.message || res.error || "Error", "error");
        }
      })
      .catch(() => mostrarToast("Error de red", "error"));
  }
}
