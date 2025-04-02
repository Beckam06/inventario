<?php
require_once('includes/load.php');

if (isset($_GET['id_categoria'])) {
    $id_categoria = (int)$_GET['id_categoria'];
    $cubiculos = find_by_sql("
        SELECT cubiculos.* 
        FROM cubiculos
        INNER JOIN categoria_cubiculo ON cubiculos.id_cubiculo = categoria_cubiculo.id_cubiculo
        WHERE categoria_cubiculo.id_categoria = {$id_categoria}
    ");
    echo json_encode($cubiculos);
}
?>
