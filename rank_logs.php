<?php
require_once 'auth_check.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDBConnection();

if ($method === 'GET') {
    $keyword_id = $_GET['keyword_id'] ?? null;
    $limit = (int)($_GET['limit'] ?? 30);

    if ($keyword_id) {
        $sql = "SELECT * FROM rank_logs WHERE keyword_id = ? ORDER BY measured_at DESC LIMIT " . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$keyword_id]);
    } else {
        $sql = "SELECT r.*, k.keyword, c.client_name FROM rank_logs r JOIN keywords k ON r.keyword_id = k.keyword_id JOIN clients c ON k.client_id = c.client_id ORDER BY r.measured_at DESC LIMIT " . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([]);
    }

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($logs);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO rank_logs (keyword_id, url, rank, url_type, measured_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$data['keyword_id'], $data['url'], $data['rank'] ?? null, $data['url_type']]);
    echo json_encode(['success' => true]);
    exit;
}
?>