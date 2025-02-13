<?php
  $page_title = 'Lista de productos';
  require_once('includes/load.php');

  // Checkin What level user has permission to view this page

  $search = '';
  if(isset($_POST['search'])){
    $search = remove_junk($db->escape($_POST['search']));
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
         <form action="product.php" method="post" class="form-inline pull-left">
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
              <tr>
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
                     <a href="delete_product.php?id=<?php echo (int)$product['id_producto'];?>" class="btn btn-danger btn-xs"  title="Eliminar" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-trash"></span>
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
        window.location.href = 'product.php';
      }
    });
  </script>
