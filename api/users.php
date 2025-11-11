<?php
header("Content-Type: application/json");
require_once "../db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST': // register or login
        $data = json_decode(file_get_contents("php://input"));
        if (isset($data->action) && $data->action === 'login') {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE username=?");
            $stmt->execute([$data->username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($data->password, $user['password_hash'])) {
                echo json_encode(["message" => "Login success", "user" => $user]);
            } else {
                http_response_code(401);
                echo json_encode(["error" => "Invalid credentials"]);
            }
        } else { // register
            $hash = password_hash($data->password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Users(username, password_hash, email) VALUES(?, ?, ?)");
            $stmt->execute([$data->username, $hash, $data->email]);
            echo json_encode(["message" => "User registered"]);
        }
        break;

    case 'GET': // list or detail
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id=?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT user_id, username, email, role, created_at FROM Users");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("UPDATE Users SET email=?, role=? WHERE user_id=?");
        $stmt->execute([$data->email, $data->role, $data->user_id]);
        echo json_encode(["message" => "User updated"]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DEL);
        $stmt = $conn->prepare("DELETE FROM Users WHERE user_id=?");
        $stmt->execute([$_DEL['id']]);
        echo json_encode(["message" => "User deleted"]);
        break;
}
?>
