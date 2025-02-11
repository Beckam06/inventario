<?php
require_once('includes/load.php');

if (isset($_GET['id'])) {
    $id_salida = (int)$_GET['id'];
    $query = "DELETE FROM salida_equipo WHERE id_salidaEquipo = '{$id_salida}'";
    $db->query($query);
    header("Location: reporte_salida.php");
} else {
    header("Location: reporte_salida.php");
}
?>
