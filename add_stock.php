<?php
  $page_title = 'Añadir Stock';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  if(isset($_POST['add_stock'])){
    $req_fields = array('cantidad');
    validate_fields($req_fields);
    $p_id = (int)$_GET['id'];
    $p_quantity = remove_junk($db->escape($_POST['cantidad']));
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    if(empty($errors)){
      $sql = "UPDATE producto SET cantidad=cantidad + '{$p_quantity}' WHERE id_producto='{$p_id}'";
      if($db->query($sql)){
        $session->msg('s',"Stock añadido exitosamente.");
        redirect('inventario.php?search='.$search.'&highlight='.$p_id, false);
      } else {
        $session->msg('d',' Lo siento, actualización falló.');
        redirect('add_stock.php?id='.$p_id.'&search='.$search, false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('add_stock.php?id='.$p_id.'&search='.$search, false);
    }
  }

  $product = find_by_id('producto', (int)$_GET['id'], 'id_producto');
  $search = isset($_GET['search']) ? $_GET['search'] : '';
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
            <span>Añadir Stock</span>
         </strong>
        </div>
        <div class="panel-body">
         <div class="col-md-12">
          <form method="post" action="add_stock.php?id=<?php echo (int)$product['id_producto'];?>&search=<?php echo $search; ?>" class="clearfix">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="number" class="form-control" name="cantidad" placeholder="Cantidad">
               </div>
              </div>
              <button type="submit" name="add_stock" class="btn btn-danger">Añadir Stock</button>
          </form>
         </div>
        </div>
      </div>
    </div>
  </div>
<?php include_once('layouts/footer.php'); ?>
