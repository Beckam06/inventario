<?php
$page_title = 'Detalles del Producto';
require_once('includes/load.php');
page_require_level(1);

if (!isset($_GET['id'])) {
    $session->msg('d', 'ID de producto no especificado.');
    redirect('inventario.php');
}

$id_producto = (int)$_GET['id'];

// Obtener detalles del producto
$producto = find_by_id('producto', $id_producto, 'id_producto');
if (!$producto) {
    $session->msg('d', 'Producto no encontrado.');
    redirect('inventario.php');
}

// Obtener códigos de unidad asociados al producto
$codigos_unidad = $db->query("SELECT * FROM producto_codigo WHERE id_producto = {$id_producto}");

// Organizar los registros por unidad
$unidades = [];

foreach ($codigos_unidad as $codigo) {
    $id_producto_codigo = $codigo['id'];

    // Obtener garantía asociada al código de unidad
    $garantia = $db->query("SELECT * FROM garantia WHERE id_producto_codigo = {$id_producto_codigo}")->fetch_assoc();

    // Obtener orden de compra asociada al código de unidad
    $orden_compra = $db->query("SELECT * FROM orden_compra WHERE id_producto_codigo = {$id_producto_codigo}")->fetch_assoc();

    // Obtener factura asociada al código de unidad
    $factura = $db->query("SELECT * FROM factura WHERE id_producto_codigo = {$id_producto_codigo}")->fetch_assoc();

    // Agregar al array de unidades
    $unidades[] = [
        'codigo_unidad' => $codigo['codigo_unidad'],
        'garantia' => $garantia,
        'orden_compra' => $orden_compra,
        'factura' => $factura
    ];
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
                <strong>Detalles del Producto: <?php echo $producto['nombreProducto']; ?></strong>
            </div>
            <div class="panel-body">
                <?php if (!empty($unidades)): ?>
                    <?php foreach ($unidades as $unidad): ?>
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <strong>Unidad: <?php echo $unidad['codigo_unidad']; ?></strong>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Fecha de Inicio</th>
                                            <th>Fecha de Fin</th>
                                            <th>Archivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Mostrar Garantía -->
                                        <?php if ($unidad['garantia']): ?>
                                            <tr>
                                                <td>Garantía</td>
                                                <td><?php echo $unidad['garantia']['garantia']; ?></td>
                                                <td><?php echo $unidad['garantia']['fecha_garantia']; ?></td>
                                                <td><?php echo $unidad['garantia']['fecha_fin_garantia']; ?></td>
                                                <td><a href="<?php echo $unidad['garantia']['archivo_pdf']; ?>" target="_blank">Ver PDF</a></td>
                                            </tr>
                                        <?php endif; ?>

                                        <!-- Mostrar Orden de Compra -->
                                        <?php if ($unidad['orden_compra']): ?>
                                            <tr>
                                                <td>Orden de Compra</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td><a href="<?php echo $unidad['orden_compra']['archivo_pdf']; ?>" target="_blank">Ver PDF</a></td>
                                            </tr>
                                        <?php endif; ?>

                                        <!-- Mostrar Factura -->
                                        <?php if ($unidad['factura']): ?>
                                            <tr>
                                                <td>Factura</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td><a href="<?php echo $unidad['factura']['archivo_pdf']; ?>" target="_blank">Ver PDF</a></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No hay unidades registradas para este producto.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>