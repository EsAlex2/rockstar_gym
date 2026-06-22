<?php 

/**
 * Verifica si el usuario logueado tiene un permiso específico
 */
function tienePermiso(string $permiso): bool {
    // Si no hay sesión, no tiene permisos
    if (!isset($_SESSION['user_permissions'])) return false;
    
    // Si es ROOT, tiene acceso a todo (ajusta el nombre del rol según tu BD)
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ROOT') return true;
    
    // Verifica si el permiso existe en el array de la sesión
    return in_array($permiso, $_SESSION['user_permissions']);
}
