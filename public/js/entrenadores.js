/**
 * Gestión Asíncrona de Entrenadores
 * Sigue los patrones de diseño UI de personas.js y usuarios.js
 */

const modal = document.getElementById("modal-entrenador");
const form = document.getElementById("form-entrenador");
const btnVerificar = document.getElementById("btn-verificar-persona");
const btnSubmit = document.getElementById("btn-submit-entrenador");
const buscarCedulaInput = document.getElementById("buscar_cedula");
const idPersonaHidden = document.getElementById("id_persona_hidden");
const nombrePersonaMatch = document.getElementById("nombre_persona_match");

function abrirModalCrear() {
  form.reset();
  idPersonaHidden.value = "";
  nombrePersonaMatch.value = "Ninguna persona vinculada aún";
  btnSubmit.disabled = true;
  btnSubmit.classList.add("opacity-50", "cursor-not-allowed");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function cerrarModal() {
  modal.classList.remove("flex");
  modal.classList.add("hidden");
}

window.addEventListener("click", (e) => {
  if (e.target === modal) cerrarModal();
});

// Reutilización idéntica de tu sistema nativo de Toasts de respuesta
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

// Evento 1: Buscar y verificar si la persona existe en la BD corporativa
// Evento 1: Buscar y verificar si la persona existe en la BD corporativa
btnVerificar.addEventListener("click", () => {
  const cedula = buscarCedulaInput.value.trim();

  if (cedula === "") {
    mostrarToast("Ingrese un número de cédula válido para buscar.", "error");
    return;
  }

  fetch(`help.php?action=buscar_persona&cedula_identidad=${cedula}`)
    .then((response) => {
      if (!response.ok) throw new Error("Fallo en el servidor.");
      return response.json();
    })
    .then((res) => {
      // Adaptación a tu estructura nativa del controlador: res.status y res.data
      if (res && res.status === true && res.data) {
        // Si tu controlador devuelve un array de registros, tomamos el primero; si no, el objeto directo
        const persona = Array.isArray(res.data) ? res.data[0] : res.data;

        if (persona && persona.id) {
          idPersonaHidden.value = persona.id;

          // Mapeo flexible por si tus columnas usan mayúsculas o minúsculas
          const nombre = persona.primer_nombre || persona.Nombre || "";
          const apellido = persona.primer_apellido || persona.Apellido || "";
          nombrePersonaMatch.value = `${nombre} ${apellido}`.trim();

          // Habilitamos el botón para guardar el registro de entrenador
          btnSubmit.disabled = false;
          btnSubmit.classList.remove("opacity-50", "cursor-not-allowed");
          mostrarToast(
            "Persona localizada. Proceda a ingresar la especialidad.",
            "success",
          );
          return; // Terminamos con éxito
        }
      }

      // Si el controlador mandó un mensaje de error explícito, lo usamos; si no, usamos uno por defecto
      const mensajeError =
        res.message || "La persona no se encuentra registrada en el sistema.";
      mostrarToast(mensajeError, "error");

      // Limpieza de seguridad en caso de fallo
      idPersonaHidden.value = "";
      nombrePersonaMatch.value = "Ninguna persona vinculada aún";
      btnSubmit.disabled = true;
      btnSubmit.classList.add("opacity-50", "cursor-not-allowed");
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarToast(
        "Error al conectar con la pasarela para verificar la identidad.",
        "error",
      );
    });
});

// Evento 2: Interceptamos la creación del entrenador
form.addEventListener("submit", function (e) {
  e.preventDefault();

  if (idPersonaHidden.value === "") {
    mostrarToast(
      "Debe verificar una persona antes de procesar el registro.",
      "error",
    );
    return;
  }

  const formData = new FormData(this);

  fetch("help.php?action=crear_entrenador", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Fallo crítico al guardar.");
      return response.json();
    })
    .then((res) => {
      // EntrenadoresController utiliza la respuesta nativa -> status o success
      if (res.status === true || res.success === true) {
        mostrarToast(
          res.message || "Entrenador registrado exitosamente.",
          "success",
        );
        cerrarModal();
        setTimeout(() => location.reload(), 1200);
      } else {
        mostrarToast(
          res.message || res.error || "No se pudo asignar la especialidad.",
          "error",
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarToast(
        "Error de comunicación de datos con el controlador.",
        "error",
      );
    });
});
