<?php
$page_title = 'Editar categoría';
require_once('includes/load.php');
page_require_level(1);

// Verificar si el ID de la categoría está presente y es válido
if (isset($_GET['id_categoria']) && is_numeric($_GET['id_categoria'])) {
    $categoria_id = (int)$_GET['id_categoria'];
    $categoria = find_by_id('categoria', $categoria_id, 'id_categoria');
    if (!$categoria) {
        $session->msg("d", "Categoría no encontrada.");
        redirect('categorie.php');
    }
} else {
    $session->msg("d", "ID de categoría no válido.");
    redirect('categorie.php');
}

// Procesar el formulario de edición
if (isset($_POST['edit_cat'])) {
    $req_field = array('categoria-categoria');
    validate_fields($req_field);
    $cat_name = remove_junk($db->escape($_POST['categoria-categoria']));
    if (empty($errors)) {
        $sql = "UPDATE categoria SET categoria='{$cat_name}'";
        $sql .= " WHERE id_categoria='{$categoria['id_categoria']}'";
        $result = $db->query($sql);
        if ($result && $db->affected_rows() === 1) {
            $session->msg("s", "Categoría actualizada con éxito.");
            redirect('categorie.php', false);
        } else {
            $session->msg("d", "Lo siento, actualización falló.");
            redirect('categorie.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('categorie.php', false);
    }
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Editando <?php echo remove_junk(ucfirst($categoria['categoria'])); ?></span>
                </strong>
            </div>
            <div class="panel-body">
                <form method="post" action="edit_categorie.php?id_categoria=<?php echo (int)$categoria['id_categoria']; ?>">
                    <div class="form-group">
                        <input type="text" class="form-control" name="categoria-categoria" value="<?php echo remove_junk(ucfirst($categoria['categoria'])); ?>">
                    </div>
                    <button type="submit" name="edit_cat" class="btn btn-primary">Actualizar categoría</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>