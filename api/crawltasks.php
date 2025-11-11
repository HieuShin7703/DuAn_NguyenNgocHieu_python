<?php
header("Content-Type: application/json");
require_once "../db.php";
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        if(isset($_GET['id'])){
            $stmt = $conn->prepare("SELECT * FROM CrawlTasks WHERE task_id=?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM CrawlTasks ORDER BY task_id DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("INSERT INTO CrawlTasks(source_id, task_name, target_url_or_keyword, crawl_type, frequency_cron, config_selectors_json, status)
                                VALUES(?,?,?,?,?,?,?)");
        $stmt->execute([
            $data->source_id, $data->task_name, $data->target_url_or_keyword,
            $data->crawl_type, $data->frequency_cron, json_encode($data->config_selectors_json),
            $data->status ?? 'Disabled'
        ]);
        echo json_encode(["message"=>"Crawl task created"]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $stmt = $conn->prepare("UPDATE CrawlTasks SET task_name=?, target_url_or_keyword=?, crawl_type=?, frequency_cron=?, config_selectors_json=?, status=? WHERE task_id=?");
        $stmt->execute([
            $data->task_name, $data->target_url_or_keyword, $data->crawl_type,
            $data->frequency_cron, json_encode($data->config_selectors_json),
            $data->status, $data->task_id
        ]);
        echo json_encode(["message"=>"Crawl task updated"]);
        break;

    case 'PATCH':
        $data = json_decode(file_get_contents("php://input"));
        if(isset($_GET['action']) && $_GET['action'] === 'run'){
            // 🔹 Tự động gọi crawl_excute.php với task_id
            $task_id = $data->task_id;
            $crawl_url = __DIR__ . "/crawl_excute.php?task_id=" . intval($task_id);

            // Dùng output buffering để lấy kết quả crawl
            ob_start();
            include $crawl_url;
            $crawl_output = ob_get_clean();

            echo json_encode([
                "message" => "Manual crawl triggered for task $task_id",
                "crawl_result" => json_decode($crawl_output, true)
            ]);
        } else {
            // Chỉ update status
            $stmt = $conn->prepare("UPDATE CrawlTasks SET status=? WHERE task_id=?");
            $stmt->execute([$data->status, $data->task_id]);
            echo json_encode(["message"=>"Task status updated"]);
        }
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $delData);
        $stmt = $conn->prepare("DELETE FROM CrawlTasks WHERE task_id=?");
        $stmt->execute([$delData['id']]);
        echo json_encode(["message"=>"Crawl task deleted"]);
        break;
}
?>
