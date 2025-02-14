<?php
require_once('includes/load.php');

// Incluir el autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

if (isset($_GET['id'])) {
    $id_salida = (int)$_GET['id'];
    $query = "SELECT s.*, p.nombreProducto, p.marca, p.modelo, p.descripcion, p.cantidad AS cantidad_producto, p.precio, p.proveedor, c.categoria AS categoria, d.nombre_departamento 
              FROM salida_equipo s 
              JOIN producto p ON s.id_producto = p.id_producto 
              JOIN departamento d ON s.id_departamento = d.id_departamento 
              JOIN categoria c ON p.id_categoria = c.id_categoria
              WHERE s.id_salidaEquipo = '{$id_salida}'";
    $result = $db->query($query);
    $salida = $db->fetch_assoc($result);

    if (isset($_GET['action']) && $_GET['action'] == 'pdf') {
        // Crear el PDF
        ob_end_clean(); // Limpiar el búfer de salida
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $html = '<h1>Reporte de Salida de Producto</h1>';
        $html .= '<table border="1" cellpadding="4">';
        $html .= '<tr><th>Campo</th><th>Datos</th></tr>';
        $html .= '<tr><td>Departamento</td><td>' . $salida['nombre_departamento'] . '</td></tr>';
        $html .= '<tr><td>Responsable</td><td>' . $salida['responsable'] . '</td></tr>';
        $html .= '<tr><td>Orden de Entrega</td><td>' . $salida['ordenEntrega'] . '</td></tr>';
        $html .= '<tr><td>Fecha de Salida</td><td>' . $salida['fechaSalida'] . '</td></tr>';
        $html .= '<tr><td>Hora de Salida</td><td>' . $salida['horaSalida'] . '</td></tr>';
        $html .= '<tr><td>Nombre del Producto</td><td>' . $salida['nombreProducto'] . '</td></tr>';
        $html .= '<tr><td>Marca</td><td>' . $salida['marca'] . '</td></tr>';
        $html .= '<tr><td>Modelo</td><td>' . $salida['modelo'] . '</td></tr>';
        $html .= '<tr><td>Descripción</td><td>' . $salida['descripcion'] . '</td></tr>';
        $html .= '<tr><td>Cantidad</td><td>' . $salida['cantidad_salida'] . '</td></tr>';
        $html .= '<tr><td>Precio</td><td>' . $salida['precio'] . '</td></tr>';
        $html .= '<tr><td>Proveedor</td><td>' . $salida['proveedor'] . '</td></tr>';
        $html .= '<tr><td>Categoría</td><td>' . $salida['categoria'] . '</td></tr>';
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('reporte_salida.pdf', 'D');
        exit;
    }

    include_once('layouts/header.php');
    ?>

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <strong>
              <span class="glyphicon glyphicon-th"></span>
              <span>Reporte de Salida de Producto</span>
           </strong>
          </div>
          <div class="panel-body">
            <?php if ($salida): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-striped text-center">
                <thead>
                  <tr class="info">
                    <th>Campo</th>
                    <th>Datos</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Departamento</td>
                    <td><?php echo $salida['nombre_departamento']; ?></td>
                  </tr>
                  <tr>
                    <td>Responsable</td>
                    <td><?php echo $salida['responsable']; ?></td>
                  </tr>
                  <tr>
                    <td>Orden de Entrega</td>
                    <td><?php echo $salida['ordenEntrega']; ?></td>
                  </tr>
                  <tr>
                    <td>Fecha de Salida</td>
                    <td><?php echo $salida['fechaSalida']; ?></td>
                  </tr>
                  <tr>
                    <td>Hora de Salida</td>
                    <td><?php echo $salida['horaSalida']; ?></td>
                  </tr>
                  <tr>
                    <td>Nombre del Producto</td>
                    <td><?php echo $salida['nombreProducto']; ?></td>
                  </tr>
                  <tr>
                    <td>Marca</td>
                    <td><?php echo $salida['marca']; ?></td>
                  </tr>
                  <tr>
                    <td>Modelo</td>
                    <td><?php echo $salida['modelo']; ?></td>
                  </tr>
                  <tr>
                    <td>Descripción</td>
                    <td><?php echo $salida['descripcion']; ?></td>
                  </tr>
                  <tr>
                    <td>Cantidad</td>
                    <td><?php echo $salida['cantidad_salida']; ?></td>
                  </tr>
                  <tr>
                    <td>Precio</td>
                    <td><?php echo $salida['precio']; ?></td>
                  </tr>
                  <tr>
                    <td>Proveedor</td>
                    <td><?php echo $salida['proveedor']; ?></td>
                  </tr>
                  <tr>
                    <td>Categoría</td>
                    <td><?php echo $salida['categoria']; ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <a href="reporte_salida.php" class="btn btn-primary">Regresar</a>
            <a href="reporte_salida.php?id=<?php echo $id_salida; ?>&action=pdf" class="btn btn-danger" title="Exportar a PDF">
              <span class="glyphicon glyphicon-file"></span> Exportar a PDF
            </a>
            <?php else: ?>
            <div class="alert alert-danger">
              <strong>Error:</strong> No se encontró el registro de salida.
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="detalleModal" tabindex="-1" role="dialog" aria-labelledby="detalleModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="detalleModalLabel">Detalle del Producto</h4>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped text-center">
                <thead>
                  <tr class="info">
                    <th>Campo</th>
                    <th>Valor</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Nombre del Producto</td>
                    <td><?php echo $salida['nombreProducto']; ?></td>
                  </tr>
                  <tr>
                    <td>Marca</td>
                    <td><?php echo $salida['marca']; ?></td>
                  </tr>
                  <tr>
                    <td>Modelo</td>
                    <td><?php echo $salida['modelo']; ?></td>
                  </tr>
                  <tr>
                    <td>Descripción</td>
                    <td><?php echo $salida['descripcion']; ?></td>
                  </tr>
                  <tr>
                    <td>Cantidad Salida</td>
                    <td><?php echo $salida['cantidad_salida']; ?></td>
                  </tr>
                  <tr>
                    <td>Precio</td>
                    <td><?php echo $salida['precio']; ?></td>
                  </tr>
                  <tr>
                    <td>Proveedor</td>
                    <td><?php echo $salida['proveedor']; ?></td>
                  </tr>
                  <tr>
                    <td>Categoría</td>
                    <td><?php echo $salida['categoria']; ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {
        $('#detalleModal').modal('show');
      });
    </script>

    <?php include_once('layouts/footer.php'); ?>
    <?php
} else {
    $query = "SELECT s.id_salidaEquipo, s.fechaSalida, s.horaSalida, s.responsable, s.cantidad_salida, p.nombreProducto, d.nombre_departamento 
              FROM salida_equipo s 
              JOIN producto p ON s.id_producto = p.id_producto 
              JOIN departamento d ON s.id_departamento = d.id_departamento
              ORDER BY s.fechaSalida DESC, s.horaSalida DESC";
    $result = $db->query($query);

    include_once('layouts/header.php');
    ?>

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <strong>
              <span class="glyphicon glyphicon-th"></span>
              <span>Reportes de Salida</span>
           </strong>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped text-center">
                <thead>
                  <tr class="info">
                    <th>Fecha de Salida</th>
                    <th>Hora de Salida</th>
                    <th>Producto</th>
                    <th>Departamento</th>
                    <th>Responsable</th>
                    <th>Cantidad</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($salida = $db->fetch_assoc($result)): ?>
                  <tr>
                    <td><?php echo $salida['fechaSalida']; ?></td>
                    <td><?php echo $salida['horaSalida']; ?></td>
                    <td><?php echo $salida['nombreProducto']; ?></td>
                    <td><?php echo $salida['nombre_departamento']; ?></td>
                    <td><?php echo $salida['responsable']; ?></td>
                    <td><?php echo $salida['cantidad_salida']; ?></td>
                    <td class="text-center">
                      <?php if (isset($salida['id_salidaEquipo'])): ?>
                      <a href="reporte_salida.php?id=<?php echo (int)$salida['id_salidaEquipo']; ?>" class="btn btn-info btn-xs" title="Ver Reporte">
                        <span class="glyphicon glyphicon-eye-open"></span>
                      </a>
                      <a href="editar_salida.php?id=<?php echo (int)$salida['id_salidaEquipo']; ?>" class="btn btn-warning btn-xs" title="Editar">
                        <span class="glyphicon glyphicon-pencil"></span>
                      </a>
                      <a href="eliminar_salida.php?id=<?php echo (int)$salida['id_salidaEquipo']; ?>" class="btn btn-danger btn-xs" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?');">
                        <span class="glyphicon glyphicon-trash"></span>
                      </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include_once('layouts/footer.php'); ?>
    <?php
}
?>
