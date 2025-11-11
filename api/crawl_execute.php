<?php
require_once "../db.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_GET['task_id'])) {
    echo json_encode(["error" => "Thiếu task_id"]);
    exit;
}

$task_id = intval($_GET['task_id']);

// 1️⃣ Lấy thông tin task
$stmt = $conn->prepare("SELECT * FROM CrawlTasks WHERE task_id=?"); 
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    echo json_encode(["error" => "Không tìm thấy task"]);
    exit;
}

$start_time = date('Y-m-d H:i:s');
$records_added = 0;
$records_updated = 0;
$message = "";

try {
    $target = $task['target_url_or_keyword'];

    // 🔹 Nếu target là URL, parse keyword
    if (filter_var($target, FILTER_VALIDATE_URL)) {
        $url_parts = parse_url($target);
        parse_str($url_parts['query'] ?? '', $query_params);
        $keyword = urlencode($query_params['keyword'] ?? '');
    } else {
        $keyword = urlencode($target);
    }

    $url = "https://shopee.vn/api/v4/search/search_items?by=relevancy&keyword={$keyword}&limit=10&newest=0&order=desc&page_type=search";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
            'X-Requested-With: XMLHttpRequest',
            'Referer: https://shopee.vn/',
        ],
        // 🔹 Thay cookie thật của bạn
        CURLOPT_COOKIE => 'SPC_CDS=XXX; SPC_R_T_ID=YYY; SPC_T_ID=ZZZ;', 
        CURLOPT_ENCODING => 'gzip, deflate',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        throw new Exception("cURL error: " . curl_error($ch));
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200 || !$response) {
        throw new Exception("Shopee trả về lỗi HTTP $httpCode hoặc không có dữ liệu.");
    }

    // 🔹 Lưu response để debug
    file_put_contents('shopee_debug.json', $response);

    $data = json_decode($response, true);
    if (!$data || !isset($data['items'])) {
        throw new Exception("Không lấy được dữ liệu từ Shopee (JSON sai định dạng).");
    }

    foreach ($data['items'] as $item) {
        $product = $item['item_basic'] ?? [];

        $platform_id = $product['itemid'] ?? null;
        $name = $product['name'] ?? '';
        $price = isset($product['price']) ? $product['price'] / 100 : 0;
        $price_before = isset($product['price_before_discount']) ? $product['price_before_discount'] / 100 : 0;
        $image = isset($product['image']) ? "https://down-vn.img.susercontent.com/file/" . $product['image'] : '';
        $url_product = isset($product['shopid'], $product['itemid']) ? "https://shopee.vn/product/{$product['shopid']}/{$product['itemid']}" : '';
        $rating = $product['item_rating']['rating_star'] ?? 0;
        $review_count = $product['item_rating']['rating_count'][0] ?? 0;
        $sales = $product['historical_sold'] ?? 0;

        // 🔹 Tìm shop_id tương ứng
        $shop_stmt = $conn->prepare("SELECT shop_id FROM Shops WHERE platform_shop_id=?");
        $shop_stmt->execute([$product['shopid'] ?? 0]);
        $shop_row = $shop_stmt->fetch(PDO::FETCH_ASSOC);
        $shop_id = $shop_row ? $shop_row['shop_id'] : null;

        // 🔹 Chèn hoặc cập nhật
        $stmt2 = $conn->prepare("
            INSERT INTO Products 
            (source_id, shop_id, platform_product_id, name, product_url, image_url, current_price, original_price, average_rating, review_count, sales_count, last_scraped_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                current_price = VALUES(current_price),
                average_rating = VALUES(average_rating),
                review_count = VALUES(review_count),
                sales_count = VALUES(sales_count),
                last_scraped_at = NOW()
        ");
        $stmt2->execute([$task['source_id'], $shop_id, $platform_id, $name, $url_product, $image, $price, $price_before, $rating, $review_count, $sales]);

        if ($stmt2->rowCount() == 1) $records_added++;
        else $records_updated++;

        // 🔹 Ghi lịch sử giá
        $product_id = $conn->lastInsertId();
        if (!$product_id) {
            $p_stmt = $conn->prepare("SELECT product_id FROM Products WHERE platform_product_id=? AND source_id=?");
            $p_stmt->execute([$platform_id, $task['source_id']]);
            $product_id = $p_stmt->fetchColumn();
        }

        $conn->prepare("
            INSERT INTO ProductHistory (product_id, price, original_price, sales_count, review_count)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$product_id, $price, $price_before, $sales, $review_count]);
    }

    $status = 'Success';
    $message = "✅ Cào thành công {$records_added} mới, cập nhật {$records_updated} sản phẩm.";

} catch (Exception $e) {
    $status = 'Failed';
    $message = "Lỗi: " . $e->getMessage();
}

// 3️⃣ Lưu log
$end_time = date('Y-m-d H:i:s');
$log_stmt = $conn->prepare("INSERT INTO CrawlLogs(task_id, start_time, end_time, status, records_added, records_updated, message)
                            VALUES(?, ?, ?, ?, ?, ?, ?)");
$log_stmt->execute([$task_id, $start_time, $end_time, $status, $records_added, $records_updated, $message]);

echo json_encode([
    "status" => $status,
    "message" => $message,
    "added" => $records_added,
    "updated" => $records_updated
], JSON_UNESCAPED_UNICODE);
