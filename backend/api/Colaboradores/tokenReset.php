<?php
require_once '../../Database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data["correo_electronico"] ?? "";
    $reset_token_hash = $data["reset_token_hash"] ?? "";
    $reset_token_expira = $data["reset_token_expira"] ?? "";

    if (empty($email) || empty($reset_token_hash) || empty($reset_token_expira)) {
        echo json_encode(["message" => "Todos los campos son requeridos: correo_electronico, reset_token_hash, reset_token_expira."]);
        http_response_code(400);
        exit();
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("UPDATE Colaboradores SET reset_token_hash = ?, reset_token_expira = ? WHERE correo_electronico = ?");
    $stmt->bind_param("sss", $reset_token_hash, $reset_token_expira, $email);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "Token de reseteo actualizado exitosamente."]);
            http_response_code(200);
        } else {
            echo json_encode(["message" => "Correo electrónico no encontrado o no se realizaron cambios."]);
            http_response_code(404);
        }
    } else {
        echo json_encode(["message" => "Error al actualizar el token: " . $stmt->error]);
        http_response_code(500);
    }

    $stmt->close();

} else {
    echo json_encode(["message" => "Método de solicitud no permitido."]);
    http_response_code(405);
}
?>