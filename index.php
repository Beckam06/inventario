<?php
$page_title = 'Inicio de Sesión';
require_once('includes/load.php');
if($session->isUserLoggedIn(true)) { redirect('home.php', false);}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: url('imagenes/Choluteca.jpg') no-repeat center center fixed;
            background-size: contain; /* Cambiar de 'cover' a 'contain' para reducir el zoom */
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 128, 0, 0.3); /* Verde suave con opacidad */
            z-index: 1;
        }
        .login-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(26, 185, 97, 0.1);
            max-width: 350px;
            width: 100%;
        }
        .login-container h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #1a5e20; /* Cambiar el color del título */
        }
        .login-container .form-group {
            text-align: center; /* Centrar los campos */
        }
        .login-container .form-group label {
            color: #1a5e20; /* Cambiar el color de las etiquetas */
        }
        .login-container .form-control {
            border-radius: 5px;
            text-align: center; /* Centrar el texto dentro de los campos */
            color: #333; /* Cambiar el color del texto en los campos */
        }
        .login-container .btn {
            border-radius: 5px;
            background-color: #28a745; /* Cambiar el color del botón a verde */
            border-color: #28a745;
            color: white;
        }
        .login-container .btn:hover {
            background-color: #218838; /* Color más oscuro al pasar el cursor */
            border-color: #1e7e34;
        }
        .login-container .footer-text {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #1a5e20; /* Cambiar el color del texto del pie */
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="login-container">
        <h1>INVENTARIO IT</h1>
        <?php echo display_msg($msg); ?>
        <form method="post" action="auth.php" class="clearfix">
            <div class="form-group">
                <label for="username" class="control-label" >Usuario</label>
                <input type="text" class="form-control" name="username" placeholder="Nombre de usuario" required>
            </div>
            <div class="form-group">
                <label for="password" class="control-label">Contraseña</label>
                <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-block">
                    <i class="fa fa-sign-in"></i> Iniciar Sesión
                </button>
            </div>
        </form>
        <div class="footer-text">
            <p>© <?php echo date('Y'); ?> Sistema de Inventario IT</p>
        </div>
    </div>
</body>
</html>
<?php include_once('layouts/footer.php'); ?>
