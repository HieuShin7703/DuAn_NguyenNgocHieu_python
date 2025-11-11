<?php
header("Content-Type: application/json");
require_once "../db.php";
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM Shops WHERE shop_id=?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $sql = "SELECT * FROM Shops";
            if (isset($_GET['source_id'])) {
                $sql .= " WHERE source_id=" . intval($_GET['source_id']);
            }
            $stmt = $conn->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DEL);
        $stmt = $conn->prepare("DELETE FROM Shops WHERE shop_id=?");
        $stmt->execute([$_DEL['id']]);
        echo json_encode(["message" => "Shop deleted"]);
        break;
}
?>
