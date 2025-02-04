<?php
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  $product = find_by_id('producto', (int)$_GET['id'], 'id_producto');
  if(!$product){
    $session->msg("d","ID del producto falta.");
    redirect('product.php');
  }

  $delete_id = delete_by_id('producto', (int)$product['id_producto'], 'id_producto');
  if($delete_id){
      $session->msg("s","Producto eliminado.");
      redirect('product.php');
  } else {
      $session->msg("d","Eliminación falló.");
      redirect('product.php');
  }
?>
