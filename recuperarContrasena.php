<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
  <dialog open id="miModal">
  <h1>Escribe el correo electrónico asociado a tu cuenta:</h1>
  <form class="pswd-form" method="dialog">
    <form action="" method="post">
      <input type="text" id="uname" name="uname" placeholder="Correo electrónico"><br>
      <div>
        <button>Salir</button>
        <button>Enviar</button>
      </div>
      
    </form>
    
  </form>
</dialog>
    <div class="content">
        <h1 class="titulo-elecciones" style="margin-top: 0px;margin-bottom: 30px;">
            Ayúdanos a que puedas recuperar tu contraseña
        </h1>
        <h1>Para que puedas recuperar tu contraseña, debemos asegurarnos que trabajas con nosotros. Te hemos enviado un código a tu correo electrónico asociado, revísalo (en bandeja de entrada o spam) e ingresa el código. Expira en 10 minutos.</h1>
        <form action="recuperarContrasena.php" method="post">
            <input style="font-size:24px;border-color:transparent;max-width:400px;margin: 30px auto 0px auto;" type="password" id="password" name="password" placeholder="Ingrese el código"><br>
        </form>
        <button type="submit">Confirmar</button>
        <a href="login.php"><h3 class="pswd-rec">Volver a Iniciar sesión</h3></a>
        <a href="index.php" class="volver-home">Volver al menú de inicio</a>
</div>
<!-- POP UP PARA PEDIR EL CORREO -->

</body>
<footer class="footer">
        <p>Heyden Aldana - Héctor Funes - Bilander Fernández</p>
        <p>Copyright &copy; todos los derechos reservados.</p>
        <p>Este es un proyecto simulado y no corresponde al modelo actual del sistema de elecciones generales en Honduras.</p>
    </footer>
</html>
