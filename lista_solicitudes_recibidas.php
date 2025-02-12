<?php
$page_title = 'Solicitudes Recibidas';
require_once('includes/load.php');
page_require_level(1);

// Obtener solicitudes recibidas
$solicitudes_recibidas = $db->query("
    SELECT sc.*, d.nombre_departamento, p.nombreProducto 
    FROM solicitud_compra sc
    JOIN departamento d ON sc.id_departamento = d.id_departamento
    JOIN producto p ON sc.id_producto = p.id_producto
    WHERE sc.id_estado = 2 AND sc.cantidad_solicitada > 0
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
                <strong>Solicitudes Recibidas</strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Departamento</th>
                            <th>Cantidad Solicitada</th>
                            <th>Responsable</th>
                            <th>Fecha de Recepción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes_recibidas as $solicitud): ?>
                            <tr>
                                <td><?php echo $solicitud['id_solicitudCompra']; ?></td>
                                <td><?php echo $solicitud['nombreProducto']; ?></td>
                                <td><?php echo $solicitud['nombre_departamento']; ?></td>
                                <td><?php echo $solicitud['cantidad_solicitada']; ?></td>
                                <td><?php echo $solicitud['responsable']; ?></td>
                                <td><?php echo $solicitud['fecha_recibido']; ?></td>
                                <td>
                                    <a href="generar_orden_salida.php?id=<?php echo $solicitud['id_solicitudCompra']; ?>" class="btn btn-success btn-xs">Generar Orden de Salida</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>