<?php
require_once '../../config/Database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Parámetros de filtro
    $partyId = isset($_GET['partyId']) ? (int)$_GET['partyId'] : null;
    $cargo = isset($_GET['cargo']) ? $_GET['cargo'] : null;
    // Parámetros de paginación
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8; // 8 filas 
    $offset = ($page - 1) * $limit;

    // consulta SQL base
    $sql = "
        SELECT
            P.nombrePartido,
            C.nombreCandidato,
            C.cargo,
            COALESCE(D.nombre, 'N/A') AS nombreDepartamento,
            COALESCE(M.nombre, 'N/A') AS nombreMunicipio
        FROM Votaciones_Votos AS V
        JOIN Candidatos AS C ON V.idCandidato = C.idCandidato
        JOIN Partidos AS P ON C.idPartido = P.idPartido
        LEFT JOIN CandidatosDiputados AS CD ON C.idCandidato = CD.idCandidato
        LEFT JOIN Departamentos AS D ON CD.idDepartamento = D.idDepartamento
        LEFT JOIN CandidatosAlcaldes AS CA ON C.idCandidato = CA.idCandidato
        LEFT JOIN Municipios AS M ON CA.idMunicipio = M.idMunicipio
    ";

    $conditions = [];
    $params = [];
    $paramTypes = "";
    if ($partyId) {
        $conditions[] = "P.idPartido = ?";
        $params[] = $partyId;
        $paramTypes .= "i";
    }
    if ($cargo && $cargo !== 'Todos') { 
        $conditions[] = "C.cargo = ?";
        $params[] = $cargo;
        $paramTypes .= "s";
    }
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $countSql = "SELECT COUNT(DISTINCT V.idVoto) AS totalRows FROM Votaciones_Votos AS V
                 JOIN Candidatos AS C ON V.idCandidato = C.idCandidato
                 JOIN Partidos AS P ON C.idPartido = P.idPartido";
    // Si hay condiciones
    if (!empty($conditions)) {
        $countSql .= " WHERE " . implode(" AND ", $conditions);
    }
    $stmtCount = $conn->prepare($countSql);
    if (!empty($conditions)) {
        $stmtCount->bind_param($paramTypes, ...$params);
    }
    $stmtCount->execute();
    $countResult = $stmtCount->get_result();
    $totalRows = $countResult->fetch_assoc()['totalRows'];
    $stmtCount->close();
    // Añadir paginación a la consulta principal
    $sql .= " ORDER BY V.fecha DESC LIMIT ? OFFSET ?"; // Ordenar por fecha 
    $params[] = $limit;
    $params[] = $offset;
    $paramTypes .= "ii";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }
    // Vincular parámetros dinámicamente
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $db->fetchAll($result);
    $stmt->close();
    // Calcular el total de páginas
    $totalPages = ceil($totalRows / $limit);

    if (empty($data)) {
        http_response_code(404);
        echo json_encode([
            'status' => 'success',
            'message' => 'No hay datos basados en esos criterios.',
            'data' => [],
            'totalRows' => 0,
            'totalPages' => 0,
            'currentPage' => $page
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'Votos obtenidos correctamente.',
            'data' => $data,
            'totalRows' => $totalRows,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ha ocurrido un error en el servidor: ' . $e->getMessage()
    ]);
}
?>