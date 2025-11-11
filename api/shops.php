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

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO Shops(source_id, platform_shop_id, shop_name, shop_url) VALUES(?, ?, ?, ?)");
        $stmt->execute([$data->source_id, $data->platform_shop_id, $data->shop_name, $data->shop_url]);
        echo json_encode(["message" => "Shop added"]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("UPDATE Shops SET shop_name=?, shop_url=? WHERE shop_id=?");
        $stmt->execute([$data->shop_name, $data->shop_url, $data->shop_id]);
        echo json_encode(["message" => "Shop updated"]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DEL);
        $stmt = $conn->prepare("DELETE FROM Shops WHERE shop_id=?");
        $stmt->execute([$_DEL['id']]);
        echo json_encode(["message" => "Shop deleted"]);
        break;
}
?>
