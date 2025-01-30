<?php
$page_title = 'Asignar Cubículos a Categoría';
require_once('includes/load.php');
page_require_level(1);

// Obtener el ID de la categoría desde la URL
$id_categoria = (int)$_GET['id_categoria'];

// Obtener la categoría
$categoria = find_by_id('categoria', $id_categoria);

// Obtener todos los cubículos
$all_cubiculos = find_all('cubiculos');

// Obtener los cubículos asignados a esta categoría
$cubiculos_asignados = find_by_sql("
    SELECT cubiculos.* 
    FROM cubiculos
    INNER JOIN categoria_cubiculo ON cubiculos.id_cubiculo = categoria_cubiculo.id_cubiculo
    WHERE categoria_cubiculo.id_categoria = {$id_categoria}
");

// Procesar el formulario para asignar/desasignar cubículos
if (isset($_POST['asignar_cubiculos'])) {
    $cubiculos_seleccionados = $_POST['cubiculos'];

    // Eliminar todas las asignaciones actuales
    $db->query("DELETE FROM categoria_cubiculo WHERE id_categoria = {$id_categoria}");

    // Asignar los nuevos cubículos seleccionados
    if (!empty($cubiculos_seleccionados)) {
        foreach ($cubiculos_seleccionados as $id_cubiculo) {
            $sql = "INSERT INTO categoria_cubiculo (id_categoria, id_cubiculo) VALUES ({$id_categoria}, {$id_cubiculo})";
            $db->query($sql);
        }
    }

    $session->msg("s", "Cubículos asignados exitosamente.");
    redirect("asignar_cubiculos.php?id_categoria={$id_categoria}", false);
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Asignar Cubículos a la Categoría: <?php echo $categoria['categoria']; ?></span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="asignar_cubiculos.php?id_categoria=<?php echo $id_categoria; ?>">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre del Cubículo</th>
                                <th>Asignar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_cubiculos as $cubiculo): ?>
                                <tr>
                                    <td><?php echo count_id(); ?></td>
                                    <td><?php echo remove_junk(ucfirst($cubiculo['nombre_cubiculo'])); ?></td>
                                    <td>
                                        <input type="checkbox" name="cubiculos[]" value="<?php echo $cubiculo['id_cubiculo']; ?>"
                                            <?php if (in_array($cubiculo, $cubiculos_asignados)) echo "checked"; ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="asignar_cubiculos" class="btn btn-primary">Guardar Asignaciones</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>