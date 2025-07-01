<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['diputados']) || !is_array($input['diputados']) || count($input['diputados']) !== 20) {
    echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar exactamente 20 diputados.']);
    exit;
}

$_SESSION['votos_temporales']['Diputados'] = $input['diputados'];

echo json_encode(['status' => 'success']);
