<?php
require_once('includes/load.php');

if (isset($_POST['marcar_recibido'])) {
    $id_solicitudCompra = (int)$_POST['id_solicitudCompra'];
    $garantia = remove_junk($db->escape($_POST['garantia']));
    $fecha_garantia = remove_junk($db->escape($_POST['fecha_garantia']));

    // Obtener la solicitud de compra
    $solicitud = find_by_id('solicitud_compra', $id_solicitudCompra, 'id_solicitudCompra');
    if (!$solicitud) {
        $session->msg("d", "Solicitud de compra no encontrada.");
        redirect('lista_pedidos.php', false);
    }

    // Obtener el producto
    $id_producto = $solicitud['id_producto'];
    $producto = find_by_id('producto', $id_producto, 'id_producto');
    if (!$producto) {
        $session->msg("d", "Producto no encontrado.");
        redirect('lista_pedidos.php', false);
    }

    // Calcular la fecha de fin de la garantía (por ejemplo, 1 año después)
    $fecha_fin_garantia = date('Y-m-d', strtotime($fecha_garantia . ' + 1 year'));

    // Insertar la garantía en la tabla `garantia`
    $sql = "INSERT INTO garantia (id_producto, garantia, fecha_garantia, fecha_fin_garantia) 
            VALUES ({$id_producto}, '{$garantia}', '{$fecha_garantia}', '{$fecha_fin_garantia}')";
    if ($db->query($sql)) {
        $session->msg("s", "Garantía registrada exitosamente.");
    } else {
        $session->msg("d", "Error al registrar la garantía.");
        redirect('lista_pedidos.php', false);
    }

    // Cambiar el estado de la solicitud a "Recibido"
    $fecha_recibido = date('Y-m-d H:i:s');
    $sql = "UPDATE solicitud_compra SET id_estado = 2, fecha_recibido = '{$fecha_recibido}' WHERE id_solicitudCompra = {$id_solicitudCompra}";
    if ($db->query($sql)) {
        $session->msg("s", "Pedido marcado como recibido exitosamente.");
    } else {
        $session->msg("d", "Error al marcar el pedido como recibido.");
    }
    redirect('lista_pedidos.php', false);
}
?>