<?php
require_once('includes/load.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = (int)$_POST['id_producto'];
    $id_departamento = (int)$_POST['id_departamento'];
    $responsable = $db->escape($_POST['responsable']);
    $id_encargado = (int)$_POST['id_encargado'];
    $cantidad_retirar = (int)$_POST['cantidad_retirar'];

    // Verificar si el encargado seleccionado existe
    $encargado_valido = $db->query("SELECT id FROM users WHERE id = {$id_encargado}")->num_rows > 0;
    if (!$encargado_valido) {
        $session->msg('d', 'El encargado seleccionado no es válido.');
        redirect('inventario.php');
    }

    // Verificar si hay suficiente stock
    $producto = $db->query("SELECT cantidad FROM producto WHERE id_producto = {$id_producto}")->fetch_assoc();
    if ($producto['cantidad'] < $cantidad_retirar) {
        $session->msg('d', 'No hay suficiente stock para realizar la salida.');
        redirect('inventario.php');
    }

    // Registrar la salida
    $fecha_entrega = make_date();
    $sql = "INSERT INTO orden_salida (id_producto, id_departamento, responsable, cantidad_entregada, fecha_entrega, id_encargado) 
            VALUES ('{$id_producto}', '{$id_departamento}', '{$responsable}', '{$cantidad_retirar}', '{$fecha_entrega}', '{$id_encargado}')";
    if ($db->query($sql)) {
        // Actualizar el stock
        $db->query("UPDATE producto SET cantidad = cantidad - {$cantidad_retirar} WHERE id_producto = {$id_producto}");
        $session->msg('s', 'Salida registrada correctamente.');
    } else {
        $session->msg('d', 'Error al registrar la salida.');
    }

    redirect('inventario.php');
}
?>
