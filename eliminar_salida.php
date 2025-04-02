<?php
require_once('includes/load.php');

page_require_level(1);

if (isset($_GET['id'])) {
    $id_salida = (int)$_GET['id'];
    $query = "DELETE FROM orden_salida WHERE id_orden_salida = '{$id_salida}'";
    $db->query($query);
    header("Location: reporte_salida.php");
} else {
    header("Location: reporte_salida.php");
}
?>
