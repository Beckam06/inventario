<?php
require_once('includes/load.php');

if (isset($_GET['id'])) {
    $id_salida = (int)$_GET['id'];
    $query = "SELECT * FROM salida_equipo WHERE id_salidaEquipo = '{$id_salida}'";
    $result = $db->query($query);
    $salida = $db->fetch_assoc($result);

    if (isset($_POST['update'])) {
        $responsable = $_POST['responsable'];
        $departamento = $_POST['departamento'];
        $ordenEntrega = $_POST['ordenEntrega'];
        $fechaSalida = $_POST['fechaSalida'];
        $horaSalida = $_POST['horaSalida'];
        $cantidad_salida = (int)$_POST['cantidad_salida'];

        // Obtener la cantidad anterior
        $cantidad_anterior = (int)$salida['cantidad_salida'];

        // Calcular la diferencia
        $diferencia = $cantidad_anterior - $cantidad_salida;

        // Actualizar la cantidad en el inventario
        $query = "UPDATE producto SET cantidad = cantidad + '{$diferencia}' WHERE id_producto = '{$salida['id_producto']}'";
        $db->query($query);

        // Actualizar la salida
        $query = "UPDATE salida_equipo SET 
                  responsable = '{$responsable}', 
                  ordenEntrega = '{$ordenEntrega}', 
                  fechaSalida = '{$fechaSalida}', 
                  horaSalida = '{$horaSalida}', 
                  cantidad_salida = '{$cantidad_salida}' 
                  WHERE id_salidaEquipo = '{$id_salida}'";
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
                <label for="ordenEntrega">Orden de Entrega</label>
                <input type="text" class="form-control" name="ordenEntrega" value="<?php echo $salida['ordenEntrega']; ?>">
              </div>
              <div class="form-group">
                <label for="fechaSalida">Fecha de Salida</label>
                <input type="date" class="form-control" name="fechaSalida" value="<?php echo $salida['fechaSalida']; ?>">
              </div>
              <div class="form-group">
                <label for="horaSalida">Hora de Salida</label>
                <input type="time" class="form-control" name="horaSalida" value="<?php echo $salida['horaSalida']; ?>">
              </div>
              <div class="form-group">
                <label for="cantidad_salida">Cantidad</label>
                <input type="number" class="form-control" name="cantidad_salida" value="<?php echo $salida['cantidad_salida']; ?>">
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
