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
        $query = "
            SELECT 
                C.idCandidato,
                C.idPartido,
                C.nombreCandidato,
                CA.idMunicipio,
                M.nombre AS nombreMunicipio
            FROM Candidatos C
            INNER JOIN CandidatosAlcaldes CA ON C.idCandidato = CA.idCandidato
            INNER JOIN Municipios M ON CA.idMunicipio = M.idMunicipio
            WHERE C.cargo = 'Alcalde'
        ";

        $result = $db->query($query);
        $alcaldes = $db->fetchAll($result);

        if (empty($alcaldes)) {
            http_response_code(404);
            echo json_encode([
                'status' => 'success',
                'message' => 'No se encontraron candidatos a alcalde',
                'data' => []
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'count' => count($alcaldes),
                'data' => $alcaldes
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
