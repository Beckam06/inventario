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
    // Verificar si el producto tiene solicitudes pendientes
    $pending_requests = $db->query("SELECT COUNT(*) AS total FROM solicitud_compra WHERE id_producto = '{$product_id}' AND id_estado = 1")->fetch_assoc();
    if ($pending_requests['total'] > 0) {
        $session->msg('d', 'No se puede editar la cantidad mientras existan solicitudes pendientes.');
        redirect('edit_product.php?id='.$product_id, false);
    }

    $req_fields = array('nombreProducto', 'marca', 'modelo', 'descripcion', 'precio', 'proveedor', 'id_categoria', 'id_cubiculo', 'cantidad', 'stock_minimo');
    validate_fields($req_fields);
    if(empty($errors)){
      $p_name  = remove_junk($db->escape($_POST['nombreProducto']));
      $p_brand = remove_junk($db->escape($_POST['marca']));
      $p_model = remove_junk($db->escape($_POST['modelo']));
      $p_desc  = remove_junk($db->escape($_POST['descripcion']));
      $p_price = remove_junk($db->escape($_POST['precio']));
      $p_supplier = remove_junk($db->escape($_POST['proveedor']));
      $p_cat   = remove_junk($db->escape($_POST['id_categoria']));
      $p_cub   = remove_junk($db->escape($_POST['id_cubiculo']));
      $p_qty   = (int)$_POST['cantidad'];
      $p_stock_min = (int)$_POST['stock_minimo'];
      
      $query  = "UPDATE producto SET";
      $query .= " nombreProducto='{$p_name}', marca='{$p_brand}', modelo='{$p_model}', descripcion='{$p_desc}', precio='{$p_price}', proveedor='{$p_supplier}', id_categoria='{$p_cat}', id_cubiculo='{$p_cub}', cantidad='{$p_qty}', stock_minimo='{$p_stock_min}'";
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
                <input type="text" class="form-control" name="nombreProducto" value="<?php echo remove_junk($product['nombreProducto']); ?>" required placeholder="Nombre del producto">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="marca" value="<?php echo remove_junk($product['marca']); ?>" required placeholder="Marca">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="modelo" value="<?php echo remove_junk($product['modelo']); ?>" required placeholder="Modelo">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="descripcion" value="<?php echo remove_junk($product['descripcion']); ?>" required placeholder="Descripción">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="precio" value="<?php echo remove_junk($product['precio']); ?>" required placeholder="Precio">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="text" class="form-control" name="proveedor" value="<?php echo remove_junk($product['proveedor']); ?>" required placeholder="Proveedor">  
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-th-large"></i>
                </span>
                <input type="number" class="form-control" name="cantidad" value="<?php echo (int)$product['cantidad']; ?>" min="0" required placeholder="Cantidad">
             </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">
                 <i class="glyphicon glyphicon-exclamation-sign"></i>
                </span>
                <input type="number" class="form-control" name="stock_minimo" value="<?php echo (int)$product['stock_minimo']; ?>" required placeholder="Stock Mínimo">
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-6">
                  <select class="form-control" name="id_categoria" id="id_categoria" required> 
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($all_categories as $cat): ?>
                      <option value="<?php echo (int)$cat['id_categoria'] ?>" <?php if($product['id_categoria'] == $cat['id_categoria']): echo "selected"; endif; ?>>
                        <?php echo $cat['categoria'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <select class="form-control" name="id_cubiculo" id="id_cubiculo" required>
                    <?php 
                      $cubiculos = find_by_sql("
                        SELECT cubiculos.* 
                        FROM cubiculos
                        INNER JOIN categoria_cubiculo ON cubiculos.id_cubiculo = categoria_cubiculo.id_cubiculo
                        WHERE categoria_cubiculo.id_categoria = '{$product['id_categoria']}'
                      ");
                      foreach ($cubiculos as $cub): ?>
                      <option value="<?php echo (int)$cub['id_cubiculo'] ?>" <?php if(isset($product['id_cubiculo']) && $product['id_cubiculo'] == $cub['id_cubiculo']): echo "selected"; endif; ?>>
                        <?php echo $cub['cubiculo'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
            </div>
            <div class="form-group">
              <br>
              <button type="submit" name="update_product" class="btn btn-danger">Actualizar producto</button>
              <a href="product.php" class="btn btn-warning" style="color: white; background-color: #f0ad4e; border-color: #eea236;">Regresar</a>
            </div>
        </form>
       </div>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
<script>
  document.getElementById('id_categoria').addEventListener('change', function() {
    var id_categoria = this.value;
    if (id_categoria) {
      fetch('get_cubiculos.php?id_categoria=' + id_categoria)
        .then(response => response.json())
        .then(data => {
          var cubiculosSelect = document.getElementById('id_cubiculo');
          cubiculosSelect.innerHTML = '<option value="">Selecciona un cubículo</option>';
          data.forEach(function(cubiculo) {
            var option = document.createElement('option');
            option.value = cubiculo.id_cubiculo;
            option.textContent = cubiculo.cubiculo;
            cubiculosSelect.appendChild(option);
          });
        });
    } else {
      document.getElementById('id_cubiculo').innerHTML = '<option value="">Selecciona un cubículo</option>';
    }
  });
</script>
