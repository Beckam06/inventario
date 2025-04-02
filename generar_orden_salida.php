<?php
$page_title = 'Generar Orden de Salida';
require_once('includes/load.php');
require_once('includes/functions.php');
page_require_level(1);

if (!isset($_GET['id'])) {
    $session->msg('d', 'ID de solicitud no especificado.');
    redirect('lista_solicitudes_recibidas.php');
}

$id_solicitud = (int)$_GET['id'];

// Obtener detalles de la solicitud
$solicitud = $db->query("
    SELECT sc.*, p.nombreProducto, p.marca, p.modelo, p.descripcion, d.nombre_departamento 
    FROM solicitud_compra sc
    JOIN producto p ON sc.id_producto = p.id_producto
    JOIN departamento d ON sc.id_departamento = d.id_departamento
    WHERE sc.id_solicitudCompra = {$id_solicitud}
")->fetch_assoc();

if (!$solicitud) {
    $session->msg('d', 'Solicitud no encontrada.');
    redirect('lista_solicitudes_recibidas.php');
}

// Obtener departamentos
$departamentos = $db->query("SELECT * FROM departamento")->fetch_all(MYSQLI_ASSOC);

// Obtener encargados de salida desde la tabla `users`
$encargados = $db->query("SELECT id, name FROM users WHERE user_level = 1 AND status = 1")->fetch_all(MYSQLI_ASSOC);

if (!$encargados) {
    $session->msg('d', 'No hay encargados de salida registrados.');
    redirect('lista_solicitudes_recibidas.php');
}

// Procesar el formulario de generación de orden de salida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->begin_transaction();

    try {
        $id_departamento = (int)$_POST['id_departamento'];
        $responsable = $db->escape($_POST['responsable']);
        $cantidad_entregada = (int)$_POST['cantidad_entregada'];
        $id_encargado = (int)$_POST['id_encargado'];

        // Verificar si el encargado seleccionado existe en la tabla `users`
        $encargado_valido = $db->query("SELECT id FROM users WHERE id = {$id_encargado}")->num_rows > 0;
        if (!$encargado_valido) {
            throw new Exception("El encargado seleccionado no es válido.");
        }

        // Verificar si la cantidad entregada es válida
        if ($cantidad_entregada <= 0 || $cantidad_entregada > $solicitud['cantidad_solicitada']) {
            throw new Exception("Cantidad entregada no válida.");
        }

        // Insertar la orden de salida
        $sql_orden_salida = "INSERT INTO orden_salida (id_solicitudCompra, id_producto, id_departamento, responsable, cantidad_entregada, archivo_pdf, id_encargado) 
                             VALUES ('{$id_solicitud}', '{$solicitud['id_producto']}', '{$id_departamento}', '{$responsable}', '{$cantidad_entregada}', '', '{$id_encargado}')";
        $db->query($sql_orden_salida);
        $id_orden_salida = $db->insert_id();

        // Insertar detalles en la tabla detalle_orden_salida
        for ($i = 1; $i <= $cantidad_entregada; $i++) {
            // Generar un código de unidad único
            $codigo_unidad = "{$solicitud['id_producto']}-" . str_pad($i, 5, '0', STR_PAD_LEFT) . "-{$id_orden_salida}";

            // Agregar el código de unidad a la tabla producto_codigo
            $sql_producto_codigo = "INSERT INTO producto_codigo (codigo_unidad, id_producto) 
                                     VALUES ('{$codigo_unidad}', '{$solicitud['id_producto']}')";
            $db->query($sql_producto_codigo);
            $id_producto_codigo = $db->insert_id();

            // Insertar el detalle en la tabla detalle_orden_salida
            $sql_detalle = "INSERT INTO detalle_orden_salida (id_orden_salida, id_producto_codigo) 
                            VALUES ('{$id_orden_salida}', '{$id_producto_codigo}')";
            if (!$db->query($sql_detalle)) {
                throw new Exception("Error al insertar en detalle_orden_salida.");
            }
        }

        // Descontar del inventario
        $db->query("UPDATE producto SET cantidad = cantidad - {$cantidad_entregada} WHERE id_producto = {$solicitud['id_producto']}");

        // Actualizar la cantidad solicitada en la solicitud
        $nueva_cantidad = $solicitud['cantidad_solicitada'] - $cantidad_entregada;
        if ($nueva_cantidad > 0) {
            $db->query("UPDATE solicitud_compra SET cantidad_solicitada = {$nueva_cantidad} WHERE id_solicitudCompra = {$id_solicitud}");
        } else {
            // Si se entrega toda la cantidad, marcar la solicitud como completada
            $db->query("UPDATE solicitud_compra SET cantidad_solicitada = 0, id_estado = 3 WHERE id_solicitudCompra = {$id_solicitud}");
        }

        // Generar el PDF con la nota de entrega
        $pdf_path = generar_pdf_orden_salida(
            $id_orden_salida,
            $id_solicitud,
            $id_departamento,
            $responsable,
            $cantidad_entregada,
            $solicitud['nombreProducto'],
            $solicitud['marca'],
            $solicitud['modelo'],
            $solicitud['descripcion']
        );

        // Actualizar la orden de salida con la ruta del PDF
        $db->query("UPDATE orden_salida SET archivo_pdf = '{$pdf_path}' WHERE id_orden_salida = {$id_orden_salida}");

        $db->commit();
        $session->msg('s', 'Orden de salida generada correctamente.');

        // Redirigir al historial de órdenes de salida
        redirect('historial_ordenes_salida.php');
    } catch (Exception $e) {
        $db->rollback();
        $session->msg('d', 'Error: ' . $e->getMessage());
        redirect('generar_orden_salida.php?id=' . $id_solicitud);
    }
}

// Verificar si la función ya está definida
if (!function_exists('generar_pdf_orden_salida')) {
    function generar_pdf_orden_salida($id_orden_salida, $id_solicitud, $id_departamento, $responsable, $cantidad_entregada, $nombreProducto, $marca, $modelo, $descripcion) {
        global $db;

        // Consulta SQL para obtener el nombre del encargado de salida
        $query = "SELECT u.name AS encargado_salida 
                  FROM orden_salida os
                  JOIN users u ON os.id_encargado = u.id
                  WHERE os.id_orden_salida = {$id_orden_salida}";
        $result = $db->query($query);

        // Verificar si la consulta devuelve resultados
        $encargado = $result && $db->num_rows($result) > 0 
            ? $db->fetch_assoc($result) 
            : ['encargado_salida' => 'No especificado']; // Valor predeterminado

        $carpeta_pdfs = __DIR__ . '/pdfs_ordenes_salida';

        // Verificar si la carpeta existe, si no, crearla
        if (!is_dir($carpeta_pdfs)) {
            mkdir($carpeta_pdfs, 0777, true);
        }

        // Crear el PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema de Inventario');
        $pdf->SetTitle('Orden de Salida #' . $id_orden_salida);
        $pdf->SetSubject('Orden de Salida');
        $pdf->SetKeywords('Orden, Salida, Inventario');

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // Contenido del PDF
        $html = '<h1 style="text-align: center;">NOTA DE ENTREGA</h1>';
        $html .= '<p><strong>Producto:</strong> ' . $nombreProducto . '</p>';
        $html .= '<p><strong>Marca:</strong> ' . $marca . '</p>';
        $html .= '<p><strong>Modelo:</strong> ' . $modelo . '</p>';
        $html .= '<p><strong>Descripción:</strong> ' . $descripcion . '</p>';
        $html .= '<p><strong>Cantidad Entregada:</strong> ' . $cantidad_entregada . '</p>';
        $html .= '<p><strong>Responsable:</strong> ' . $responsable . '</p>';

        // Sección de firmas
        $html .= '<br><br>';
        $html .= '<table width="100%">
                    <tr>
                        <td width="50%" style="text-align: left;">
                            <strong>RECIBE:</strong><br><br><br>' . $responsable . '
                        </td>
                        <td width="50%" style="text-align: left;">
                            <strong>ENTREGA:</strong><br><br><br>' . $encargado['encargado_salida'] . '
                        </td>
                    </tr>
                  </table>';

        $file_path = $carpeta_pdfs . '/orden_salida_' . $id_orden_salida . '.pdf';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($file_path, 'F'); // Guardar el archivo en el servidor
        return $file_path;
    }
}

include_once('layouts/header.php');
?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Generar Orden de Salida - Solicitud #<?php echo $id_solicitud; ?></strong>
            </div>
            <div class="panel-body">
                <form method="post" action="generar_orden_salida.php?id=<?php echo $id_solicitud; ?>">
                    <!-- Producto (no editable) -->
                    <div class="form-group">
                        <label>Producto:</label>
                        <input type="text" class="form-control" value="<?php echo $solicitud['nombreProducto']; ?>" readonly>
                    </div>

                    <!-- Descripción (no editable) -->
                    <div class="form-group">
                        <label>Descripción:</label>
                        <input type="text" class="form-control" value="<?php echo $solicitud['descripcion']; ?>" readonly>
                    </div>

                    <!-- Cantidad (editable, pero precargada con el total de la solicitud) -->
                    <div class="form-group">
                        <label>Cantidad:</label>
                        <input type="number" class="form-control" name="cantidad_entregada" min="1" max="<?php echo $solicitud['cantidad_solicitada']; ?>" value="<?php echo $solicitud['cantidad_solicitada']; ?>" required>
                    </div>

                    <!-- Departamento (editable) -->
                    <div class="form-group">
                        <label>Departamento:</label>
                        <select class="form-control" name="id_departamento" required>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?php echo $departamento['id_departamento']; ?>" <?php echo ($departamento['id_departamento'] == $solicitud['id_departamento']) ? 'selected' : ''; ?>>
                                    <?php echo $departamento['nombre_departamento']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Responsable (editable) -->
                    <div class="form-group">
                        <label>Responsable:</label>
                        <input type="text" class="form-control" name="responsable" value="<?php echo $solicitud['responsable']; ?>" required>
                    </div>

                    <!-- Responsable de la salida (lista desplegable) -->
                    <div class="form-group">
                        <label>Encargado de la Salida:</label>
                        <select class="form-control" name="id_encargado" required>
                            <option value="">Selecciona un encargado</option>
                            <?php foreach ($encargados as $encargado): ?>
                                <option value="<?php echo $encargado['id']; ?>">
                                    <?php echo $encargado['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Botón para generar la orden -->
                    <button type="submit" name="generar_orden" class="btn btn-primary">Generar Orden de Salida</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>