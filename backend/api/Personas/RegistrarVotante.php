<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

// Para depuración (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido", 405);
    }

    // Obtener input JSON
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception("Datos JSON vacíos", 400);
    }

    $input = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON inválido: " . json_last_error_msg(), 400);
    }

    // Validar campos requeridos
    if (!isset($input['nombre']) || !isset($input['dni'])) {
        throw new Exception("Nombre y DNI son requeridos", 400);
    }

    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si ya existe el votante
    $stmt = $conn->prepare("SELECT idPersona, haVotadoPresidente, haVotadoDiputado, haVotadoAlcalde FROM Personas WHERE dni = ?");
    $stmt->execute([$input['dni']]);
    $votante = $stmt->fetch();

    if ($votante) {
        if ($votante['haVotadoPresidente'] || $votante['haVotadoDiputado'] || $votante['haVotadoAlcalde']) {
            throw new Exception("Esta persona ya ha votado", 400);
        }
        echo json_encode(['status' => 'success', 'idPersona' => $votante['idPersona']]);
        exit;
    }

    // Registrar nuevo votante
    $stmt = $conn->prepare("INSERT INTO Personas (dni, nombreCompleto) VALUES (?, ?)");
    $stmt->execute([$input['dni'], $input['nombre']]);
    $idPersona = $conn->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'idPersona' => $idPersona,
        'message' => 'Votante registrado exitosamente'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>