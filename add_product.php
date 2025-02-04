<?php
  $page_title = 'Agregar producto';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);
  $all_categories = find_all('categoria');
  $new_product = null;
?>
<?php
 if(isset($_POST['add_product'])){
   $req_fields = array('nombreProducto', 'marca', 'modelo', 'descripcion', 'cantidad', 'garantia', 'precio', 'proveedor', 'id_categoria');
   validate_fields($req_fields);
   $p_name  = remove_junk($db->escape($_POST['nombreProducto']));
   $p_brand = remove_junk($db->escape($_POST['marca']));
   $p_model = remove_junk($db->escape($_POST['modelo']));
   $p_desc  = remove_junk($db->escape($_POST['descripcion']));
   $p_quantity = remove_junk($db->escape($_POST['cantidad']));
   $p_warranty = remove_junk($db->escape($_POST['garantia']));
   $p_price = remove_junk($db->escape($_POST['precio']));
   $p_supplier = remove_junk($db->escape($_POST['proveedor']));
   $p_cat   = remove_junk($db->escape($_POST['id_categoria']));
   if(empty($errors)){
     $query  = "INSERT INTO producto (";
     $query .=" nombreProducto, marca, modelo, descripcion, cantidad, garantia, precio, proveedor, id_categoria";
     $query .=") VALUES (";
     $query .=" '{$p_name}', '{$p_brand}', '{$p_model}', '{$p_desc}', '{$p_quantity}', '{$p_warranty}', '{$p_price}', '{$p_supplier}', '{$p_cat}'";
     $query .=")";
     $query .=" ON DUPLICATE KEY UPDATE nombreProducto='{$p_name}'";
     
     if($db->query($query)){
       $session->msg('s',"Producto agregado exitosamente. ");
       $new_product_id = $db->insert_id();
       $new_product = find_product_with_category($new_product_id);
     } else {
       $session->msg('d',' Lo siento, registro falló.');
       redirect('add_product.php', false);
     }
   } else {
     $session->msg("d", $errors);
     redirect('add_product.php',false);
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
            <span>Agregar producto</span>
         </strong>
        </div>
        <div class="panel-body">
         <div class="col-md-12">
          <form method="post" action="add_product.php" class="clearfix">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="nombreProducto" placeholder="Nombre del producto">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="marca" placeholder="Marca">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="modelo" placeholder="Modelo">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="descripcion" placeholder="Descripción">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="number" class="form-control" name="cantidad" placeholder="Cantidad">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="garantia" placeholder="Garantía">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="number" step="0.01" class="form-control" name="precio" placeholder="Precio">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="proveedor" placeholder="Proveedor">
               </div>
              </div>
              <div class="form-group">
                <div class="row">
                  <div class="col-md-6">
                    <select class="form-control" name="id_categoria" required>
                      <option value="">Selecciona una categoría</option>
                    <?php  foreach ($all_categories as $cat): ?>
                      <option value="<?php echo (int)$cat['id_categoria'] ?>">
                        <?php echo $cat['categoria'] ?></option>
                    <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              <button type="submit" name="add_product" class="btn btn-danger">Agregar producto</button>
          </form>
         </div>
        </div>
      </div>
    </div>
  </div>
  <?php if ($new_product): ?>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span>Producto agregado</span>
         </strong>
        </div>
        <div class="panel-body">
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
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-center"><?php echo count_id();?></td>
                <td> <?php echo remove_junk($new_product['nombreProducto']); ?></td>
                <td> <?php echo remove_junk($new_product['marca']); ?></td>
                <td> <?php echo remove_junk($new_product['modelo']); ?></td>
                <td> <?php echo remove_junk($new_product['descripcion']); ?></td>
                <td> <?php echo remove_junk($new_product['cantidad']); ?></td>
                <td> <?php echo remove_junk($new_product['garantia']); ?></td>
                <td> <?php echo remove_junk($new_product['precio']); ?></td>
                <td> <?php echo remove_junk($new_product['proveedor']); ?></td>
                <td> <?php echo remove_junk($new_product['categoria']); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

<?php include_once('layouts/footer.php'); ?>
