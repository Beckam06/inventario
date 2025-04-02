<?php
$page_title = 'Marcar Pedido como Recibido';
require_once('includes/load.php');
page_require_level(1);

if (!isset($_GET['id'])) {
    $session->msg('d', 'ID de solicitud no especificado.');
    redirect('lista_pedidos.php');
}

$id_solicitud = (int)$_GET['id'];

// Obtener detalles de la solicitud y productos asociados
$sql = "SELECT sc.*, p.nombreProducto, p.id_producto 
        FROM solicitud_compra sc
        LEFT JOIN producto p ON sc.id_producto = p.id_producto
        WHERE sc.id_solicitudCompra = {$id_solicitud}";
$solicitud = $db->query($sql)->fetch_assoc();

if (!$solicitud) {
    $session->msg('d', 'Solicitud no encontrada.');
    redirect('lista_pedidos.php');
}

// Procesar el formulario de recepción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->begin_transaction();

    try {
        $id_producto = $solicitud['id_producto'];
        $cantidad = $solicitud['cantidad_solicitada'];

        // Verificar si el producto ya tiene una cantidad inicial
        $producto = $db->query("SELECT cantidad FROM producto WHERE id_producto = {$id_producto}")->fetch_assoc();
        if ($producto['cantidad'] == 0) {
            // Si la cantidad es 0, actualizar con la cantidad recibida
            $db->query("UPDATE producto SET cantidad = {$cantidad}, visible = 1 WHERE id_producto = {$id_producto}");
        } else {
            // Si ya tiene una cantidad, no modificarla
            $db->query("UPDATE producto SET visible = 1 WHERE id_producto = {$id_producto}");
        }

        // Cambiar estado de la solicitud
        $db->query("UPDATE solicitud_compra SET id_estado = 2, fecha_recibido = NOW() WHERE id_solicitudCompra = {$id_solicitud}");

        $db->commit();
        $session->msg('s', 'Recepción procesada correctamente. La cantidad ha sido actualizada en el inventario y el producto está visible.');
        redirect('lista_pedidos.php');

    } catch (Exception $e) {
        $db->rollback();
        $session->msg('d', 'Error: ' . $e->getMessage());
        redirect('marcar_recibido.php?id=' . $id_solicitud);
    }
}

// Función para subir archivos
function upload_file($tmp_file, $folder) {
    $target_dir = "uploads/{$folder}/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Crear la carpeta si no existe
    }
    $file_name = uniqid() . '.pdf';
    $target_file = $target_dir . $file_name;
    if (!move_uploaded_file($tmp_file, $target_file)) {
        throw new Exception("Error al subir el archivo a {$folder}");
    }
    return $target_file;
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
                <strong>Marcar como Recibido - Solicitud #<?php echo $id_solicitud; ?></strong>
            </div>
            <div class="panel-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Producto:</label>
                        <input type="text" class="form-control" value="<?php echo $solicitud['nombreProducto']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Cantidad Solicitada:</label>
                        <input type="text" class="form-control" value="<?php echo $solicitud['cantidad_solicitada']; ?>" readonly>
                    </div>

                    <!-- Campos por cada producto -->
                    <?php for ($i = 0; $i < $solicitud['cantidad_solicitada']; $i++): ?>
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <strong>Producto #<?php echo ($i + 1); ?></strong>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Código de Unidad:</label>
                                    <input type="text" class="form-control" name="codigo_unidad[]" required>
                                </div>
                                <div class="form-group">
                                    <label>Descripción de la Garantía:</label>
                                    <input type="text" class="form-control" name="garantia[]" required>
                                </div>
                                <div class="form-group">
                                    <label>Fecha de Inicio de Garantía:</label>
                                    <input type="date" class="form-control" name="fecha_garantia[]" required>
                                </div>
                                <div class="form-group">
                                    <label>Archivo de Garantía (PDF):</label>
                                    <input type="file" class="form-control" name="archivo_garantia[]" accept="application/pdf" required onchange="showFileName(this)">
                                    <input type="text" class="form-control mt-2" name="nombre_archivo_garantia[]" readonly>
                                </div>
                                <script>
                                    function showFileName(input) {
                                        var fileName = input.files[0].name;
                                        var textInput = input.nextElementSibling;
                                        textInput.value = fileName;
                                    }
                                </script>
                                
                                <div class="form-group">
                                    <label>Número orden de compra</label>
                                    <input type="text" class="form-control" name="num_orden_compra[]" required>
                                </div>
                                <div class="form-group">
                                    <label>Orden de Compra (PDF):</label>
                                    <input type="file" class="form-control" name="archivo_orden[]" accept="application/pdf" required onchange="showFileName(this)">
                                    <input type="text" class="form-control mt-2" name="nombre_archivo_orden[]" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Factura (PDF):</label>
                                    <input type="file" class="form-control" name="archivo_factura[]" accept="application/pdf" required onchange="showFileName(this)">
                                    <input type="text" class="form-control mt-2" name="nombre_archivo_factura[]" readonly>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <button type="submit" name="procesar" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>