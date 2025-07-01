<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Elección Alcaldes</title>
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

  <main class="content">
    <h1 class="titulo-elecciones">Votación de Alcaldes</h1>
    <form id="formAlcaldes" method="POST" action="/Elecciones_hn-main/backend/api/Personas/guardar_voto_alcalde.php">
      <div id="municipiosContainer"></div>
      <br>
      <button type="submit" class="btn-start">Votar</button>
    </form>
  </main>

  <footer>
    <h2>Heyden Aldana - Héctor Funes - Bilander Fernández</h2>
    <h2>Copyright © todos los derechos reservados.</h2>
    <h2>Este es un proyecto simulado y no corresponde al modelo actual del sistema de elecciones generales en Honduras</h2>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      const municipiosContainer = document.getElementById("municipiosContainer");

      try {
        const res = await fetch("/Elecciones_hn-main/backend/api/Personas/GetAlcaldes.php");
        const data = await res.json();

        const alcaldes = data.data;

        // Agrupar por municipio
        const porMunicipio = {};
        alcaldes.forEach(c => {
          if (!porMunicipio[c.idMunicipio]) {
            porMunicipio[c.idMunicipio] = {
              nombre: c.nombreMunicipio,
              candidatos: []
            };
          }
          porMunicipio[c.idMunicipio].candidatos.push(c);
        });

        // Generar bloques por municipio
        for (const [idMunicipio, info] of Object.entries(porMunicipio)) {
          const section = document.createElement("section");
          section.className = "municipio-section";
          section.innerHTML = `<h2>${info.nombre}</h2>`;

          info.candidatos.forEach(candidato => {
            const box = document.createElement("label");
            box.className = `candidato-box alcalde partido-${candidato.idPartido}`;
            box.innerHTML = `
              <h3>Candidato</h3>
              <p>${candidato.nombreCandidato}</p>
              <input type="radio" name="alcalde_general" value="${candidato.idCandidato}" required>
            `;
            section.appendChild(box);
          });

          municipiosContainer.appendChild(section);
        }
      } catch (error) {
        console.error("Error al cargar alcaldes:", error);
        municipiosContainer.innerHTML = "<p>Error al cargar los candidatos a alcalde.</p>";
      }
    });
  </script>
</body>
</html>
