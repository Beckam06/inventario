<?php
  $page_title = 'Agregar producto';
  require_once('includes/load.php');
  // Verificar el nivel de permiso del usuario
  page_require_level(1);
  $all_categories = find_all('categoria');
  $all_departments = find_all('departamento');
?>
<?php
 if(isset($_POST['add_product'])){
   $req_fields = array('nombreProducto', 'marca', 'modelo', 'descripcion', 'cantidad', 'precio', 'proveedor', 'id_categoria', 'id_cubiculo', 'stock_minimo');
   validate_fields($req_fields);
   if(empty($errors)){
     $p_date  = make_date();
     $p_name  = remove_junk($db->escape($_POST['nombreProducto']));
     $p_brand = remove_junk($db->escape($_POST['marca']));
     $p_model = remove_junk($db->escape($_POST['modelo']));
     $p_desc  = remove_junk($db->escape($_POST['descripcion']));
     $p_quantity = (int)$_POST['cantidad']; // Reflejar la cantidad inicial en el inventario
     $p_price = (float)$_POST['precio'];
     $p_supplier = remove_junk($db->escape($_POST['proveedor']));
     $p_cat   = (int)$_POST['id_categoria'];
     $p_cub   = (int)$_POST['id_cubiculo'];
     $p_stock_min = (int)$_POST['stock_minimo'];
     $requires_request = isset($_POST['requiere_solicitud']) && $_POST['requiere_solicitud'] === '1';

     $db->begin_transaction(); // Iniciar transacción

     // Guardar el producto en la tabla producto
     $query  = "INSERT INTO producto (";
     $query .= "fechaIngreso, nombreProducto, marca, modelo, descripcion, cantidad, precio, proveedor, id_categoria, id_cubiculo, stock_minimo, visible";
     $query .= ") VALUES (";
     $query .= "'{$p_date}', '{$p_name}', '{$p_brand}', '{$p_model}', '{$p_desc}', '{$p_quantity}', '{$p_price}', '{$p_supplier}', '{$p_cat}', '{$p_cub}', '{$p_stock_min}', 1";
     $query .= ")";

     if ($db->query($query)) {
         $product_id = $db->insert_id(); // Obtener el ID del producto recién insertado

         // Validar si se requiere solicitud de compra
         if ($requires_request) {
             // Generar solicitud de compra automáticamente
             $p_responsible = remove_junk($db->escape($_POST['responsable']));
             $p_department = (int)$_POST['id_departamento'];
             $requested_quantity = (int)$_POST['cantidad'];

             $request_query  = "INSERT INTO solicitud_compra (";
             $request_query .= "id_producto, cantidad_solicitada, id_departamento, responsable, id_estado, fecha_solicitud";
             $request_query .= ") VALUES (";
             $request_query .= "'{$product_id}', '{$requested_quantity}', '{$p_department}', '{$p_responsible}', 1, '{$p_date}')";

             if (!$db->query($request_query)) {
                 $db->rollback(); // Revertir cambios si falla la solicitud
                 $session->msg('d', 'Error al generar la solicitud de compra.');
                 redirect('add_product.php', false);
             }

             // Marcar el producto como no visible en el inventario
             $db->query("UPDATE producto SET visible = 0 WHERE id_producto = '{$product_id}'");
         }

         $db->commit(); // Confirmar transacción
         $session->msg('s', "Producto agregado exitosamente.");
         redirect('inventario.php', false); // Redirige al inventario directamente
     } else {
         $db->rollback(); // Revertir cambios si falla la inserción del producto
         $session->msg('d', 'Error al agregar el producto.');
         redirect('add_product.php', false);
     }
   } else {
     $session->msg("d", "Complete todos los campos para el producto.");
     redirect('add_product.php', false);
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
                  <input type="text" class="form-control" name="nombreProducto" required placeholder="Nombre del producto">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="marca" required placeholder="Marca">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="modelo" required placeholder="Modelo">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="descripcion" required placeholder="Descripción">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="number" class="form-control" name="cantidad" required placeholder="Cantidad">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="number" step="0.01" class="form-control" name="precio" required placeholder="Precio">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="proveedor" required placeholder="Proveedor">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-exclamation-sign"></i>
                  </span>
                  <input type="number" class="form-control" name="stock_minimo" required placeholder="Stock Mínimo">
               </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-exclamation-sign"></i>
                  </span>
                  <label class="checkbox-inline">
                    <input type="checkbox" name="requiere_solicitud" value="1" id="requiere_solicitud"> Requiere solicitud de compra
                  </label>
               </div>
              </div>
              <div class="form-group" id="responsable-departamento" style="display: none;">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-user"></i>
                  </span>
                  <input type="text" class="form-control" name="responsable" placeholder="Responsable">
                </div>
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-briefcase"></i>
                  </span>
                  <select class="form-control" name="id_departamento">
                    <option value="">Selecciona un departamento</option>
                    <?php foreach ($all_departments as $department): ?>
                      <option value="<?php echo (int)$department['id_departamento']; ?>">
                        <?php echo $department['nombre_departamento']; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <div class="row">
                  <div class="col-md-6">
                    <select class="form-control" name="id_categoria" id="id_categoria" required>
                      <option value="">Selecciona una categoría</option>
                      <?php foreach ($all_categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id_categoria']; ?>">
                          <?php echo $cat['categoria']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <select class="form-control" name="id_cubiculo" id="id_cubiculo" required>
                      <option value="">Selecciona un cubículo</option>
                    </select>
                  </div>
                </div>
              </div>
              <button type="submit" name="add_product" class="btn btn-danger">Agregar producto</button>
              <a href="product.php" class="btn btn-warning" style="color: white; background-color: #f0ad4e; border-color: #eea236;">Regresar</a>
          </form>
         </div>
        </div>
      </div>
    </div>
</div>
<script>
  document.getElementById('requiere_solicitud').addEventListener('change', function() {
    const responsableDepartamento = document.getElementById('responsable-departamento');
    if (this.checked) {
      responsableDepartamento.style.display = 'block';
    } else {
      responsableDepartamento.style.display = 'none';
    }
  });

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
<?php include_once('layouts/footer.php'); ?>
