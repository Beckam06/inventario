<?php
  $page_title = 'Editar producto';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  if(isset($_GET['id'])){
    $product_id = (int)$_GET['id'];
    $product = find_product_with_category($product_id);
    $all_categories = find_all('categoria');
    if(!$product){
      $session->msg("d","ID de producto no encontrado.");
      redirect('product.php');
    }
  } else {
    $session->msg("d","Falta el ID del producto.");
    redirect('product.php');
  }

  if(isset($_POST['update_product'])){
    $req_fields = array('nombreProducto', 'marca', 'modelo', 'descripcion', 'garantia', 'precio', 'proveedor', 'id_categoria');
    validate_fields($req_fields);
    if(empty($errors)){
      $p_name  = remove_junk($db->escape($_POST['nombreProducto']));
      $p_brand = remove_junk($db->escape($_POST['marca']));
      $p_model = remove_junk($db->escape($_POST['modelo']));
      $p_desc  = remove_junk($db->escape($_POST['descripcion']));
      $p_warranty = remove_junk($db->escape($_POST['garantia']));
      $p_price = remove_junk($db->escape($_POST['precio']));
      $p_supplier = remove_junk($db->escape($_POST['proveedor']));
      $p_cat   = remove_junk($db->escape($_POST['id_categoria']));
      $query  = "UPDATE producto SET";
      $query .= " nombreProducto='{$p_name}', marca='{$p_brand}', modelo='{$p_model}', descripcion='{$p_desc}', garantia='{$p_warranty}', precio='{$p_price}', proveedor='{$p_supplier}', id_categoria='{$p_cat}'";
      $query .= " WHERE id_producto='{$product_id}'";
      if($db->query($query)){
        $session->msg('s',"Producto actualizado exitosamente.");
        redirect('product.php', false);
      } else {
        $session->msg('d',' Lo siento, actualización falló.');
        redirect('edit_product.php?id='.$product_id, false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('edit_product.php?id='.$product_id, false);
    }
  }
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-9">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Editar producto</span>
       </strong>
      </div>
      <div class="panel-body">
       <div class="col-md-12">
        <form method="post" action="edit_product.php?id=<?php echo (int)$product['id_producto'];?>" class="clearfix">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="nombreProducto" value="<?php echo remove_junk($product['nombreProducto']); ?>">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="marca" value="<?php echo remove_junk($product['marca']); ?>">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="modelo" value="<?php echo remove_junk($product['modelo']); ?>">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="descripcion" value="<?php echo remove_junk($product['descripcion']); ?>">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="garantia" value="<?php echo remove_junk($product['garantia']); ?>">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="precio" value="<?php echo remove_junk($product['precio']); ?>">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="proveedor" value="<?php echo remove_junk($product['proveedor']); ?>">
             </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-6">
                  <select class="form-control" name="id_categoria" required>
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($all_categories as $cat): ?>
                      <option value="<?php echo (int)$cat['id_categoria'] ?>" <?php if($product['id_categoria'] == $cat['id_categoria']): echo "selected"; endif; ?>>
                        <?php echo $cat['categoria'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <button type="submit" name="update_product" class="btn btn-danger">Actualizar producto</button>
        </form>
       </div>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
