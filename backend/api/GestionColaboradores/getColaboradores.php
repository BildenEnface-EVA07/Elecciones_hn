<?php
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */

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
    $conn = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $rowsPerPage = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        $searchText = isset($_GET['searchText']) ? trim($_GET['searchText']) : '';
        $roleFilter = isset($_GET['roleFilter']) ? trim($_GET['roleFilter']) : '';
        $sortOrder = isset($_GET['sortOrder']) ? trim($_GET['sortOrder']) : '';

        $offset = ($currentPage - 1) * $rowsPerPage;

        $whereClauses = [];
        $params = [];
        $types = '';

        if (!empty($searchText)) {
            $whereClauses[] = "(p.dni LIKE ? OR p.nombreCompleto LIKE ?)";
            $params[] = "%" . $searchText . "%";
            $params[] = "%" . $searchText . "%";
            $types .= 'ss';
        }

        if (!empty($roleFilter)) {
            $whereClauses[] = "c.rol = ?";
            $params[] = $roleFilter;
            $types .= 's';
        }

        $whereSql = '';
        if (!empty($whereClauses)) {
            $whereSql = "WHERE " . implode(" AND ", $whereClauses);
        }

        $orderBySql = "";
        if ($sortOrder === 'asc') {
            $orderBySql = "ORDER BY p.nombreCompleto ASC";
        } else {
            $orderBySql = "ORDER BY c.idAdmin DESC"; // Default sort
        }

        // Count total rows
        $countSql = "SELECT COUNT(c.idAdmin) AS totalRows 
                     FROM Colaboradores c 
                     JOIN Personas p ON c.idPersona = p.idPersona 
                     " . $whereSql;

        $stmt = mysqli_prepare($conn, $countSql);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $countResult = mysqli_stmt_get_result($stmt);
        $totalRows = mysqli_fetch_assoc($countResult)['totalRows'];
        mysqli_stmt_close($stmt);

        $totalPages = ceil($totalRows / $rowsPerPage);

        // Fetch paginated data
        $sql = "SELECT p.idPersona, p.dni, p.nombreCompleto, c.rol, c.estaActivo AS estado 
                FROM Colaboradores c 
                JOIN Personas p ON c.idPersona = p.idPersona 
                " . $whereSql . " 
                " . $orderBySql . " 
                LIMIT ?, ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        $allParams = array_merge($params, [$offset, $rowsPerPage]);
        $allTypes = $types . 'ii';
        mysqli_stmt_bind_param($stmt, $allTypes, ...$allParams);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $colaboradores = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $colaboradores[] = $row;
        }
        mysqli_stmt_close($stmt);

        echo json_encode([
            'status' => 'success',
            'data' => $colaboradores,
            'totalRows' => (int)$totalRows,
            'currentPage' => (int)$currentPage,
            'totalPages' => (int)$totalPages
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