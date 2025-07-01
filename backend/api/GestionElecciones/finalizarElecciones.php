<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $db = Database::getInstance();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sqlSelectLatest = "SELECT idProcesoVotacion FROM Proceso_Votacion ORDER BY idProcesoVotacion DESC LIMIT 1";
        $resultSelectLatest = $db->query($sqlSelectLatest);
        $latestProceso = mysqli_fetch_assoc($resultSelectLatest); // Corregido aquí

        if ($latestProceso) {
            $idProcesoVotacion = $latestProceso['idProcesoVotacion'];
            $sqlUpdate = "UPDATE Proceso_Votacion SET sePuedeVotar = FALSE WHERE idProcesoVotacion = '$idProcesoVotacion'";
            $resultUpdate = $db->query($sqlUpdate);

            if ($resultUpdate) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Las elecciones han sido finalizadas correctamente para el proceso actual.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se pudo finalizar las elecciones. Error en la base de datos.'
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encontraron procesos de votación para finalizar.'
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Método no permitido.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ha habido un error inesperado en el servidor: ' . $e->getMessage()
    ]);
}
?>