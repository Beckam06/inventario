<?php
$page_title = 'Solicitud de Compra';
require_once('includes/load.php');
page_require_level(1);

// Obtener el ID del producto desde la URL
$id_producto = isset($_GET['id_producto']) ? (int)$_GET['id_producto'] : 0;

// Obtener el producto
$producto = find_by_id('producto', $id_producto, 'id_producto');
if (!$producto) {
    $session->msg("d", "Producto no encontrado.");
    redirect('inventario.php', false);
}

// Obtener todos los departamentos
$departamentos = find_all('departamento');

// Procesar el formulario de solicitud de compra
if (isset($_POST['crear_solicitud'])) {
    $cantidad_solicitada = (int)$_POST['cantidad_solicitada'];
    $id_departamento = (int)$_POST['id_departamento'];
    $responsable = remove_junk($db->escape($_POST['responsable'])); // Responsable se digita manualmente
    $id_estado = 1; // Estado 1 = Pendiente
    $fecha_solicitud = date('Y-m-d H:i:s'); // Capturar la fecha y hora del sistema

    $sql = "INSERT INTO solicitud_compra (id_producto, cantidad_solicitada, id_departamento, responsable, id_estado, fecha_solicitud) 
            VALUES ({$id_producto}, {$cantidad_solicitada}, {$id_departamento}, '{$responsable}', {$id_estado}, '{$fecha_solicitud}')";
    
    if ($db->query($sql)) {
        $session->msg("s", "Solicitud de compra creada exitosamente.");
        redirect('lista_pedidos.php', false);
    } else {
        $session->msg("d", "Error al crear la solicitud de compra.");
        redirect('solicitud_compra.php?id_producto=' . $id_producto, false);
    }
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-shopping-cart"></span>
                    <span>Solicitud de Compra</span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="solicitud_compra.php?id_producto=<?php echo $id_producto; ?>">
                    <div class="form-group">
                        <label>Producto:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['nombreProducto']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Marca:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['marca']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Modelo:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['modelo']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Descripción:</label>
                        <textarea class="form-control" readonly><?php echo remove_junk($producto['descripcion']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Cantidad en Inventario:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['cantidad']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Garantía:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['garantia']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Precio:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['precio']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Proveedor:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['proveedor']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Categoría:</label>
                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['id_categoria']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Cantidad a Solicitar:</label>
                        <input type="number" class="form-control" name="cantidad_solicitada" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Departamento:</label>
                        <select class="form-control" name="id_departamento" required>
                            <option value="">Seleccione un departamento</option>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?php echo $departamento['id_departamento']; ?>">
                                    <?php echo $departamento['nombre_departamento']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Responsable:</label>
                        <input type="text" class="form-control" name="responsable" required>
                    </div>
                    <button type="submit" name="crear_solicitud" class="btn btn-primary">Crear Solicitud</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>