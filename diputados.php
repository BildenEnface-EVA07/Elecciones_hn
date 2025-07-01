<?php
session_start();

// Validar que haya votado por presidente
if (!isset($_SESSION['votos_temporales']['Presidente'])) {
    header("Location: votacion.php");
    exit();
}
?>
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

    <form id="formVotoDiputados" method="POST">
      <div id="diputadosContainer"></div>
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
        const res = await fetch("/Elecciones_hn-main/backend/api/Personas/GetCandidatos.php");
        const data = await res.json();
        const diputados = data.data.filter(c => c.cargo === "Diputado");

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

        document.getElementById("formVotoDiputados").addEventListener("change", () => {
          const checkboxes = document.querySelectorAll('input[name="diputados[]"]:checked');
          if (checkboxes.length > 20) {
            alert("Solo puede seleccionar hasta 20 diputados.");
            checkboxes[checkboxes.length - 1].checked = false;
          }
        });

        document.getElementById("formVotoDiputados").addEventListener("submit", (e) => {
          e.preventDefault();
          const seleccionados = Array.from(document.querySelectorAll('input[name="diputados[]"]:checked')).map(cb => cb.value);
          
          if (seleccionados.length !== 20) {
            alert("Debe seleccionar exactamente 20 diputados.");
            return;
          }

          // Enviar mediante fetch al backend para guardar en sesión
          fetch("/Elecciones_hn-main/backend/api/Personas/guardarvotodiputados.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ diputados: seleccionados })
          })
          .then(resp => resp.json())
          .then(data => {
            if (data.status === "success") {
              window.location.href = "alcaldes.php";
            } else {
              alert(data.message || "Error al guardar los votos.");
            }
          })
          .catch(() => alert("Error al procesar el voto."));
        });

      } catch (error) {
        console.error("Error al cargar los diputados:", error);
        document.getElementById("diputadosContainer").innerHTML = "<p>Error al cargar los candidatos.</p>";
      }
    });
  </script>
</body>
</html>
