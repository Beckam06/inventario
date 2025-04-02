<?php
require_once('includes/load.php');

// Incluir el autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Función para generar la nota de entrega
function generar_nota_entrega_reporte($id_salida, $salida) {
    $carpeta_notas = __DIR__ . '/notas_entrega';

    // Verificar si la carpeta existe, si no, crearla
    if (!is_dir($carpeta_notas)) {
        mkdir($carpeta_notas, 0777, true); // Crear la carpeta con permisos de escritura
    }

    // Crear el PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema de Inventario');
    $pdf->SetTitle('Nota de Entrega #' . $id_salida);
    $pdf->SetSubject('Nota de Entrega');
    $pdf->SetKeywords('Nota, Entrega, Inventario');

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Contenido del PDF
    $html = '<table width="100%">
    <tr>
        <td width="20%">
            <img src="C:\wamp64\www\inventario\imagenes\img_azucarera.png" width="100" />
        </td>
        <td width="60%">
            <hr style="border: 2px solid green;">
        </td>
        <td width="20%" style="text-align: right;">
            <p><strong>' . date('d/m/Y') . '</strong></p>
        </td>
    </tr>
</table>';

    $html .= '<h1 style="text-align: center; font-size: 14px; font-weight: bold;">NOTA DE ENTREGA</h1>';

    $html .= '<p>Por medio del presente se hace la entrega de:<br>
<strong>' . $salida['nombreProducto'] . ' Marca: ' . $salida['marca'] . ' Modelo: ' . $salida['modelo'] . '</strong><br>
<strong>Descripción:</strong> ' . $salida['descripcion'] . '<br>
</p>';
    $html .= '<p><strong>Cantidad Entregada:</strong> ' . $salida['cantidad_entregada'] . '</p>';

    $html .= '<p>Este equipo estará asignado al empleado <strong>' . $salida['responsable'] . '</strong>, 
' . $salida['nombre_departamento'] . ', comprometiéndose a su uso estrictamente laboral.</p>';

    $html .= '<p><strong>Nota de Responsabilidad:</strong> El responsable <strong>' . $salida['responsable'] . '</strong> se hace cargo de los productos entregados.</p>';

    // Sección de firmas
    $html .= '<br>';
    $html .= '<table width="100%">
    <tr>
        <td width="50%" style="text-align: left;"><strong>RECIBE:</strong><br><br><br>' . $salida['responsable'] . '</td>
        <td width="50%" style="text-align: left;"><strong>ENTREGA:</strong><br><br><br>' . $salida['encargado_salida']. '</td>
    </tr>
  </table>';

    // Sección CC
    $html .= '<br><br>';
    $html .= '<p><strong>CC:</strong></p>';
    $html .= '<p style="color: black;"><strong>Gcia. Admon.</strong></p>';
    $html .= '<p style="color: black;"><strong>Gcia. de Gestión y Talento Humano</strong></p>';
    $html .= '<p style="color: black;"><strong>Aud. Interna</strong></p>';
    $html .= '<p><strong>Archivo</strong></p>';

    $file_path = $carpeta_notas . '/reporte_salida_' . $id_salida . '.pdf';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($file_path, 'F'); // Guardar el archivo en el servidor
    return $file_path;
}

if (isset($_GET['id'])) {
    $id_salida = (int)$_GET['id'];
    $query = "SELECT s.*, 
                     p.nombreProducto, 
                     p.marca, 
                     p.modelo, 
                     p.descripcion, 
                     p.cantidad AS cantidad_producto, 
                     p.precio, 
                     p.proveedor, 
                     c.categoria AS categoria, 
                     d.nombre_departamento, 
                     u.name AS encargado_salida 
              FROM orden_salida s 
              JOIN producto p ON s.id_producto = p.id_producto 
              JOIN departamento d ON s.id_departamento = d.id_departamento 
              JOIN categoria c ON p.id_categoria = c.id_categoria
              JOIN users u ON s.id_encargado = u.id
              WHERE s.id_orden_salida = '{$id_salida}'";
    $result = $db->query($query);
    $salida = $db->fetch_assoc($result);

    if (isset($_GET['action']) && $_GET['action'] == 'pdf') {
        // Crear el PDF
        ob_clean(); // Limpiar el búfer de salida
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $html = '<h1>Reporte de Salida de Producto</h1>';
        $html .= '<table border="1" cellpadding="4">';
        $html .= '<tr><th>Campo</th><th>Datos</th></tr>';
        $html .= '<tr><td>Departamento</td><td>' . $salida['nombre_departamento'] . '</td></tr>';
        $html .= '<tr><td>Responsable</td><td>' . $salida['responsable'] . '</td></tr>';
        $html .= '<tr><td>Fecha-Hora Salida Equipo</td><td>' . $salida['fecha_entrega'] . '</td></tr>';
        $html .= '<tr><td>Nombre del Producto</td><td>' . $salida['nombreProducto'] . '</td></tr>';
        $html .= '<tr><td>Marca</td><td>' . $salida['marca'] . '</td></tr>';
        $html .= '<tr><td>Modelo</td><td>' . $salida['modelo'] . '</td></tr>';
        $html .= '<tr><td>Descripción</td><td>' . $salida['descripcion'] . '</td></tr>';
        $html .= '<tr><td>Cantidad</td><td>' . $salida['cantidad_entregada'] . '</td></tr>';
        $html .= '<tr><td>Precio</td><td>' . $salida['precio'] . '</td></tr>';
        $html .= '<tr><td>Proveedor</td><td>' . $salida['proveedor'] . '</td></tr>';
        $html .= '<tr><td>Categoría</td><td>' . $salida['categoria'] . '</td></tr>';
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('reporte_salida.pdf', 'I'); 
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] == 'nota') {
        if ($salida) {
            $nota_path = generar_nota_entrega_reporte($id_salida, $salida);
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($nota_path) . '"');
            readfile($nota_path);
            exit;
        } else {
            $session->msg('d', 'No se encontró el registro de salida.');
            redirect('reporte_salida.php');
        }
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
                    <td>Fecha y Hora Salida Equipo</td>
                    <td><?php echo $salida['fecha_entrega']; ?></td>
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
                    <td><?php echo $salida['cantidad_entregada']; ?></td>
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
            <a href="reporte_salida.php?id=<?php echo $id_salida; ?>&action=nota" target="_blank" class="btn btn-danger" title="Ver PDF NOTA">
              <span class="glyphicon glyphicon-file"></span> Ver PDF NOTA
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
                    <td><?php echo $salida['cantidad_entregada']; ?></td>
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
    $query = "SELECT s.id_orden_salida, s.fecha_entrega, s.responsable, s.cantidad_entregada, p.nombreProducto, d.nombre_departamento 
              FROM orden_salida s 
              JOIN producto p ON s.id_producto = p.id_producto 
              JOIN departamento d ON s.id_departamento = d.id_departamento
              WHERE s.id_solicitudCompra IS NULL
              ORDER BY s.fecha_entrega DESC";
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
                    <th>Fecha-Hora Salida Equipo</th>
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
                    <td><?php echo $salida['fecha_entrega']; ?></td>
                    <td><?php echo $salida['nombreProducto']; ?></td>
                    <td><?php echo $salida['nombre_departamento']; ?></td>
                    <td><?php echo $salida['responsable']; ?></td>
                    <td><?php echo $salida['cantidad_entregada']; ?></td>
                    <td class="text-center">
                      <?php if (isset($salida['id_orden_salida'])): ?>
                      <a href="reporte_salida.php?id=<?php echo (int)$salida['id_orden_salida']; ?>" class="btn btn-info btn-xs" title="Ver Reporte">
                        <span class="glyphicon glyphicon-eye-open"></span>
                      </a>
                      <a href="editar_salida.php?id=<?php echo (int)$salida['id_orden_salida']; ?>" class="btn btn-warning btn-xs" title="Editar">
                        <span class="glyphicon glyphicon-pencil"></span>
                      </a>
                      <a href="eliminar_salida.php?id=<?php echo (int)$salida['id_orden_salida']; ?>" class="btn btn-danger btn-xs" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?');">
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
