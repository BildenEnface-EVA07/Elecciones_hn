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
        $query = "SELECT idCandidato, idPartido, nombreCandidato, cargo, foto FROM Candidatos";
        $result = $db->query($query);
        $candidatos = $db->fetchAll($result);

        if (empty($candidatos)) {
            http_response_code(404);
            echo json_encode([
                'status' => 'success',
                'message' => 'No se encontraron candidatos',
                'data' => []
            ]);
        } else {
            foreach ($candidatos as &$candidato) {
                if (!empty($candidato['foto'])) {
                    $candidato['foto'] = 'data:image/jpeg;base64,' . base64_encode($candidato['foto']);
                } else {
                    $candidato['foto'] = null;
                }
            }

            echo json_encode([
                'status' => 'success',
                'count' => count($candidatos),
                'data' => $candidatos
            ]);
        }
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en el servidor',
        'error' => $e->getMessage()
    ]);
}
?>
