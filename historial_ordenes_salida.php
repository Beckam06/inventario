<?php
$page_title = 'Historial de Órdenes de Salida';
require_once('includes/load.php');
page_require_level(1);

// Obtener órdenes de salida con detalles de unidades entregadas
$ordenes_salida = $db->query("
    SELECT os.*, d.nombre_departamento, p.nombreProducto, GROUP_CONCAT(pc.codigo_unidad) AS unidades_entregadas
    FROM orden_salida os
    JOIN departamento d ON os.id_departamento = d.id_departamento
    JOIN solicitud_compra sc ON os.id_solicitudCompra = sc.id_solicitudCompra
    JOIN producto p ON sc.id_producto = p.id_producto
    LEFT JOIN detalle_orden_salida dos ON os.id_orden_salida = dos.id_orden_salida
    LEFT JOIN producto_codigo pc ON dos.id_producto_codigo = pc.id
    GROUP BY os.id_orden_salida
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
                <strong>Historial de Órdenes de Salida</strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Departamento</th>
                            <th>Cantidad Entregada</th>
                            <th>Unidades Entregadas</th>
                            <th>Responsable</th>
                            <th>Fecha de Entrega</th>
                            <th>Archivo PDF</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes_salida as $orden): ?>
                            <tr>
                                <td><?php echo $orden['id_orden_salida']; ?></td>
                                <td><?php echo $orden['nombreProducto']; ?></td>
                                <td><?php echo $orden['nombre_departamento']; ?></td>
                                <td><?php echo $orden['cantidad_entregada']; ?></td>
                                <td><?php echo $orden['unidades_entregadas']; ?></td>
                                <td><?php echo $orden['responsable']; ?></td>
                                <td><?php echo $orden['fecha_entrega']; ?></td>
                                <td><a href="<?php echo $orden['archivo_pdf']; ?>" target="_blank">Ver PDF</a></td>
                                <td><a href="detalle_orden_salida.php?id=<?php echo $orden['id_orden_salida']; ?>" class="btn btn-info btn-xs">Ver Detalles</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>