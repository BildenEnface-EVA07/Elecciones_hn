<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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

        $sqlProcesoActivo = "SELECT idProcesoVotacion FROM Proceso_Votacion WHERE sePuedeVotar = TRUE ORDER BY idProcesoVotacion DESC LIMIT 1";
        $resultProcesoActivo = $db->query($sqlProcesoActivo);
        $procesoActivo = mysqli_fetch_assoc($resultProcesoActivo);

        $activeProcesoVotacionId = null;
        if ($procesoActivo) {
            $activeProcesoVotacionId = (int)$procesoActivo['idProcesoVotacion'];
        }

        $resultados = [];
        $totalRows = 0;
        $totalPages = 1;

        if ($activeProcesoVotacionId !== null) {
            $whereClause = "vv.idProcesoVotacion = $activeProcesoVotacionId";
            $params = [];

            if (!empty($partyId)) {
                $whereClause .= " AND pa.idPartido = ?";
                $params[] = $partyId;
            }
            if (!empty($cargo)) {
                $whereClause .= " AND c.cargo = ?";
                $params[] = $cargo;
            }

            $sqlCount = "
                SELECT COUNT(vv.idVoto) AS total
                FROM Votaciones_Votos vv
                JOIN Candidatos c ON vv.idCandidato = c.idCandidato
                JOIN Partidos pa ON c.idPartido = pa.idPartido
                WHERE " . $whereClause;

            $stmtCount = mysqli_prepare($db->getConnection(), $sqlCount);
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                mysqli_stmt_bind_param($stmtCount, $types, ...$params);
            }
            mysqli_stmt_execute($stmtCount);
            $resultCount = mysqli_stmt_get_result($stmtCount);
            $totalRows = mysqli_fetch_assoc($resultCount)['total'];
            mysqli_stmt_close($stmtCount);

            $totalPages = ceil($totalRows / $rowsPerPage);
            $offset = ($currentPage - 1) * $rowsPerPage;

            $sql = "
                SELECT 
                    pa.nombrePartido,
                    c.nombreCandidato,
                    c.cargo,
                    d.nombre AS nombreDepartamento,
                    m.nombre AS nombreMunicipio,
                    COUNT(vv.idVoto) AS totalVotos
                FROM 
                    Votaciones_Votos vv
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
                    " . $whereClause . "
                GROUP BY
                    pa.nombrePartido, c.nombreCandidato, c.cargo, d.nombre, m.nombre
                ORDER BY 
                    vv.fecha DESC
                LIMIT ?, ?";
            
            $stmt = mysqli_prepare($db->getConnection(), $sql);
            $types = '';
            if (!empty($params)) {
                $types .= str_repeat('s', count($params));
            }
            $types .= 'ii';
            $allParams = array_merge($params, [$offset, $rowsPerPage]);
            mysqli_stmt_bind_param($stmt, $types, ...$allParams);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $resultados = $db->fetchAll($result);
            mysqli_stmt_close($stmt);

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