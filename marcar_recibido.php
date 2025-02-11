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
        JOIN producto p ON sc.id_producto = p.id_producto
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

        // Validar que se hayan subido archivos para cada producto
        for ($i = 0; $i < $cantidad; $i++) {
            if (
                empty($_POST['codigo_unidad'][$i]) || 
                empty($_POST['garantia'][$i]) || 
                empty($_POST['fecha_garantia'][$i]) ||
                empty($_FILES['archivo_garantia']['tmp_name'][$i]) ||
                empty($_FILES['archivo_orden']['tmp_name'][$i]) ||
                empty($_FILES['archivo_factura']['tmp_name'][$i])
            ) {
                throw new Exception("Complete todos los campos para el producto #" . ($i + 1));
            }

            // Obtener el código de unidad ingresado manualmente
            $codigo_unidad = $_POST['codigo_unidad'][$i];

            // Insertar el código de unidad en la tabla producto_codigo
            $sql_codigo = "INSERT INTO producto_codigo (id_producto, codigo_unidad) 
                           VALUES ('{$id_producto}', '{$db->escape($codigo_unidad)}')";
            $db->query($sql_codigo);
            $id_producto_codigo = $db->insert_id(); // Obtener el ID generado

            // Subir archivos
            $garantia_path = upload_file($_FILES['archivo_garantia']['tmp_name'][$i], 'garantias');
            $orden_path = upload_file($_FILES['archivo_orden']['tmp_name'][$i], 'ordenes');
            $factura_path = upload_file($_FILES['archivo_factura']['tmp_name'][$i], 'facturas');

            // Insertar garantía
            $fecha_fin = date('Y-m-d', strtotime($_POST['fecha_garantia'][$i] . ' +1 year'));
            $sql_garantia = "INSERT INTO garantia (id_producto_codigo, garantia, fecha_garantia, fecha_fin_garantia, archivo_pdf) 
                             VALUES ('{$id_producto_codigo}', '{$db->escape($_POST['garantia'][$i])}', 
                                     '{$db->escape($_POST['fecha_garantia'][$i])}', '{$fecha_fin}', '{$garantia_path}')";
            $db->query($sql_garantia);

            // Insertar orden de compra
            $sql_orden = "INSERT INTO orden_compra (id_solicitudCompra, id_producto_codigo, archivo_pdf) 
                          VALUES ('{$id_solicitud}', '{$id_producto_codigo}', '{$orden_path}')";
            $db->query($sql_orden);

            // Insertar factura
            $db->query("INSERT INTO factura (id_producto_codigo, archivo_pdf) VALUES ('{$id_producto_codigo}', '{$factura_path}')");
        }

        // Actualizar inventario
        $db->query("UPDATE producto SET cantidad = cantidad + {$cantidad} WHERE id_producto = {$id_producto}");

        // Cambiar estado de la solicitud
        $db->query("UPDATE solicitud_compra SET id_estado = 2, fecha_recibido = NOW() WHERE id_solicitudCompra = {$id_solicitud}");

        $db->commit();
        $session->msg('s', 'Recepción procesada correctamente.');
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
                                    <input type="file" class="form-control" name="archivo_garantia[]" accept="application/pdf" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Número orden de compra</label>
                                    <input type="text" class="form-control" name="num_orden_compra[]" required>
                                </div>
                                <div class="form-group">
                                    <label>Orden de Compra (PDF):</label>
                                    <input type="file" class="form-control" name="archivo_orden[]" accept="application/pdf" required>
                                </div>
                                <div class="form-group">
                                    <label>Factura (PDF):</label>
                                    <input type="file" class="form-control" name="archivo_factura[]" accept="application/pdf" required>
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