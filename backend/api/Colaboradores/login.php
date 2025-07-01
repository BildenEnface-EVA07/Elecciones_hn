<?php
require_once '../../Database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data["correo_electronico"] ?? "";
    $password = $data["contrasena"] ?? "";

    if (empty($email) || empty($password)) {
        echo json_encode(["message" => "Correo electrónico y contraseña son requeridos."]);
        http_response_code(400);
        exit();
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, contrasena FROM Colaboradores WHERE correo_electronico = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            echo json_encode(["message" => "Inicio de sesión exitoso.", "id" => $id]);
            http_response_code(200);
        } else {
            echo json_encode(["message" => "Contraseña incorrecta."]);
            http_response_code(401);
        }
    } else {
        echo json_encode(["message" => "Correo electrónico no encontrado."]);
        http_response_code(404);
    }

    $stmt->close();

} else {
    echo json_encode(["message" => "Método de solicitud no permitido."]);
    http_response_code(405);
}
?>