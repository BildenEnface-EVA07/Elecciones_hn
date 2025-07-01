<?php
/* 0ni_set('display_errors', 1);
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

        $idPersonas = $input['idPersonas'] ?? [];

        if (empty($idPersonas) || !is_array($idPersonas)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Se requiere un array de IDs de personas para cambiar el estado.'
            ]);
            exit();
        }

        mysqli_begin_transaction($conn);
        $success = true;
        $messages = [];

        foreach ($idPersonas as $idPersona) {
            // Get current status
            $sqlSelect = "SELECT estaActivo FROM Colaboradores WHERE idPersona = ?";
            $stmtSelect = mysqli_prepare($conn, $sqlSelect);
            mysqli_stmt_bind_param($stmtSelect, 'i', $idPersona);
            mysqli_stmt_execute($stmtSelect);
            $resultSelect = mysqli_stmt_get_result($stmtSelect);
            $colaborador = mysqli_fetch_assoc($resultSelect);
            mysqli_stmt_close($stmtSelect);

            if ($colaborador) {
                $newStatus = !$colaborador['estaActivo'];
                $statusText = $newStatus ? 'habilitado' : 'deshabilitado';

                $sqlUpdate = "UPDATE Colaboradores SET estaActivo = ? WHERE idPersona = ?";
                $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
                mysqli_stmt_bind_param($stmtUpdate, 'ii', $newStatus, $idPersona);
                $resultUpdate = mysqli_stmt_execute($stmtUpdate);
                mysqli_stmt_close($stmtUpdate);

                if ($resultUpdate) {
                    $messages[] = "Colaborador con ID " . $idPersona . " " . $statusText . ".";
                } else {
                    $success = false;
                    $messages[] = "Error al cambiar el estado del colaborador con ID " . $idPersona . ".";
                    break;
                }
            } else {
                $success = false;
                $messages[] = "Colaborador con ID " . $idPersona . " no encontrado.";
                break;
            }
        }

        if ($success) {
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success',
                'message' => implode(' ', $messages)
            ]);
        } else {
            mysqli_rollback($conn);
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error en la operación: ' . implode(' ', $messages)
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