<?php
require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $sql = "SELECT idPartido, nombrePartido FROM Partidos ORDER BY nombrePartido ASC";
    $result = $db->query($sql);

    if ($result) {
        $partidos = $db->fetchAll($result);
        echo json_encode([
            'status' => 'success',
            'data' => $partidos
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener los partidos.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ha ocurrido un error en el servidor: ' . $e->getMessage()
    ]);
}
?>