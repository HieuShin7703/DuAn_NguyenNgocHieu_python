<?php
header("Content-Type: application/json");
require_once "../db.php";
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM Products WHERE product_id=?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            // lọc nâng cao
            $sql = "SELECT * FROM Products WHERE 1=1";
            if (isset($_GET['source_id'])) $sql .= " AND source_id=" . intval($_GET['source_id']);
            if (isset($_GET['shop_id'])) $sql .= " AND shop_id=" . intval($_GET['shop_id']);
            if (isset($_GET['keyword'])) $sql .= " AND name LIKE '%" . $_GET['keyword'] . "%'";
            if (isset($_GET['minPrice'])) $sql .= " AND current_price >= " . floatval($_GET['minPrice']);
            if (isset($_GET['maxPrice'])) $sql .= " AND current_price <= " . floatval($_GET['maxPrice']);
            $stmt = $conn->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO Products(source_id, shop_id, platform_product_id, name, product_url, image_url, current_price, original_price, average_rating, review_count, sales_count)
                                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data->source_id, $data->shop_id, $data->platform_product_id, 
            $data->name, $data->product_url, $data->image_url,
            $data->current_price, $data->original_price,
            $data->average_rating, $data->review_count, $data->sales_count
        ]);
        echo json_encode(["message" => "Product added"]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("UPDATE Products SET name=?, current_price=?, original_price=?, average_rating=?, review_count=?, sales_count=? WHERE product_id=?");
        $stmt->execute([
            $data->name, $data->current_price, $data->original_price, 
            $data->average_rating, $data->review_count, $data->sales_count,
            $data->product_id
        ]);
        echo json_encode(["message" => "Product updated"]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DEL);
        $stmt = $conn->prepare("DELETE FROM Products WHERE product_id=?");
        $stmt->execute([$_DEL['id']]);
        echo json_encode(["message" => "Product deleted"]);
        break;
}
?>
