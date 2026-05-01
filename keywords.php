<?php
require_once 'auth_check.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDBConnection();

// キーワード一覧取得
if ($method === 'GET') {
    $client_id = $_GET['client_id'] ?? null;
    if ($client_id) {
        $stmt = $pdo->prepare("
            SELECT k.*, GROUP_CONCAT(CONCAT(w.url, '|', w.url_type) SEPARATOR ',,') as urls
            FROM keywords k
            LEFT JOIN watch_urls w ON k.keyword_id = w.keyword_id
            WHERE k.client_id = ?
            GROUP BY k.keyword_id
            ORDER BY k.created_at ASC
        ");
        $stmt->execute([$client_id]);
    } else {
        $stmt = $pdo->query("
            SELECT k.*, GROUP_CONCAT(CONCAT(w.url, '|', w.url_type) SEPARATOR ',,') as urls
            FROM keywords k
            LEFT JOIN watch_urls w ON k.keyword_id = w.keyword_id
            GROUP BY k.keyword_id
            ORDER BY k.created_at ASC
        ");
    }
    $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($keywords);
    exit;
}

// キーワード追加
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $keyword_id = 'kw_' . time() . rand(100, 999);
    
    $stmt = $pdo->prepare("INSERT INTO keywords (keyword_id, client_id, keyword) VALUES (?, ?, ?)");
    $stmt->execute([$keyword_id, $data['client_id'], $data['keyword']]);
    
    // 監視URLの登録
    if (!empty($data['urls'])) {
        foreach ($data['urls'] as $url_data) {
            $stmt = $pdo->prepare("INSERT INTO watch_urls (keyword_id, url, url_type) VALUES (?, ?, ?)");
            $stmt->execute([$keyword_id, $url_data['url'], $url_data['url_type']]);
        }
    }
    
    echo json_encode(['success' => true, 'keyword_id' => $keyword_id]);
    exit;
}

// キーワード削除
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM watch_urls WHERE keyword_id = ?");
    $stmt->execute([$data['keyword_id']]);
    $stmt = $pdo->prepare("DELETE FROM keywords WHERE keyword_id = ?");
    $stmt->execute([$data['keyword_id']]);
    echo json_encode(['success' => true]);
    exit;
}
?>