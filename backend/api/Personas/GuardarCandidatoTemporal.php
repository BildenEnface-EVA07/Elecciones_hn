<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['idCandidato'], $input['cargo'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    $cargo = $input['cargo'];
    $idCandidato = intval($input['idCandidato']);

    if (!isset($_SESSION['votos_temporales'])) {
        $_SESSION['votos_temporales'] = [];
    }

    $_SESSION['votos_temporales'][$cargo] = $idCandidato;

    echo json_encode(['status' => 'success', 'message' => 'Candidato guardado temporalmente']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
