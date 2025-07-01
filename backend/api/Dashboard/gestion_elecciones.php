<?php
require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $sePuedeVotar = filter_var($input['sePuedeVotar'], FILTER_VALIDATE_BOOLEAN);
        $adminToken = $input['adminToken'] ?? '';

        if ($adminToken !== 'ADMIN_SECRET_TOKEN') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado.']);
            exit();
        }

        $result = $db->query("SELECT idProcesoVotacion FROM Proceso_Votacion LIMIT 1");
        $procesoVotacion = $db->fetchAll($result);

        if (empty($procesoVotacion)) {
            $insertSql = "INSERT INTO Proceso_Votacion (sePuedeVotar) VALUES (?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("i", $sePuedeVotar_int);
            $sePuedeVotar_int = (int)$sePuedeVotar;
            $stmt->execute();

            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Proceso de votación creado y estado actualizado.']);

        } else {
            $idProcesoVotacion = $procesoVotacion[0]['idProcesoVotacion'];
            $updateSql = "UPDATE Proceso_Votacion SET sePuedeVotar = ? WHERE idProcesoVotacion = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ii", $sePuedeVotar_int, $idProcesoVotacion);
            $sePuedeVotar_int = (int)$sePuedeVotar;
            $stmt->execute();

            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Estado de las elecciones actualizado correctamente.']);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $db->query("SELECT sePuedeVotar FROM Proceso_Votacion LIMIT 1");
        $procesoVotacion = $db->fetchAll($result);

        if (!empty($procesoVotacion)) {
            $sePuedeVotar = (bool)$procesoVotacion[0]['sePuedeVotar'];
            echo json_encode(['status' => 'success', 'sePuedeVotar' => $sePuedeVotar]);
        } else {
            echo json_encode(['status' => 'success', 'sePuedeVotar' => false, 'message' => 'No se ha iniciado un proceso de votación.']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>