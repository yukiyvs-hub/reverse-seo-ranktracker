<?php
require_once 'auth_check.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDBConnection();

// クライアント一覧取得
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY created_at DESC");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($clients);
    exit;
}

// クライアント追加
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $client_id = 'client_' . time() . rand(100, 999);
    $stmt = $pdo->prepare("INSERT INTO clients (client_id, client_name) VALUES (?, ?)");
    $stmt->execute([$client_id, $data['client_name']]);
    echo json_encode(['success' => true, 'client_id' => $client_id]);
    exit;
}

// クライアント削除
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM clients WHERE client_id = ?");
    $stmt->execute([$data['client_id']]);
    echo json_encode(['success' => true]);
    exit;
}
?>