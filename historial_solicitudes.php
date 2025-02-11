<?php
$page_title = 'Historial de Solicitudes de Compra';
require_once('includes/load.php');
page_require_level(1);

// Obtener todas las solicitudes de compra con JOIN a las tablas relacionadas
$sql = "SELECT sc.*, e.estado, d.nombre_departamento, p.* 
        FROM solicitud_compra sc
        JOIN estado e ON sc.id_estado = e.id_estado
        JOIN departamento d ON sc.id_departamento = d.id_departamento
        JOIN producto p ON sc.id_producto = p.id_producto
        ORDER BY sc.fecha_solicitud DESC";
$solicitudes = $db->query($sql);
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
                    <span class="glyphicon glyphicon-list"></span>
                    <span>Historial de Solicitudes de Compra</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad Solicitada</th>
                            <th>Departamento</th>
                            <th>Responsable</th>
                            <th>Fecha y Hora de Solicitud</th>
                            <th>Fecha y Hora de Recepción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td><?php echo remove_junk($solicitud['nombreProducto']); ?></td>
                                <td><?php echo remove_junk($solicitud['cantidad_solicitada']); ?></td>
                                <td><?php echo remove_junk($solicitud['nombre_departamento']); ?></td>
                                <td><?php echo remove_junk($solicitud['responsable']); ?></td>
                                <td><?php echo remove_junk($solicitud['fecha_solicitud']); ?></td>
                                <td><?php echo remove_junk($solicitud['fecha_recibido'] ?? 'No recibido'); ?></td>
                                <td><?php echo remove_junk($solicitud['estado']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modalProducto<?php echo $solicitud['id_producto']; ?>">
                                        <span class="glyphicon glyphicon-eye-open"></span> Ver Producto
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal para ampliar la información del producto -->
                            <div class="modal fade" id="modalProducto<?php echo $solicitud['id_producto']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalProductoLabel<?php echo $solicitud['id_producto']; ?>">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <h4 class="modal-title" id="modalProductoLabel<?php echo $solicitud['id_producto']; ?>">Detalles del Producto</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Nombre:</strong> <?php echo remove_junk($solicitud['nombreProducto']); ?></p>
                                                    <p><strong>Marca:</strong> <?php echo remove_junk($solicitud['marca']); ?></p>
                                                    <p><strong>Modelo:</strong> <?php echo remove_junk($solicitud['modelo']); ?></p>
                                                    <p><strong>Descripción:</strong> <?php echo remove_junk($solicitud['descripcion']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Cantidad en Inventario:</strong> <?php echo remove_junk($solicitud['cantidad']); ?></p>
                                                    <p><strong>Garantía:</strong> <?php echo remove_junk($solicitud['garantia']); ?></p>
                                                    <p><strong>Precio:</strong> <?php echo remove_junk($solicitud['precio']); ?></p>
                                                    <p><strong>Proveedor:</strong> <?php echo remove_junk($solicitud['proveedor']); ?></p>
                                                    <p><strong>Categoría:</strong> <?php echo remove_junk($solicitud['id_categoria']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>