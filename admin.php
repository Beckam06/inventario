<?php
$page_title = 'Inventario';
require_once('includes/load.php');
page_require_level(2); // Asegúrate de que solo usuarios con permiso puedan ver esta página

// Obtener productos con stock bajo
$productos_bajo_stock = find_by_sql("
    SELECT p.*, s.cantidad 
    FROM producto p
    INNER JOIN stock s ON p.id_producto = s.id_producto
    WHERE s.cantidad <= 10  -- Cambia 10 por el stock mínimo deseado
");

// Obtener todos los productos para la lista
$all_productos = find_all('producto');
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<!-- Alertas de Stock Bajo -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-alert"></span>
                    <span>Alertas de Stock Bajo</span>
                </strong>
            </div>
            <div class="panel-body">
                <?php if (!empty($productos_bajo_stock)): ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock Actual</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_bajo_stock as $producto): ?>
                                <tr>
                                    <td><?php echo remove_junk($producto['nombreProducto']); ?></td>
                                    <td><?php echo $producto['cantidad']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#modalSolicitud<?php echo $producto['id_producto']; ?>">
                                            <span class="glyphicon glyphicon-shopping-cart"></span> Solicitar Compra
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal para Solicitud de Compra -->
                                <div class="modal fade" id="modalSolicitud<?php echo $producto['id_producto']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalSolicitudLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="modalSolicitudLabel">Solicitar Compra de <?php echo remove_junk($producto['nombreProducto']); ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <form method="post" action="procesar_solicitud.php">
                                                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                                    <div class="form-group">
                                                        <label>Producto:</label>
                                                        <input type="text" class="form-control" value="<?php echo remove_junk($producto['nombreProducto']); ?>" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Cantidad a Solicitar:</label>
                                                        <input type="number" class="form-control" name="cantidad_solicitada" min="1" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Departamento:</label>
                                                        <select class="form-control" name="id_departamento" required>
                                                            <?php
                                                            $departamentos = find_all('departamento');
                                                            foreach ($departamentos as $departamento): ?>
                                                                <option value="<?php echo $departamento['id_departamento']; ?>">
                                                                    <?php echo $departamento['nombre_departamento']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-success">No hay productos con stock bajo.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Productos -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Lista de Productos</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_productos as $producto): ?>
                            <tr>
                                <td><?php echo count_id(); ?></td>
                                <td><?php echo remove_junk($producto['nombreProducto']); ?></td>
                                <td><?php echo $producto['cantidad']; ?></td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#modalSolicitud<?php echo $producto['id_producto']; ?>">
                                        <span class="glyphicon glyphicon-shopping-cart"></span> Solicitar Compra
                                    </button>
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