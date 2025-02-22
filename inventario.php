<?php
$page_title = 'Inventario';
require_once('includes/load.php');
page_require_level(1);

$search = '';
$highlight = '';
  $low_stock_alert = false;
  $low_stock_products = [];
if (isset($_POST['search']) || isset($_GET['search']) || isset($_GET['highlight'])) {
    $search = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['search']) ? $_GET['search'] : '');
    $search = $search ? remove_junk($db->escape($search)) : '';
    $highlight = isset($_GET['highlight']) ? (int)$_GET['highlight'] : '';
    if ($search == '') {
        $products = join_product_table();
    } else {
        $products = search_product_table($search);
    }
} else {
    $products = join_product_table();
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
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
                            
                            <th> Precio </th>
                            <th> Proveedor </th>
                            <th> Categoría </th>
                            <th class="text-center" style="width: 150px;"> Acciones </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr id="product-<?php echo $product['id_producto']; ?>" <?php if ($product['id_producto'] == $highlight) echo 'class="highlight"'; ?>>
                                <td class="text-center"><?php echo count_id(); ?></td>
                                <td> <?php echo remove_junk($product['nombreProducto']); ?></td>
                                <td> <?php echo remove_junk($product['marca']); ?></td>
                                <td> <?php echo remove_junk($product['modelo']); ?></td>
                                <td> <?php echo remove_junk($product['descripcion']); ?></td>
                                <td class="<?php echo ($product['cantidad'] < 3) ? 'low-stock' : ''; ?>"> <?php echo remove_junk($product['cantidad']); ?></td>
                               
                                <td> <?php echo remove_junk($product['precio']); ?></td>
                                <td> <?php echo remove_junk($product['proveedor']); ?></td>
                                <td> <?php echo remove_junk($product['categorie']); ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="edit_product.php?id=<?php echo (int)$product['id_producto']; ?>" class="btn btn-info btn-xs" title="Editar" data-toggle="tooltip">
                                            <span class="glyphicon glyphicon-edit"></span>
                                        </a>
                                        <a href="add_stock.php?id=<?php echo (int)$product['id_producto']; ?>&search=<?php echo $search; ?>" class="btn btn-success btn-xs" title="Añadir Stock" data-toggle="tooltip">
                                            <span class="glyphicon glyphicon-plus"></span>
                                        </a>
                                        <?php if ($product['cantidad'] < 3): ?>
                                            <a href="solicitud_compra.php?id_producto=<?php echo (int)$product['id_producto']; ?>" class="btn btn-danger btn-xs" title="Solicitar Compra" data-toggle="tooltip">
                                                <span class="glyphicon glyphicon-shopping-cart"></span>
                                            </a>
                                        <?php endif; ?>
                                        <!-- Botón para ver detalles de garantías, órdenes y facturas -->
                                        <a href="detalles_producto.php?id=<?php echo (int)$product['id_producto']; ?>" class="btn btn-warning btn-xs" title="Ver Detalles" data-toggle="tooltip">
                                            <span class="glyphicon glyphicon-list-alt"></span>
                                        </a>
                                    </div>
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

<script>
  // Búsqueda en tiempo real
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

  // Resaltar producto si hay un highlight
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
    background-color: #ffcccc; /* Fondo rojo para productos con bajo stock */
    font-weight: bold; /* Texto en negrita */
  }
</style>