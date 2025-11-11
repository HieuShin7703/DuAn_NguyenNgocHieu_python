<?php
header("Content-Type: application/json");
require_once "../db.php";
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['product_id'])) {
            $stmt = $conn->prepare("SELECT * FROM ProductHistory WHERE product_id=? ORDER BY scraped_at ASC");
            $stmt->execute([$_GET['product_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM ProductHistory");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO ProductHistory(product_id, price, original_price, sales_count, review_count)
                                VALUES(?, ?, ?, ?, ?)");
        $stmt->execute([$data->product_id, $data->price, $data->original_price, $data->sales_count, $data->review_count]);
        echo json_encode(["message" => "History added"]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DEL);
        $stmt = $conn->prepare("DELETE FROM ProductHistory WHERE history_id=?");
        $stmt->execute([$_DEL['id']]);
        echo json_encode(["message" => "History deleted"]);
        break;
}
?>
