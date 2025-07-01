<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Validación Ciudadano</title>
  <link rel="stylesheet" href="styles.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">

  <style>
    body {
      background: #F7F7F7;
      font-family: 'Fredoka', sans-serif;
      margin: 0;
    }

    .login-form {
      width: 60%;
      max-width: 600px;
      margin: 0 auto;
      padding: 30px;
      background: #FFFFFF;
      border-radius: 25px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: left;
    }

    .login-form label {
      display: block;
      font-size: 20px;
      margin: 15px 0 5px 10px;
      color: #000;
      font-weight: 500;
    }

    .login-form input {
      width: 90%;
      padding: 12px;
      margin-left: 10px;
      background-color: #D9D9D9;
      border: none;
      border-radius: 10px;
      font-size: 18px;
      color: rgba(0, 0, 0, 0.7);
      font-family: 'Fredoka';
    }

    .login-form .btn-start {
      display: block;
      margin: 30px auto 10px auto;
      padding: 12px 40px;
      font-size: 24px;
      background-color: #929AAB;
      color: #000;
      border: none;
      border-radius: 25px;
      font-family: 'Fredoka';
      font-weight: 425;
      cursor: pointer;
    }

    .login-form .btn-start:hover {
      background-color: #7a828f;
    }

    .login-form .error {
      margin-top: 10px;
      text-align: center;
      background-color: #F2DEDE;
      color: #A94442;
      padding: 10px;
      border-radius: 10px;
      font-size: 16px;
    }

    .titulo-elecciones {
      font-size: 48px;
      font-weight: 500;
      text-align: center;
      margin-top: 80px;
      margin-bottom: 40px;
    }

    footer {
      background-color: #393E46;
      color: #EEEEEE;
      text-align: center;
      padding: 20px;
      margin-top: 60px;
    }

    .navbar {
      background-color: #EEEEEE;
    }

    .nav-links {
      list-style: none;
      display: flex;
      justify-content: space-around;
      padding: 0;
      margin: 0;
    }

    .nav-links li {
      margin: 0;
    }

    .nav-links a {
      text-decoration: none;
      color: black;
      padding: 15px 20px;
      display: block;
      font-family: 'Fredoka';
      font-weight: 400;
    }

    .nav-links a:hover {
      background-color: #929AAB;
      text-decoration: underline;
    }

    .content {
      padding: 20px;
    }
  </style>
</head>

<body>
  <header>
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="index.php">Inicio</a></li>
        <li><a href="">Proceso de votación</a></li>
        <li><a href="#">Preguntas frecuentes</a></li>
        <li><a href="#">Algoritmos</a></li>
      </ul>
    </nav>
  </header>

  <div class="content">
    <h1 class="titulo-elecciones">Pre-Votación</h1>

    <form class="login-form" id="form-votacion" method="POST">
      <label for="nombre">Nombre completo:</label>
      <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez">

      <label for="dni">Número de identidad:</label>
      <input type="text" id="dni" name="dni" maxlength="13" placeholder="Ej. 0801199901234">

      <button type="submit" class="btn-start">Empezar</button>
      <p id="error-msg" class="error" style="display:none;"></p>
    </form>
  </div>

  <footer class="footer">
        <p>Heyden Aldana - Héctor Funes - Bilander Fernández</p>
        <p>Copyright &copy; todos los derechos reservados.</p>
        <p>Este es un proyecto simulado y no corresponde al modelo actual del sistema de elecciones generales en Honduras.</p>
  </footer>

  <script>
document.getElementById("form-votacion").addEventListener("submit", async function(e) {
    e.preventDefault();

    const nombre = document.getElementById("nombre").value.trim();
    const dni = document.getElementById("dni").value.trim();
    const errorMsg = document.getElementById("error-msg");
    errorMsg.style.display = "none";
    errorMsg.textContent = "";

    // Validaciones frontend
    if (!nombre || !dni) {
        showError("Todos los campos son obligatorios.");
        return;
    }

    if (!/^\d{13}$/.test(dni)) {
        showError("El DNI debe tener 13 dígitos numéricos.");
        return;
    }

    try {
        const response = await fetch("/Elecciones_hn-main/backend/api/Personas/RegistrarVotante.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ nombre, dni })
        });

        if (!response.ok) {
            const err = await response.json().catch(() => null);
            throw new Error(err?.message || `HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.status === "success") {
            sessionStorage.setItem('idPersona', result.idPersona);
            sessionStorage.setItem('nombreVotante', nombre);
            sessionStorage.setItem('dniVotante', dni);
            window.location.href = "votacion.php";
        } else {
            showError(result.message || "Error desconocido");
        }
    } catch (error) {
        console.error("Error:", error);
        showError(error.message || "Error al conectar con el servidor");
    }
});

function showError(message) {
    const errorMsg = document.getElementById("error-msg");
    errorMsg.textContent = message;
    errorMsg.style.display = "block";
}
</script>
</body>
</html>
