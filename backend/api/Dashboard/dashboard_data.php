<?php

require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $db = Database::getInstance();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        // Get the ID of the latest active voting process
        $sqlLatestProceso = "SELECT idProcesoVotacion FROM Proceso_Votacion WHERE sePuedeVotar = TRUE ORDER BY idProcesoVotacion DESC LIMIT 1";
        $resultLatestProceso = $db->query($sqlLatestProceso);
        $latestProceso = mysqli_fetch_assoc($resultLatestProceso);

        $currentProcesoVotacionId = null;
        if ($latestProceso) {
            $currentProcesoVotacionId = $latestProceso['idProcesoVotacion'];
        }

        if ($action === 'votos24h') {
            if ($currentProcesoVotacionId === null) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'No hay elecciones activas para mostrar votos.', 'data' => []]);
                exit();
            }

            $sql = "
                SELECT
                    p.nombrePartido,
                    COUNT(v.idVoto) AS totalVotos
                FROM Votaciones_Votos v
                JOIN Candidatos c ON v.idCandidato = c.idCandidato
                JOIN Partidos p ON c.idPartido = p.idPartido
                WHERE v.fecha >= NOW() - INTERVAL 24 HOUR
                AND v.idProcesoVotacion = '$currentProcesoVotacionId'
                GROUP BY p.nombrePartido
                ORDER BY totalVotos DESC;
            ";
            $result = $db->query($sql);
            $votos24h = $db->fetchAll($result);

            if (empty($votos24h)) {
                http_response_code(404);
                echo json_encode(['status' => 'success', 'message' => 'No hay votos en las últimas 24 horas para la elección actual.', 'data' => []]);
            } else {
                echo json_encode(['status' => 'success', 'data' => $votos24h]);
            }
        } elseif ($action === 'votosCandidatura') {
            if ($currentProcesoVotacionId === null) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'No hay elecciones activas para mostrar datos de candidaturas.', 'data' => []]);
                exit();
            }

            $sql = "
                SELECT
                    c.cargo,
                    p.nombrePartido,
                    COUNT(v.idVoto) AS totalVotos
                FROM Votaciones_Votos v
                JOIN Candidatos c ON v.idCandidato = c.idCandidato
                JOIN Partidos p ON c.idPartido = p.idPartido
                WHERE v.idProcesoVotacion = '$currentProcesoVotacionId'
                GROUP BY c.cargo, p.nombrePartido
                ORDER BY c.cargo, p.nombrePartido;
            ";
            $result = $db->query($sql);
            $votosCandidatura = $db->fetchAll($result);
            $formattedData = [];
            $cargos = ['Diputado', 'Alcalde', 'Presidente'];
            $partidos = ['Partido Libre', 'Partido Nacional', 'Partido Liberal'];

            foreach ($cargos as $cargo) {
                foreach ($partidos as $partido) {
                    $votos = 0;
                    foreach ($votosCandidatura as $row) {
                        if ($row['cargo'] === $cargo && $row['nombrePartido'] === $partido) {
                            $votos = (int)$row['totalVotos'];
                            break;
                        }
                    }
                    $formattedData[] = [
                        'cargo' => $cargo,
                        'partido' => $partido,
                        'totalVotos' => $votos
                    ];
                }
            }

            if (empty($formattedData)) {
                 http_response_code(404);
                 echo json_encode(['status' => 'success', 'message' => 'No hay datos de candidaturas para la elección actual.', 'data' => []]);
            } else {
                echo json_encode(['status' => 'success', 'data' => $formattedData]);
            }

        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>