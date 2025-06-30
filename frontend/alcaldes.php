<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Votación de Alcaldes</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="index.php">Inicio</a></li>
        <li><a href="#">Servicios</a></li>
        <li><a href="#">Acerca de</a></li>
        <li><a href="#">Contacto</a></li>
      </ul>
    </nav>
  </header>

  <main class="content">
    <h1 class="titulo-elecciones">Votación de Alcaldes</h1>
    <form id="formAlcaldes">
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
              <h3 class="alcalde_texto" id="alcalde-candidato">Candidato</h3>
              <p class="alcalde_texto" id="alcalde-candidato">${candidato.nombreCandidato}</p>
              <input type="radio" name="municipio_${idMunicipio}" value="${candidato.idCandidato}" required>
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

    // Recolectar datos al enviar el formulario
    document.getElementById("formAlcaldes").addEventListener("submit", function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      for (let [key, value] of formData.entries()) {
        console.log(`Municipio ${key}: Candidato ${value}`);
      }

      alert("¡Voto de alcaldes registrado (simulado)!");
      // Aquí podrías hacer un POST al backend si decides guardar el voto
    });
  </script>
</body>
</html>
