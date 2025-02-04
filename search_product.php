<?php
  require_once('includes/load.php');
  $query = isset($_GET['query']) ? remove_junk($db->escape($_GET['query'])) : '';
  if($query != ''){
    $products = search_product_table($query);
  } else {
    $products = join_product_table();
  }
?>
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
          <a href="add_stock.php?id=<?php echo (int)$product['id_producto'];?>&search=<?php echo $query; ?>" class="btn btn-success btn-xs"  title="Añadir Stock" data-toggle="tooltip">
            <span class="glyphicon glyphicon-plus"></span>
          </a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
