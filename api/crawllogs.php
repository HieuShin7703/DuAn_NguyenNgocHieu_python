<?php
header("Content-Type: application/json");
require_once "../db.php";
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM CrawlLogs WHERE log_id=?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $sql = "SELECT * FROM CrawlLogs WHERE 1=1";
            if (isset($_GET['task_id'])) $sql .= " AND task_id=" . intval($_GET['task_id']);
            if (isset($_GET['status'])) $sql .= " AND status='" . $_GET['status'] . "'";
            $sql .= " ORDER BY start_time DESC";
            $stmt = $conn->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO CrawlLogs(task_id, start_time, end_time, status, records_added, records_updated, message)
                                VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data->task_id, $data->start_time, $data->end_time,
            $data->status, $data->records_added, $data->records_updated, $data->message
        ]);
        echo json_encode(["message" => "Log recorded"]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DEL);
        $stmt = $conn->prepare("DELETE FROM CrawlLogs WHERE log_id=?");
        $stmt->execute([$_DEL['id']]);
        echo json_encode(["message" => "Log deleted"]);
        break;
}
?>
