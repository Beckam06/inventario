<?php
$page_title = 'Detalles de la Orden de Salida';
require_once('includes/load.php');
page_require_level(1);

if (!isset($_GET['id'])) {
    $session->msg('d', 'ID de orden de salida no especificado.');
    redirect('historial_ordenes_salida.php');
}

$id_orden_salida = (int)$_GET['id'];

// Obtener detalles de la orden de salida
$orden_salida = $db->query("
    SELECT os.*, d.nombre_departamento, p.nombreProducto, sc.responsable AS solicitante
    FROM orden_salida os
    JOIN departamento d ON os.id_departamento = d.id_departamento
    JOIN solicitud_compra sc ON os.id_solicitudCompra = sc.id_solicitudCompra
    JOIN producto p ON sc.id_producto = p.id_producto
    WHERE os.id_orden_salida = {$id_orden_salida}
")->fetch_assoc();

if (!$orden_salida) {
    $session->msg('d', 'Orden de salida no encontrada.');
    redirect('historial_ordenes_salida.php');
}

// Obtener las unidades entregadas en esta orden
$unidades_entregadas = $db->query("
    SELECT 
        pc.codigo_unidad, 
        g.garantia, 
        g.fecha_garantia AS fecha_inicio_garantia, 
        g.fecha_fin_garantia, 
        g.archivo_pdf AS garantia_pdf, 
        f.archivo_pdf AS factura_pdf, 
        oc.archivo_pdf AS orden_compra_pdf
    FROM detalle_orden_salida dos
    JOIN producto_codigo pc ON dos.id_producto_codigo = pc.id
    LEFT JOIN garantia g ON pc.id = g.id_producto_codigo
    LEFT JOIN factura f ON pc.id = f.id_producto_codigo
    LEFT JOIN orden_compra oc ON pc.id = oc.id_producto_codigo
    WHERE dos.id_orden_salida = {$id_orden_salida}
")->fetch_all(MYSQLI_ASSOC);

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
                <strong>Detalles de la Orden de Salida #<?php echo $id_orden_salida; ?></strong>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Información de la Orden</h4>
                        <p><strong>Producto:</strong> <?php echo $orden_salida['nombreProducto']; ?></p>
                        <p><strong>Departamento:</strong> <?php echo $orden_salida['nombre_departamento']; ?></p>
                        <p><strong>Cantidad Entregada:</strong> <?php echo $orden_salida['cantidad_entregada']; ?></p>
                        <p><strong>Responsable:</strong> <?php echo $orden_salida['responsable']; ?></p>
                        <p><strong>Fecha de Entrega:</strong> <?php echo $orden_salida['fecha_entrega']; ?></p>
                        <p><strong>Archivo PDF:</strong> 
                            <?php if (!empty($orden_salida['archivo_pdf'])): ?>
                                <a href="<?php echo $orden_salida['archivo_pdf']; ?>" target="_blank">Ver PDF</a>
                            <?php else: ?>
                                No disponible
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <hr>

                <h4>Unidades Entregadas</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Código de Unidad</th>
                            <th>Garantía</th>
                            <th>Fecha Inicio Garantía</th>
                            <th>Fecha Fin Garantía</th>
                            <th>Archivo Garantía</th>
                            <th>Factura</th>
                            <th>Orden de Compra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($unidades_entregadas)): ?>
                            <?php foreach ($unidades_entregadas as $unidad): ?>
                                <tr>
                                    <td><?php echo $unidad['codigo_unidad']; ?></td>
                                    <td><?php echo $unidad['garantia'] ?: 'N/A'; ?></td>
                                    <td><?php echo $unidad['fecha_inicio_garantia'] ?: 'N/A'; ?></td>
                                    <td><?php echo $unidad['fecha_fin_garantia'] ?: 'N/A'; ?></td>
                                    <td>
                                        <?php if ($unidad['garantia_pdf']): ?>
                                            <a href="<?php echo $unidad['garantia_pdf']; ?>" target="_blank">Ver Garantía</a>
                                        <?php else: ?>
                                            Sin archivo
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($unidad['factura_pdf']): ?>
                                            <a href="<?php echo $unidad['factura_pdf']; ?>" target="_blank">Ver Factura</a>
                                        <?php else: ?>
                                            Sin factura
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($unidad['orden_compra_pdf']): ?>
                                            <a href="<?php echo $unidad['orden_compra_pdf']; ?>" target="_blank">Ver Orden</a>
                                        <?php else: ?>
                                            Sin orden
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No se encontraron unidades entregadas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>