<?php
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  if(isset($_GET['id'])){
    $product_id = (int)$_GET['id'];
    if(delete_by_id('producto', $product_id)){
      $session->msg("s","Producto eliminado.");
      redirect('product.php');
    } else {
      $session->msg("d","Eliminación falló.");
      redirect('product.php');
    }
  } else {
    $session->msg("d","ID vacío.");
    redirect('product.php');
  }
?>
