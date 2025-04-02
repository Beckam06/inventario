<?php
$page_title = 'Solicitar Compra';
require_once('includes/load.php');
page_require_level(1);

if (!isset($_GET['id_producto'])) {
    $session->msg('d', 'ID de producto no especificado.');
    redirect('inventario.php');
}

$id_producto = (int)$_GET['id_producto'];

// Obtener detalles del producto
$sql = "SELECT * FROM producto WHERE id_producto = {$id_producto}";
$producto = $db->query($sql)->fetch_assoc();

if (!$producto) {
    $session->msg('d', 'Producto no encontrado.');
    redirect('inventario.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad_solicitada = (int)$_POST['cantidad_solicitada'];
    $id_departamento = (int)$_POST['id_departamento'];
    $responsable = remove_junk($db->escape($_POST['responsable']));
    $fecha_solicitud = make_date();

    if ($cantidad_solicitada <= 0) {
        $session->msg('d', 'La cantidad solicitada debe ser mayor a 0.');
        redirect('solicitar_compra.php?id_producto=' . $id_producto);
    }

    $sql = "INSERT INTO solicitud_compra (id_producto, cantidad_solicitada, id_departamento, responsable, id_estado, fecha_solicitud) 
            VALUES ('{$id_producto}', '{$cantidad_solicitada}', '{$id_departamento}', '{$responsable}', 1, '{$fecha_solicitud}')";
    if ($db->query($sql)) {
        $session->msg('s', 'Solicitud de compra creada exitosamente.');
        redirect('lista_pedidos.php');
    } else {
        $session->msg('d', 'Error al crear la solicitud de compra.');
        redirect('solicitar_compra.php?id_producto=' . $id_producto);
    }
}

$departamentos = find_all('departamento');
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
                <strong>Solicitar Compra - Producto: <?php echo $producto['nombreProducto']; ?></strong>
            </div>
            <div class="panel-body">
                <form method="post" action="solicitar_compra.php?id_producto=<?php echo $id_producto; ?>">
                    <div class="form-group">
                        <label>Producto:</label>
                        <input type="text" class="form-control" value="<?php echo $producto['nombreProducto']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Cantidad Solicitada:</label>
                        <input type="number" class="form-control" name="cantidad_solicitada" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Departamento:</label>
                        <select class="form-control" name="id_departamento" required>
                            <option value="">Selecciona un departamento</option>
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
                    <button type="submit" class="btn btn-primary">Crear Solicitud</button>
                    <a href="inventario.php" class="btn btn-default">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>
