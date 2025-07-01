<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $db = Database::getInstance();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql = "SELECT idProcesoVotacion, sePuedeVotar FROM Proceso_Votacion ORDER BY idProcesoVotacion DESC LIMIT 1";
        $result = $db->query($sql);
        $proceso = mysqli_fetch_assoc($result);

        if ($proceso) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'idProcesoVotacion' => (int)$proceso['idProcesoVotacion'],
                    'sePuedeVotar' => (bool)$proceso['sePuedeVotar']
                ]
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'No se encontraron procesos de votación. Se asume que no hay elecciones activas.',
                'data' => [
                    'idProcesoVotacion' => null,
                    'sePuedeVotar' => false
                ]
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