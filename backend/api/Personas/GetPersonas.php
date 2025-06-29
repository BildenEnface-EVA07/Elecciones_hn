<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $db = Database::getInstance();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $db->query("SELECT dni, nombreCompleto FROM Personas");
        $resultados = $db->fetchAll($result);
        // Caso 404
        if (empty($resultados)) {
            http_response_code(404); 
            echo json_encode([
                'status' => 'success',
                'message' => 'No se encontraron personas',
                'data' => []
            ]);
        // Caso 200
        } else {
            echo json_encode([
                'status' => 'success',
                'count' => count($resultados),
                'data' => $resultados
            ]);
        }
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ha habido un error repentino en el servidor.',
        'error' => $e->getMessage()
    ]);
}
?>