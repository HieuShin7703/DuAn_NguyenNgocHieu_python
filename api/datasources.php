<?php
header("Content-Type: application/json");
require_once "../db.php";
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM DataSources WHERE source_id=?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM DataSources");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO DataSources(name, base_url, status) VALUES(?, ?, ?)");
        $stmt->execute([$data->name, $data->base_url, $data->status ?? 'Inactive']);
        echo json_encode(["message" => "DataSource added"]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("UPDATE DataSources SET name=?, base_url=?, status=? WHERE source_id=?");
        $stmt->execute([$data->name, $data->base_url, $data->status, $data->source_id]);
        echo json_encode(["message" => "DataSource updated"]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DEL);
        $stmt = $conn->prepare("DELETE FROM DataSources WHERE source_id=?");
        $stmt->execute([$_DEL['id']]);
        echo json_encode(["message" => "DataSource deleted"]);
        break;
}
?>
