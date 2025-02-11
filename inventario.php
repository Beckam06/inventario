<?php
  $page_title = 'Inventario';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  // Establecer la zona horaria
  date_default_timezone_set('America/Tegucigalpa'); // Ajusta esto a tu zona horaria

  $search = '';
  $highlight = '';
  $low_stock_alert = false;
  $low_stock_products = [];
  if(isset($_POST['search']) || isset($_GET['search']) || isset($_GET['highlight'])){
    $search = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['search']) ? $_GET['search'] : '');
    $search = $search ? remove_junk($db->escape($search)) : '';
    $highlight = isset($_GET['highlight']) ? (int)$_GET['highlight'] : '';
    if($search == ''){
      $products = join_product_table();
    } else {
      $products = search_product_table($search);
    }
  } else {
    $products = join_product_table();
  }

  // Verificar si hay productos con stock bajo
  foreach ($products as $product) {
    if ($product['cantidad'] <= $product['stock_minimo']) {
      $low_stock_alert = true;
      $low_stock_products[] = $product['nombreProducto'];
    }
  }

  if(isset($_POST['salida'])) {
    $id_producto = (int)$_POST['id_producto'];
    $id_departamento = (int)$_POST['id_departamento'];
    $responsable = remove_junk($db->escape($_POST['responsable']));
    $ordenEntrega = remove_junk($db->escape($_POST['ordenEntrega']));
    $cantidad_salida = (int)$_POST['cantidad_salida'];
    $fechaSalida = date('Y-m-d');
    $horaSalida = date('H:i:s'); // Obtener la hora actual de la computadora

    // Verificar si el id_departamento existe
    $result = $db->query("SELECT id_departamento FROM departamento WHERE id_departamento = '{$id_departamento}'");
    if($db->num_rows($result) > 0) {
      // Verificar si hay suficiente stock
      $result = $db->query("SELECT cantidad, stock_minimo FROM producto WHERE id_producto = '{$id_producto}'");
      $producto = $db->fetch_assoc($result);
      if($producto['cantidad'] >= $cantidad_salida) {
        $query  = "INSERT INTO salida_equipo (id_departamento, responsable, ordenEntrega, id_producto, fechaSalida, horaSalida, cantidad_salida) ";
        $query .= "VALUES ('{$id_departamento}', '{$responsable}', '{$ordenEntrega}', '{$id_producto}', '{$fechaSalida}', '{$horaSalida}', '{$cantidad_salida}')";
        if($db->query($query)){
          $query  = "UPDATE producto SET cantidad = cantidad - '{$cantidad_salida}' WHERE id_producto = '{$id_producto}'";
          $db->query($query);
          $session->msg('s',"Producto retirado exitosamente.");
          redirect('inventario.php?highlight=' . $id_producto, false);
        } else {
          $session->msg('d',' Lo siento, registro de salida falló.');
          redirect('inventario.php', false);
        }
      } else {
        $session->msg('d',' No hay suficiente stock para retirar.');
        redirect('inventario.php', false);
      }
    } else {
      $session->msg('d',' El departamento no existe.');
      redirect('inventario.php', false);
    }
  }
?>

<?php include_once('layouts/header.php'); ?>
  <div class="row">
     <div class="col-md-12">
       <?php echo display_msg($msg); ?>
       <?php if ($low_stock_alert): ?>
       <div class="alert alert-danger">
         <strong>¡Atención!</strong> Los siguientes productos tienen un stock bajo: <?php echo implode(', ', $low_stock_products); ?>.
       </div>
       <?php endif; ?>
     </div>
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
         <div class="pull-right">
           <a href="add_product.php" class="btn btn-primary">Agregar producto</a>
         </div>
         <form action="inventario.php" method="post" class="form-inline pull-left">
           <div class="form-group">
             <input type="text" class="form-control" id="search" name="search" placeholder="Buscar por nombre" value="<?php echo $search; ?>">
           </div>
           <button type="submit" class="btn btn-default">Buscar</button>
         </form>
        </div>
        <div class="panel-body" id="product-table">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th> Nombre del Producto </th>
                <th> Marca </th>
                <th> Modelo </th>
                <th> Descripción </th>
                <th> Cantidad </th>
                <th> Garantía </th>
                <th> Precio </th>
                <th> Proveedor </th>
                <th> Categoría </th>
                <th> Fecha Ingreso </th>
                <th class="text-center" style="width: 100px;"> Acciones </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product):?>
              <tr id="product-<?php echo $product['id_producto']; ?>" <?php if($product['id_producto'] == $highlight) echo 'class="highlight"'; if($product['cantidad'] <= $product['stock_minimo']) echo ' class="low-stock"'; ?>>
                <td class="text-center"><?php echo count_id();?></td>
                <td> <?php echo remove_junk($product['nombreProducto']); ?></td>
                <td> <?php echo remove_junk($product['marca']); ?></td>
                <td> <?php echo remove_junk($product['modelo']); ?></td>
                <td> <?php echo remove_junk($product['descripcion']); ?></td>
                <td> <?php echo remove_junk($product['cantidad']); ?></td>
                <td> <?php echo remove_junk($product['garantia']); ?></td>
                <td> <?php echo remove_junk($product['precio']); ?></td>
                <td> <?php echo remove_junk($product['proveedor']); ?></td>
                <td> <?php echo remove_junk($product['categorie']); ?></td>
                <td> <?php echo remove_junk($product['fechaIngreso']); ?></td>

                <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_product.php?id=<?php echo (int)$product['id_producto'];?>" class="btn btn-info btn-xs"  title="Editar" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <a href="add_stock.php?id=<?php echo (int)$product['id_producto'];?>&search=<?php echo $search; ?>" class="btn btn-success btn-xs"  title="Añadir Stock" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-plus"></span>
                    </a>
                    <a href="#" class="btn btn-danger btn-xs" title="Salida" data-toggle="modal" data-target="#salidaModal-<?php echo (int)$product['id_producto']; ?>">
                      <span class="glyphicon glyphicon-minus"></span>
                    </a>
                  </div>
                </td>
              </tr>
              <!-- Modal -->
              <div class="modal fade" id="salidaModal-<?php echo (int)$product['id_producto']; ?>" tabindex="-1" role="dialog" aria-labelledby="salidaModalLabel-<?php echo (int)$product['id_producto']; ?>">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <form method="post" action="inventario.php">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="salidaModalLabel-<?php echo (int)$product['id_producto']; ?>">Salida de Producto</h4>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="id_producto" value="<?php echo (int)$product['id_producto']; ?>">
                        <div class="form-group">
                          <label for="id_departamento">Departamento</label>
                          <select class="form-control" name="id_departamento" required>
                            <?php
                              $departamentos = $db->query("SELECT id_departamento, nombre_departamento FROM departamento");
                              while($departamento = $db->fetch_assoc($departamentos)) {
                                echo "<option value='{$departamento['id_departamento']}'>{$departamento['nombre_departamento']}</option>";
                              }
                            ?>
                          </select>
                        </div>
                        <div class="form-group">
                          <label for="responsable">Responsable</label>
                          <input type="text" class="form-control" name="responsable" required>
                        </div>
                        <div class="form-group">
                          <label for="ordenEntrega">Orden de Entrega</label>
                          <input type="text" class="form-control" name="ordenEntrega" required>
                        </div>
                        <div class="form-group">
                          <label for="cantidad_salida">Cantidad a Retirar</label>
                          <input type="number" class="form-control" name="cantidad_salida" min="1" max="<?php echo (int)$product['cantidad']; ?>" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" name="salida" class="btn btn-primary">Registrar Salida</button>
                      </div>
                    </form>
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
<script>
  document.getElementById('search').addEventListener('input', function() {
    if (this.value.length >= 3) {
      fetch('search_product.php?query=' + this.value)
        .then(response => response.text())
        .then(data => {
          document.getElementById('product-table').innerHTML = data;
          // Resaltar productos con stock bajo
          document.querySelectorAll('#product-table tr').forEach(function(row) {
            var cantidad = parseInt(row.querySelector('td:nth-child(6)').innerText);
            var stock_minimo = parseInt(row.querySelector('td:nth-child(12)').innerText);
            if (cantidad <= stock_minimo) {
              row.classList.add('low-stock');
            }
          });
        });
    } else if (this.value === '') {
      window.location.href = 'inventario.php';
    }
  });

  window.onload = function() {
    var highlight = "<?php echo $highlight; ?>";
    if (highlight) {
      var element = document.getElementById('product-' + highlight);
      if (element) {
        element.classList.add('highlight');
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Eliminar el resaltado y el parámetro de la URL después de 3 segundos
        setTimeout(function() {
          element.classList.remove('highlight');
          window.history.replaceState(null, null, window.location.pathname);
        }, 3000);
      }
    }
  };
</script>
<style>
  .highlight {
    background-color: #ffff99;
  }
  .low-stock {
    background-color: #f2dede;
  }
</style>
