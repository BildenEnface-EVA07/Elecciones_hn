<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Elección de Diputados</title>
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
    <h1 class="titulo-elecciones">Elección de Diputados</h1>
    <h2>Seleccione los diputados por los que desea votar (Máx 20):</h2>

    <form id="formVotoDiputados" action="guardar_voto_diputados.php" method="POST">
      <div id="diputadosContainer">
        <!-- Partidos y diputados serán insertados por JavaScript -->
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

        const diputados = data.filter(c => c.cargo === "Diputado");

        // Agrupar por idPartido
        const partidosMap = {};
        diputados.forEach(d => {
          if (!partidosMap[d.idPartido]) {
            partidosMap[d.idPartido] = [];
          }
          partidosMap[d.idPartido].push(d);
        });

        const container = document.getElementById("diputadosContainer");

        for (const idPartido in partidosMap) {
          const diputadosPartido = partidosMap[idPartido];

          const partidoBox = document.createElement("div");
          partidoBox.className = "partido-container";

          // Aquí podrías usar el nombre y logo si lo tienes en el JSON
          partidoBox.innerHTML = `
            <div class="partido-logo"></div>
            <div class="partido-nombre">Partido</div>
            <div class="diputado-lista">
              ${diputadosPartido.map(d => `
                <label class="diputado-box">
                  ${d.nombreCandidato}
                  <input type="checkbox" name="diputados[]" value="${d.idCandidato}">
                </label>
              `).join('')}
            </div>
          `;

          container.appendChild(partidoBox);
        }

        // Limitar a 20 selecciones
        document.getElementById("formVotoDiputados").addEventListener("change", () => {
          const checkboxes = document.querySelectorAll('input[name="diputados[]"]:checked');
          if (checkboxes.length > 20) {
            alert("Solo puede seleccionar hasta 20 diputados.");
            // Desmarca el último marcado
            checkboxes[checkboxes.length - 1].checked = false;
          }
        });

      } catch (error) {
        console.error("Error al cargar los diputados:", error);
        document.getElementById("diputadosContainer").innerHTML = "<p>Error al cargar los candidatos.</p>";
      }
    });
  </script>
</body>
</html>
