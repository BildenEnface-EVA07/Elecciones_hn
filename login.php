<?php
session_start();
require_once 'backend/config/Database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correoElectronico = $_POST['uname'];
    $clave = $_POST['password'];

    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT c.idAdmin, p.nombreCompleto, c.clave, c.rol, c.estaActivo FROM Colaboradores c JOIN Personas p ON c.idPersona = p.idPersona WHERE c.correoElectronico = ?");
    $stmt->bind_param("s", $correoElectronico);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($clave, $user['clave'])) {
            if ($user['estaActivo']) {
                $_SESSION['loggedin'] = true;
                $_SESSION['idAdmin'] = $user['idAdmin'];
                $_SESSION['nombreCompleto'] = $user['nombreCompleto'];
                $_SESSION['rol'] = $user['rol'];

                header("Location: Dashboard.html");
                exit();
            } else {
                header("Location: login.php?error=Su cuenta está inactiva.");
                exit();
            }
        } else {
            header("Location: login.php?error=Correo o contraseña incorrectos.");
            exit();
        }
    } else {
        header("Location: login.php?error=Correo o contraseña incorrectos.");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LOGIN</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
 </head>
<body>
    <div class="content">
    <h1 class="titulo-elecciones" style="margin-top: 30px;margin-bottom: 40px;">
        Elecciones Generales 2025
    </h1>
  <form class="login-form" action="login.php" method="post">

    <?php if (isset($_GET['error'])) { ?>
      <p class="error"><?php echo $_GET['error']; ?></p>
    <?php } ?>

    <label for="uname">Ingrese su correo electrónico</label>
    <input type="text" id="uname" name="uname" placeholder="Correo electrónico"><br>

    <label for="password">Ingrese su contraseña</label>
    <input type="password" id="password" name="password" placeholder="• • • • • • • • • •"><br>

    <button type="submit">Entrar</button>
  </form>
  <a href="recuperarContrasena.php" ><h3 class="pswd-rec">Recuperar Contraseña</h3></a>
  <a href="index.php" class="volver-home">Volver al menú de inicio</a>
</div>

</body>
<footer class="footer">
    <p>Heyden Aldana - Héctor Funes - Bilander Fernández</p>
    <p>Copyright © todos los derechos reservados.</p>
    <p>Este es un proyecto simulado y no corresponde al modelo actual del sistema de elecciones generales en Honduras </p>
  </footer>
</html>