<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        $dni = $input['dni'] ?? null;
        $nombreCompleto = $input['nombreCompleto'] ?? null;
        $correoElectronico = $input['correoElectronico'] ?? null;
        $rol = $input['rol'] ?? null;
        $clave = $input['clave'] ?? null;
        $errors = [];
        if (empty($dni) || !preg_match('/^\d{13}$/', $dni)) {
            $errors['dni'] = 'El DNI debe contener exactamente 13 dígitos numéricos.';
        } else {
            $sqlCheckDni = "SELECT COUNT(*) FROM Personas WHERE dni = ?";
            $stmtCheckDni = mysqli_prepare($conn, $sqlCheckDni);
            mysqli_stmt_bind_param($stmtCheckDni, 's', $dni);
            mysqli_stmt_execute($stmtCheckDni);
            mysqli_stmt_bind_result($stmtCheckDni, $dniCount);
            mysqli_stmt_fetch($stmtCheckDni);
            mysqli_stmt_close($stmtCheckDni);
            if ($dniCount > 0) {
                $errors['dni'] = 'El DNI ya está registrado.';
            }
        }
        if (empty($nombreCompleto)) {
            $errors['nombreCompleto'] = 'El nombre completo es requerido.';
        }
        if (empty($correoElectronico) || !filter_var($correoElectronico, FILTER_VALIDATE_EMAIL)) {
            $errors['correoElectronico'] = 'El correo electrónico no es válido.';
        } else {
            $sqlCheckEmail = "SELECT COUNT(*) FROM Colaboradores WHERE correoElectronico = ?";
            $stmtCheckEmail = mysqli_prepare($conn, $sqlCheckEmail);
            mysqli_stmt_bind_param($stmtCheckEmail, 's', $correoElectronico);
            mysqli_stmt_execute($stmtCheckEmail);
            mysqli_stmt_bind_result($stmtCheckEmail, $emailCount);
            mysqli_stmt_fetch($stmtCheckEmail);
            mysqli_stmt_close($stmtCheckEmail);
            if ($emailCount > 0) {
                $errors['correoElectronico'] = 'El correo electrónico ya está registrado.';
            }
        }
        if (empty($rol)) {
            $errors['rol'] = 'El rol es requerido.';
        }
        if (empty($clave)) {
            $errors['clave'] = 'La contraseña es requerida.';
        }
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Errores de validación', 'errors' => $errors]);
            exit();
        }
        mysqli_begin_transaction($conn);
        try {
            $sqlInsertPersona = "INSERT INTO Personas (dni, nombreCompleto, haVotadoPresidente, haVotadoAlcalde, haVotadoDiputado) VALUES (?, ?, FALSE, FALSE, FALSE)";
            $stmtPersona = mysqli_prepare($conn, $sqlInsertPersona);
            mysqli_stmt_bind_param($stmtPersona, 'ss', $dni, $nombreCompleto);
            mysqli_stmt_execute($stmtPersona);
            $idPersona = mysqli_insert_id($conn);
            mysqli_stmt_close($stmtPersona);
            if (!$idPersona) {
                throw new Exception('Failed to insert into Personas table.');
            }
            $hashedClave = password_hash($clave, PASSWORD_DEFAULT);
            $sqlInsertColaborador = "INSERT INTO Colaboradores (idPersona, correoElectronico, clave, rol, estaActivo) VALUES (?, ?, ?, ?, TRUE)";
            $stmtColaborador = mysqli_prepare($conn, $sqlInsertColaborador);
            mysqli_stmt_bind_param($stmtColaborador, 'isss', $idPersona, $correoElectronico, $hashedClave, $rol);
            mysqli_stmt_execute($stmtColaborador);
            $idAdmin = mysqli_insert_id($conn);
            mysqli_stmt_close($stmtColaborador);
            if (!$idAdmin) {
                throw new Exception('Failed to insert into Colaboradores table.');
            }
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success',
                'message' => 'Colaborador agregado correctamente.'
            ]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al agregar colaborador: ' . $e->getMessage()
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