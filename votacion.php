<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Elección Presidencial</title>
  <link rel="stylesheet" href="styles.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
</head>

<body>
  <header>
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="../index.php">Inicio</a></li>
        <li><a href="#">Proceso de votación</a></li>
        <li><a href="#">Preguntas frecuentes</a></li>
        <li><a href="#">Algoritmos</a></li>
      </ul>
    </nav>
  </header>

  <div class="seccion-eleccion">
    <h1 class="titulo-elecciones">Elección Presidencial</h1>
    <h2>Seleccione el Presidente por el que desea votar:</h2>

    <form id="formVoto" action="guardar_voto.php" method="POST">
      <div id="candidatosContainer" class="candidatos-grid">
        <!-- Candidatos serán insertados por JavaScript -->
      </div>

      <button class="btn-siguiente" type="submit">Siguiente</button>
    </form>
  </div>

  <footer>
    <h2>Heyden Aldana - Héctor Funes - Bilander Fernández</h2>
    <h2>Copyright © todos los derechos reservados.</h2>
    <h2>Este es un proyecto simulado y no corresponde al modelo actual del sistema de elecciones generales en Honduras</h2>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      try {
        const res = await fetch("/Elecciones_hn-main/backend/api/Personas/GetPersonas.php");
        const data = await res.json();

        const container = document.getElementById("candidatosContainer");

        // Filtrar solo candidatos a presidente
        const candidatosPresidenciales = data.filter(c => c.cargo === "Presidente");

        if (candidatosPresidenciales.length === 0) {
          container.innerHTML = "<p>No hay candidatos disponibles.</p>";
          return;
        }

        candidatosPresidenciales.forEach(candidato => {
          const box = document.createElement("label");
          box.className = "candidato-box";
          box.innerHTML = `
            <h3>Candidato</h3>
            <p>${candidato.nombreCandidato}</p>
            <input type="radio" name="candidato_presidente" value="${candidato.idCandidato}" required>
          `;
          container.appendChild(box);
        });
      } catch (error) {
        console.error("Error al cargar los candidatos:", error);
        document.getElementById("candidatosContainer").innerHTML = "<p>Error al cargar los candidatos.</p>";
      }
    });
  </script>
</body>
</html>
