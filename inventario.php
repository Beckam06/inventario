<?php
  $page_title = 'Inventario';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  $search = '';
  $highlight = '';
  if(isset($_POST['search']) || isset($_GET['search'])){
    $search = isset($_POST['search']) ? remove_junk($db->escape($_POST['search'])) : remove_junk($db->escape($_GET['search']));
    $highlight = isset($_GET['highlight']) ? (int)$_GET['highlight'] : '';
    if($search == ''){
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
                <th> Garantía </th>
                <th> Precio </th>
                <th> Proveedor </th>
                <th> Categoría </th>
                <th class="text-center" style="width: 100px;"> Acciones </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product):?>
              <tr id="product-<?php echo $product['id_producto']; ?>" <?php if($product['id_producto'] == $highlight) echo 'class="highlight"'; ?>>
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
                <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_product.php?id=<?php echo (int)$product['id_producto'];?>" class="btn btn-info btn-xs"  title="Editar" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <a href="add_stock.php?id=<?php echo (int)$product['id_producto'];?>&search=<?php echo $search; ?>" class="btn btn-success btn-xs"  title="Añadir Stock" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-plus"></span>
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
  document.getElementById('search').addEventListener('input', function() {
    if (this.value.length >= 3) {
      fetch('search_product.php?query=' + this.value)
        .then(response => response.text())
        .then(data => {
          document.getElementById('product-table').innerHTML = data;
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
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  };
</script>
<style>
  .highlight {
    background-color: #ffff99;
  }
</style>
