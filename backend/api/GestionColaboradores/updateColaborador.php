<?php
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */

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
    $conn = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $idPersona = $input['idPersona'] ?? null;
        $nombreCompleto = $input['nombreCompleto'] ?? null;
        $rol = $input['rol'] ?? null;

        if (empty($idPersona) || empty($nombreCompleto) || empty($rol)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Todos los campos son requeridos: idPersona, nombreCompleto, rol.'
            ]);
            exit();
        }

        // Update Personas table
        $sqlPersona = "UPDATE Personas SET nombreCompleto = ? WHERE idPersona = ?";
        $stmtPersona = mysqli_prepare($conn, $sqlPersona);
        mysqli_stmt_bind_param($stmtPersona, 'si', $nombreCompleto, $idPersona);
        $resultPersona = mysqli_stmt_execute($stmtPersona);
        mysqli_stmt_close($stmtPersona);

        // Update Colaboradores table for role
        $sqlColaborador = "UPDATE Colaboradores SET rol = ? WHERE idPersona = ?";
        $stmtColaborador = mysqli_prepare($conn, $sqlColaborador);
        mysqli_stmt_bind_param($stmtColaborador, 'si', $rol, $idPersona);
        $resultColaborador = mysqli_stmt_execute($stmtColaborador);
        mysqli_stmt_close($stmtColaborador);

        if ($resultPersona && $resultColaborador) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Información del colaborador actualizada correctamente.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo actualizar la información del colaborador. Error en la base de datos.'
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