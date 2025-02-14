<?php
$page_title = 'Generar Orden de Salida';
require_once('includes/load.php');
require_once('includes/functions.php');
page_require_level(1);

if (!isset($_GET['id'])) {
    $session->msg('d', 'ID de solicitud no especificado.');
    redirect('lista_solicitudes_recibidas.php');
}

$id_solicitud = (int)$_GET['id'];

// Obtener detalles de la solicitud
$solicitud = $db->query("
    SELECT sc.*, p.nombreProducto, p.marca, p.modelo, d.nombre_departamento 
    FROM solicitud_compra sc
    JOIN producto p ON sc.id_producto = p.id_producto
    JOIN departamento d ON sc.id_departamento = d.id_departamento
    WHERE sc.id_solicitudCompra = {$id_solicitud}
")->fetch_assoc();

if (!$solicitud) {
    $session->msg('d', 'Solicitud no encontrada.');
    redirect('lista_solicitudes_recibidas.php');
}

// Obtener departamentos
$departamentos = $db->query("SELECT * FROM departamento")->fetch_all(MYSQLI_ASSOC);

// Procesar el formulario de generación de orden de salida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->begin_transaction();

    try {
        $id_departamento = (int)$_POST['id_departamento'];
        $responsable = $db->escape($_POST['responsable']);
        $cantidad_entregada = (int)$_POST['cantidad_entregada'];

        // Verificar si la cantidad entregada es válida
        if ($cantidad_entregada <= 0 || $cantidad_entregada > $solicitud['cantidad_solicitada']) {
            throw new Exception("Cantidad entregada no válida.");
        }

        // Insertar la orden de salida
        $sql_orden_salida = "INSERT INTO orden_salida (id_solicitudCompra, id_departamento, responsable, cantidad_entregada, archivo_pdf) 
                             VALUES ('{$id_solicitud}', '{$id_departamento}', '{$responsable}', '{$cantidad_entregada}', '')";
        $db->query($sql_orden_salida);
        $id_orden_salida = $db->insert_id();

        // Descontar del inventario
        $db->query("UPDATE producto SET cantidad = cantidad - {$cantidad_entregada} WHERE id_producto = {$solicitud['id_producto']}");

        // Actualizar la cantidad solicitada en la solicitud
        $nueva_cantidad = $solicitud['cantidad_solicitada'] - $cantidad_entregada;
        if ($nueva_cantidad > 0) {
            $db->query("UPDATE solicitud_compra SET cantidad_solicitada = {$nueva_cantidad} WHERE id_solicitudCompra = {$id_solicitud}");
        } else {
            // Si se entrega toda la cantidad, marcar la solicitud como completada
            $db->query("UPDATE solicitud_compra SET cantidad_solicitada = 0, id_estado = 3 WHERE id_solicitudCompra = {$id_solicitud}");
        }

        // Generar el PDF con la nota de entrega
        $pdf_path = generar_pdf_orden_salida(
            $id_orden_salida,
            $id_solicitud,
            $id_departamento,
            $responsable,
            $cantidad_entregada,
            $solicitud['nombreProducto'],
            $solicitud['marca'],
            $solicitud['modelo']
        );

        // Actualizar la orden de salida con la ruta del PDF
        $db->query("UPDATE orden_salida SET archivo_pdf = '{$pdf_path}' WHERE id_orden_salida = {$id_orden_salida}");

        $db->commit();
        $session->msg('s', 'Orden de salida generada correctamente.');

        // Redirigir al historial de órdenes de salida
        redirect('historial_ordenes_salida.php');

    } catch (Exception $e) {
        $db->rollback();
        $session->msg('d', 'Error: ' . $e->getMessage());
        redirect('generar_orden_salida.php?id=' . $id_solicitud);
    }
}

include_once('layouts/header.php');
?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Generar Orden de Salida - Solicitud #<?php echo $id_solicitud; ?></strong>
            </div>
            <div class="panel-body">
                <form method="post" action="generar_orden_salida.php?id=<?php echo $id_solicitud; ?>">
                    <!-- Producto (no editable) -->
                    <div class="form-group">
                        <label>Producto:</label>
                        <input type="text" class="form-control" value="<?php echo $solicitud['nombreProducto']; ?>" readonly>
                    </div>

                    <!-- Cantidad (editable, pero precargada con el total de la solicitud) -->
                    <div class="form-group">
                        <label>Cantidad:</label>
                        <input type="number" class="form-control" name="cantidad_entregada" min="1" max="<?php echo $solicitud['cantidad_solicitada']; ?>" value="<?php echo $solicitud['cantidad_solicitada']; ?>" required>
                    </div>

                    <!-- Departamento (editable) -->
                    <div class="form-group">
                        <label>Departamento:</label>
                        <select class="form-control" name="id_departamento" required>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?php echo $departamento['id_departamento']; ?>" <?php echo ($departamento['id_departamento'] == $solicitud['id_departamento']) ? 'selected' : ''; ?>>
                                    <?php echo $departamento['nombre_departamento']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Responsable (editable) -->
                    <div class="form-group">
                        <label>Responsable:</label>
                        <input type="text" class="form-control" name="responsable" value="<?php echo $solicitud['responsable']; ?>" required>
                    </div>

                    <!-- Botón para generar la orden -->
                    <button type="submit" name="generar_orden" class="btn btn-primary">Generar Orden de Salida</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>