<?php
require_once('includes/load.php');

page_require_level(1);

if (isset($_GET['id'])) {
    $id_salida = (int)$_GET['id'];
    $query = "SELECT * FROM orden_salida WHERE id_orden_salida = '{$id_salida}'";
    $result = $db->query($query);
    $salida = $db->fetch_assoc($result);

    if (isset($_POST['update'])) {
        $responsable = $_POST['responsable'];
        $cantidad_entregada = (int)$_POST['cantidad_entregada'];
        $fecha_entrega = date('Y-m-d H:i:s'); // Capturar la fecha y hora del sistema

        // Obtener la cantidad anterior
        $cantidad_anterior = (int)$salida['cantidad_entregada'];

        // Calcular la diferencia
        $diferencia = $cantidad_anterior - $cantidad_entregada;

        // Actualizar la cantidad en el inventario
        $query = "UPDATE producto SET cantidad = cantidad + '{$diferencia}' WHERE id_producto = '{$salida['id_producto']}'";
        $db->query($query);

        // Actualizar la salida
        $query = "UPDATE orden_salida SET 
                  responsable = '{$responsable}', 
                  fecha_entrega = '{$fecha_entrega}', 
                  cantidad_entregada = '{$cantidad_entregada}' 
                  WHERE id_orden_salida = '{$id_salida}'";
        $db->query($query);
        header("Location: reporte_salida.php?id={$id_salida}");
    }

    include_once('layouts/header.php');
    ?>

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <strong>
              <span class="glyphicon glyphicon-th"></span>
              <span>Editar Salida de Producto</span>
           </strong>
          </div>
          <div class="panel-body">
            <form method="post" action="editar_salida.php?id=<?php echo $id_salida; ?>">
              <div class="form-group">
                <label for="responsable">Responsable</label>
                <input type="text" class="form-control" name="responsable" value="<?php echo $salida['responsable']; ?>">
              </div>
              <div class="form-group">
                <label for="cantidad_entregada">Cantidad</label>
                <input type="number" class="form-control" name="cantidad_entregada" value="<?php echo $salida['cantidad_entregada']; ?>">
              </div>
              <button type="submit" name="update" class="btn btn-primary">Actualizar</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <?php include_once('layouts/footer.php'); ?>
    <?php
} else {
    header("Location: reporte_salida.php");
}
?>
