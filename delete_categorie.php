
<?php
require_once('includes/load.php');
// Verificar el nivel de permiso del usuario
page_require_level(1);

// Verificar si el ID de la categoría está presente y es válido
if (isset($_GET['id_categoria']) && is_numeric($_GET['id_categoria'])) {
    $categoria_id = (int)$_GET['id_categoria'];
    $categoria = find_by_id('categoria', $categoria_id, 'id_categoria'); // Buscar la categoría
    if (!$categoria) {
        $session->msg("d", "ID de la categoría no encontrado.");
        redirect('categorie.php');
    }
} else {
    $session->msg("d", "ID de la categoría no válido.");
    redirect('categorie.php');
}

// Eliminar la categoría
$delete_id = delete_by_id('categoria', $categoria['id_categoria'], 'id_categoria'); // Eliminar por ID
if ($delete_id) {
    $session->msg("s", "Categoría eliminada correctamente.");
    redirect('categorie.php');
} else {
    $session->msg("d", "Eliminación falló.");
    redirect('categorie.php');
}
?>