<?php
$page_title = 'Historial de Órdenes de Salida';
require_once('includes/load.php');
page_require_level(1);

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener órdenes de salida
$ordenes_salida = $db->query("
    SELECT os.*, d.nombre_departamento, p.nombreProducto 
    FROM orden_salida os
    JOIN departamento d ON os.id_departamento = d.id_departamento
    JOIN solicitud_compra sc ON os.id_solicitudCompra = sc.id_solicitudCompra
    JOIN producto p ON sc.id_producto = p.id_producto
");

if (!$ordenes_salida) {
    die("Error en la consulta: " . $db->error);
}

$ordenes_salida = $ordenes_salida->fetch_all(MYSQLI_ASSOC);

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
                            <th>Responsable</th>
                            <th>Fecha de Entrega</th>
                            <th>Archivo PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes_salida as $orden): ?>
                            <tr>
                                <td><?php echo $orden['id_orden_salida']; ?></td>
                                <td><?php echo $orden['nombreProducto']; ?></td>
                                <td><?php echo $orden['nombre_departamento']; ?></td>
                                <td><?php echo $orden['cantidad_entregada']; ?></td>
                                <td><?php echo $orden['responsable']; ?></td>
                                <td><?php echo $orden['fecha_entrega']; ?></td>
                                <td><a href="<?php echo $orden['archivo_pdf']; ?>" target="_blank">Ver PDF</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>