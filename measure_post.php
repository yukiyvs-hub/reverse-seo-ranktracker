<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
$filterKeywordId = $input['keywordId'] ?? null;

if ($filterKeywordId) {
    $stmt = $pdo->prepare("SELECT keyword_id, keyword FROM keywords WHERE keyword_id = ?");
    $stmt->execute([$filterKeywordId]);
    $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $keywords = $pdo->query("SELECT keyword_id, keyword FROM keywords")->fetchAll(PDO::FETCH_ASSOC);
    
}

$posted = [];

foreach ($keywords as $kw) {
    $url = 'https://api.dataforseo.com/v3/serp/google/organic/task_post';
    $data = [[
        'keyword' => $kw['keyword'],
        'location_code' => 2392,
        'language_code' => 'ja',
        'device' => 'desktop',
        'os' => 'windows',
        'depth' => 50
    ]];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERPWD, DATAFORSEO_LOGIN . ':' . DATAFORSEO_PASSWORD);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    if (($result['status_code'] ?? 0) === 20000) {
        $task_id = $result['tasks'][0]['id'];
        $stmt = $pdo->prepare("INSERT INTO task_queue (task_id, keyword_id) VALUES (?, ?)");
        $stmt->execute([$task_id, $kw['keyword_id']]);
        $posted[] = ['keyword' => $kw['keyword'], 'task_id' => $task_id];
    }
}

echo json_encode(['success' => true, 'posted' => $posted]);
?>