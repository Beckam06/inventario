<?php
require_once('includes/load.php');

$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;

// Construir la consulta SQL con los filtros
$sql = "SELECT nombreProducto, cantidad 
        FROM producto 
        WHERE visible = 1";

if ($categoria) {
    $sql .= " AND id_categoria = {$categoria}";
}

$sql .= " ORDER BY cantidad DESC";

$productos = $db->query($sql)->fetch_all(MYSQLI_ASSOC);

// Devolver los resultados como JSON
header('Content-Type: application/json');
echo json_encode($productos);
?>
