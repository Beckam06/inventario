<?php
require_once('includes/load.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (isset($_GET['id'])) {
    $id_solicitud = (int)$_GET['id'];

    // Obtener detalles de la solicitud
    $solicitud = $db->query("
        SELECT sc.*, p.nombreProducto, p.marca, p.modelo, d.nombre_departamento 
        FROM solicitud_compra sc
        JOIN producto p ON sc.id_producto = p.id_producto
        JOIN departamento d ON sc.id_departamento = d.id_departamento
        WHERE sc.id_solicitudCompra = {$id_solicitud}
    ")->fetch_assoc();

    if ($solicitud) {
        // Configuración del correo
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tu_correo@gmail.com'; // Cambia esto al correo del remitente
            $mail->Password = 'tu_contraseña'; // Cambia esto a la contraseña del remitente
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('tu_correo@gmail.com', 'Inventario');
            $mail->addAddress('luishtercero.lt@gmail.com'); // Cambia esto al correo del destinatario

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Solicitud de Pedido Pendiente';
            $mail->Body    = "
                <h1>Solicitud de Pedido Pendiente</h1>
                <p><strong>Producto:</strong> {$solicitud['nombreProducto']}</p>
                <p><strong>Marca:</strong> {$solicitud['marca']}</p>
                <p><strong>Modelo:</strong> {$solicitud['modelo']}</p>
                <p><strong>Descripción:</strong> {$solicitud['descripcion']}</p>
                <p><strong>Cantidad Solicitada:</strong> {$solicitud['cantidad_solicitada']}</p>
                <p><strong>Departamento:</strong> {$solicitud['nombre_departamento']}</p>
                <p><strong>Responsable:</strong> {$solicitud['responsable']}</p>
                <p><strong>Fecha y Hora de Solicitud:</strong> {$solicitud['fecha_solicitud']}</p>
            ";

            $mail->send();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID de solicitud no especificado.']);
}
?>
