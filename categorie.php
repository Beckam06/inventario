<?php
$page_title = 'Lista de categorías';
require_once('includes/load.php');
page_require_level(1);

// Configuración de la paginación
$registros_por_pagina = 5; // Número de registros por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página actual, por defecto es 1
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Consulta paginada para obtener las categorías
$sql = "SELECT * FROM categoria LIMIT {$registros_por_pagina} OFFSET {$offset}";
$all_categoria = find_by_sql($sql);

// Obtener el total de registros para calcular el número total de páginas
$sql_total = "SELECT COUNT(*) AS total FROM categoria";
$resultado_total = $db->query($sql_total);
$fila_total = $db->fetch_assoc($resultado_total);
$total_registros = $fila_total['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

$all_cubiculos = find_all('cubiculos'); // Obtener todos los cubículos

// Procesar el formulario para agregar una nueva categoría
if (isset($_POST['add_cat'])) {
    $req_field = array('categoria');
    validate_fields($req_field);
    $cat_categoria = remove_junk($db->escape($_POST['categoria']));
    $cubiculos_asignados = isset($_POST['cubiculos']) ? $_POST['cubiculos'] : [];

    if (empty($errors)) {
        // Insertar la categoría
        $sql = "INSERT INTO categoria (categoria) VALUES ('{$cat_categoria}')";
        if ($db->query($sql)) {
            $id_categoria = $db->insert_id(); // Obtener el ID de la categoría recién insertada

            // Asignar cubículos a la categoría
            if (!empty($cubiculos_asignados)) {
                foreach ($cubiculos_asignados as $id_cubiculo) {
                    $db->query("INSERT INTO categoria_cubiculo (id_categoria, id_cubiculo) VALUES ({$id_categoria}, {$id_cubiculo})");
                }
            }

            $session->msg("s", "Categoría agregada exitosamente.");
            redirect('categorie.php', false);
        } else {
            $session->msg("d", "Lo siento, registro falló.");
            redirect('categorie.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('categorie.php', false);
    }
}

// Procesar el formulario para agregar/quitar cubículos de una categoría existente
if (isset($_POST['update_cubiculos'])) {
    $id_categoria = (int)$_POST['id_categoria'];
    $cubiculos_asignados = isset($_POST['cubiculos']) ? $_POST['cubiculos'] : [];

    // Eliminar todas las asignaciones actuales
    $db->query("DELETE FROM categoria_cubiculo WHERE id_categoria = {$id_categoria}");

    // Asignar los nuevos cubículos seleccionados
    if (!empty($cubiculos_asignados)) {
        foreach ($cubiculos_asignados as $id_cubiculo) {
            $db->query("INSERT INTO categoria_cubiculo (id_categoria, id_cubiculo) VALUES ({$id_categoria}, {$id_cubiculo})");
        }
    }

    $session->msg("s", "Cubículos actualizados exitosamente.");
    redirect('categorie.php', false);
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
                    <span>Agregar categoría</span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="categorie.php">
                    <div class="form-group">
                        <input type="text" class="form-control" name="categoria" placeholder="Nombre de la categoría" required>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar Cubículos:</label>
                        <?php foreach ($all_cubiculos as $cubiculo): ?>
                            <div>
                                <input type="checkbox" name="cubiculos[]" value="<?php echo $cubiculo['id_cubiculo']; ?>">
                                <?php echo $cubiculo['cubiculo']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="add_cat" class="btn btn-primary">Agregar categoría</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Lista de categorías</span>
                </strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Categorías</th>
                            <th>Cubículos Asignados</th>
                            <th class="text-center" style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_categoria as $cat): ?>
                            <tr>
                                <td class="text-center"><?php echo count_id(); ?></td>
                                <td><?php echo remove_junk(ucfirst($cat['categoria'])); ?></td>
                                <td>
                                    <?php
                                    $cubiculos_asignados = find_by_sql("
                                        SELECT c.cubiculo 
                                        FROM cubiculos c
                                        INNER JOIN categoria_cubiculo cc ON c.id_cubiculo = cc.id_cubiculo
                                        WHERE cc.id_categoria = {$cat['id_categoria']}
                                    ");
                                    foreach ($cubiculos_asignados as $cubiculo) {
                                        echo $cubiculo['cubiculo'] . "<br>";
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="edit_categorie.php?id_categoria=<?php echo (int)$cat['id_categoria']; ?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Editar">
                                            <span class="glyphicon glyphicon-edit"></span>
                                        </a>
                                        <a href="delete_categorie.php?id_categoria=<?php echo (int)$cat['id_categoria']; ?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Eliminar">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#modalCubiculos<?php echo $cat['id_categoria']; ?>" title="Gestionar Cubículos">
                                            <span class="glyphicon glyphicon-folder-open"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="paginacion">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="btn btn-primary">Anterior</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?pagina=<?php echo $i; ?>" class="btn btn-default <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="btn btn-primary">Siguiente</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>