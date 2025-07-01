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
        $db->query("UPDATE Proceso_Votacion SET sePuedeVotar = FALSE WHERE sePuedeVotar = TRUE");

        $sqlInsert = "INSERT INTO Proceso_Votacion (sePuedeVotar) VALUES (TRUE)";
        $resultInsert = $db->query($sqlInsert);

        if ($resultInsert) {
            $newId = mysqli_insert_id($db->getConnection()); // Corregido aquí
            echo json_encode([
                'status' => 'success',
                'message' => 'Un nuevo proceso de elecciones ha sido iniciado correctamente.',
                'idProcesoVotacion' => (int)$newId
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo iniciar un nuevo proceso de elecciones. Error en la base de datos.'
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