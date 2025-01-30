<?php
$page_title = 'Asignar cubículos a categoría';
require_once('includes/load.php');
page_require_level(1);

// Verificar si se ha seleccionado una categoría
if (isset($_GET['id_categoria'])) {
    $id_categoria = (int)$_GET['id_categoria'];
    $categoria = find_by_id('categoria', $id_categoria);

    if (!$categoria) {
        $session->msg("d", "Categoría no encontrada.");
        redirect('cubiculo.php');
    }
} else {
    $session->msg("d", "Seleccione una categoría.");
    redirect('cubiculo.php');
}

// Procesar el formulario para asignar cubículos
if (isset($_POST['asignar_cubiculos'])) {
    $cubiculos_asignados = $_POST['cubiculos']; // Array de IDs de cubículos seleccionados

    if (!empty($cubiculos_asignados)) {
        foreach ($cubiculos_asignados as $id_cubiculo) {
            // Asignar el cubículo a la categoría
            $sql = "UPDATE cubiculo SET id_categoria = '{$id_categoria}' WHERE id_cubiculo = '{$id_cubiculo}'";
            $db->query($sql);
        }
        $session->msg("s", "Cubículos asignados correctamente.");
        redirect('categorie.php');
    } else {
        $session->msg("d", "No se seleccionaron cubículos.");
        redirect('asignar_cubiculos.php?id_categoria=' . $id_categoria);
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
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Asignar cubículos a <?php echo remove_junk(ucfirst($categoria['categoria'])); ?></span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="asignar_cubiculos.php?id_categoria=<?php echo $id_categoria; ?>">
                    <div class="form-group">
                        <label for="cubiculos">Seleccione cubículos:</label>
                        <?php
                        // Obtener todos los cubículos disponibles
                        $cubiculos = $db->query("SELECT * FROM cubiculo");
                        while ($cub = $db->fetch_assoc($cubiculos)) :
                        ?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="cubiculos[]" value="<?php echo $cub['id_cubiculo']; ?>">
                                    <?php echo remove_junk(ucfirst($cub['cubiculo'])); ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <button type="submit" name="asignar_cubiculos" class="btn btn-primary">Asignar cubículos</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>