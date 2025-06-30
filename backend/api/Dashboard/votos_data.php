<?php
require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $db = Database::getInstance();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $rowsPerPage = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        $partyId = isset($_GET['partyId']) ? $_GET['partyId'] : '';
        $cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';

        // 1. Obtener el idProcesoVotacion del proceso activo más reciente
        $sqlProcesoActivo = "SELECT idProcesoVotacion FROM Proceso_Votacion WHERE sePuedeVotar = TRUE ORDER BY idProcesoVotacion DESC LIMIT 1";
        $resultProcesoActivo = $db->query($sqlProcesoActivo);
        $procesoActivo = $db->fetchAssoc($resultProcesoActivo);

        $activeProcesoVotacionId = null;
        if ($procesoActivo) {
            $activeProcesoVotacionId = (int)$procesoActivo['idProcesoVotacion'];
        }

        $resultados = [];
        $totalRows = 0;
        $totalPages = 1;

        if ($activeProcesoVotacionId !== null) {
            $whereClause = "vv.idProcesoVotacion = '$activeProcesoVotacionId'";

            if (!empty($partyId)) {
                $whereClause .= " AND c.idPartido = '$partyId'";
            }
            if (!empty($cargo)) {
                $whereClause .= " AND c.cargo = '$cargo'";
            }

            // Consulta para el total de filas
            $sqlCount = "
                SELECT COUNT(vv.idVoto) AS total
                FROM Votaciones_Votos vv
                JOIN Candidatos c ON vv.idCandidato = c.idCandidato
                WHERE $whereClause
            ";
            $countResult = $db->query($sqlCount);
            $totalRows = $db->fetchAssoc($countResult)['total'];
            $totalPages = ceil($totalRows / $rowsPerPage);
            if ($totalPages == 0) $totalPages = 1;

            // Asegurar que currentPage no exceda totalPages
            if ($currentPage < 1) $currentPage = 1;
            if ($currentPage > $totalPages) $currentPage = $totalPages;

            $offset = ($currentPage - 1) * $rowsPerPage;

            // Consulta principal para obtener los votos
            $sql = "
                SELECT 
                    vv.idVoto, 
                    p.nombreCompleto AS nombrePersona,
                    c.nombreCandidato, 
                    c.cargo, 
                    pa.nombrePartido,
                    d.nombre AS nombreDepartamento,
                    m.nombre AS nombreMunicipio,
                    vv.fecha
                FROM 
                    Votaciones_Votos vv
                JOIN 
                    Personas p ON vv.idPersona = p.idPersona
                JOIN 
                    Candidatos c ON vv.idCandidato = c.idCandidato
                JOIN 
                    Partidos pa ON c.idPartido = pa.idPartido
                LEFT JOIN 
                    CandidatosDiputados cd ON c.idCandidato = cd.idCandidato
                LEFT JOIN 
                    Departamentos d ON cd.idDepartamento = d.idDepartamento
                LEFT JOIN 
                    CandidatosAlcaldes ca ON c.idCandidato = ca.idCandidato
                LEFT JOIN 
                    Municipios m ON ca.idMunicipio = m.idMunicipio
                WHERE 
                    $whereClause
                ORDER BY 
                    vv.fecha DESC
                LIMIT $offset, $rowsPerPage
            ";
            $result = $db->query($sql);
            $resultados = $db->fetchAll($result);
        }

        echo json_encode([
            'status' => 'success',
            'count' => count($resultados),
            'totalRows' => (int)$totalRows,
            'currentPage' => (int)$currentPage,
            'totalPages' => (int)$totalPages,
            'data' => $resultados
        ]);
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