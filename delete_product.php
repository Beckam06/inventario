<?php
require_once('includes/load.php');
page_require_level(1);

$id = (int)$_GET['id'];
if (!$id) {
    $session->msg("d", "ID de producto no válido.");
    redirect('product.php');
}

// Eliminar registros relacionados en solicitud_compra
$query = "DELETE FROM solicitud_compra WHERE id_producto = '{$id}'";
if ($db->query($query)) {
    // Luego eliminar el producto
    $delete_id = delete_by_id('producto', $id, 'id_producto');
    if ($delete_id) {
        $session->msg("s", "Producto eliminado.");
        redirect('product.php');
    } else {
        $session->msg("d", "Eliminación de producto falló.");
        redirect('product.php');
    }
} else {
    $session->msg("d", "Eliminación de registros relacionados falló.");
    redirect('product.php');
}
?>
