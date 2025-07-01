<?php
session_start();

// Verificar que viene el alcalde seleccionado
if (!isset($_POST['alcalde_general'])) {
    echo "Error: No se seleccionó ningún candidato a alcalde.";
    exit;
}

// Verificar que ya existen los votos de presidente y diputados en la sesión
if (
    !isset($_SESSION['votos_temporales']['Presidente']) ||
    !isset($_SESSION['votos_temporales']['Diputados']) ||
    count($_SESSION['votos_temporales']['Diputados']) !== 20
) {
    echo "Error: Debe haber votado por presidente y 20 diputados antes de votar por alcalde.";
    exit;
}

// Guardar el id del candidato a alcalde en la sesión
$_SESSION['votos_temporales']['Alcalde'] = (int)$_POST['alcalde_general'];


try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Iniciar transacción
    $conn->beginTransaction();

    // 1. Obtener idPersona de la sesión
    $idPersona = $_SESSION['idPersona'] ?? null;
    if (!$idPersona) {
        throw new Exception("No se identificó al votante");
    }

    // 2. Insertar todos los votos
    $stmt = $conn->prepare("INSERT INTO Votaciones_Votos (idProcesoVotacion, idPersona, idCandidato) VALUES (1, ?, ?)");
    
    // Presidente
    $stmt->execute([$idPersona, $_SESSION['votos_temporales']['Presidente']]);
    
    // Diputados
    foreach ($_SESSION['votos_temporales']['Diputados'] as $idDiputado) {
        $stmt->execute([$idPersona, $idDiputado]);
    }
    
    // Alcalde
    $stmt->execute([$idPersona, $_POST['alcalde_general']]);

    // 3. Marcar como votado en Personas
    $update = $conn->prepare("UPDATE Personas SET 
        haVotadoPresidente = 1,
        haVotadoDiputado = 1,
        haVotadoAlcalde = 1
        WHERE idPersona = ?");
    $update->execute([$idPersona]);

    // Commit y limpiar
    $conn->commit();
    session_unset();
    
    header("Location: /frontend/confirmacion.php");
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    die("Error al guardar los votos: " . $e->getMessage());
    }
exit;
?>
