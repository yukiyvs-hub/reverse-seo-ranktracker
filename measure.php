<?php
require_once 'auth_check.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
set_time_limit(300);

require_once 'config.php';

function normalizeUrl($url) {
    try {
        if (!preg_match('/^https?:\/\//', $url)) $url = 'https://' . $url;
        $parsed = parse_url($url);
        $host = strtolower(preg_replace('/^www\./', '', $parsed['host'] ?? ''));
        $path = strtolower(rtrim($parsed['path'] ?? '', '/'));
        return $host . $path;
    } catch (Exception $e) {
        return strtolower($url);
    }
}

function postTask($keyword) {
    $url = 'https://api.dataforseo.com/v3/serp/google/organic/task_post';
    $data = [[
        'keyword' => $keyword,
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
        return $result['tasks'][0]['id'];
    }
    return null;
}

function getTaskResult($task_id) {
    $url = 'https://api.dataforseo.com/v3/serp/google/organic/task_get/advanced/' . $task_id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, DATAFORSEO_LOGIN . ':' . DATAFORSEO_PASSWORD);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
$filterKeywordId = $input['keywordId'] ?? null;

// キーワードと監視URLを取得
if ($filterKeywordId) {
    $stmt = $pdo->prepare("
        SELECT k.keyword_id, k.keyword, w.url, w.url_type
        FROM keywords k
        JOIN watch_urls w ON k.keyword_id = w.keyword_id
        WHERE k.keyword_id = ?
    ");
    $stmt->execute([$filterKeywordId]);
} else {
    $stmt = $pdo->query("
        SELECT k.keyword_id, k.keyword, w.url, w.url_type
        FROM keywords k
        JOIN watch_urls w ON k.keyword_id = w.keyword_id
    ");
}

$targets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$keywords = [];
foreach ($targets as $target) {
    $keywords[$target['keyword_id']]['keyword'] = $target['keyword'];
    $keywords[$target['keyword_id']]['urls'][] = [
        'url' => $target['url'],
        'url_type' => $target['url_type']
    ];
}

// Step1: 全キーワードのタスクを一気に投稿
$taskMap = [];
foreach ($keywords as $keyword_id => $data) {
    $task_id = postTask($data['keyword']);
    if ($task_id) $taskMap[$keyword_id] = $task_id;
}

// Step2: 60秒待機
sleep(60);

// Step3: 結果を取得してDBに保存
$results = [];
foreach ($taskMap as $keyword_id => $task_id) {
    $items = [];
    for ($i = 0; $i < 4; $i++) {
        $result = getTaskResult($task_id);
        $status = $result['tasks'][0]['status_code'] ?? 0;
        if ($status === 20000) {
            $items = $result['tasks'][0]['result'][0]['items'] ?? [];
            break;
        }
        sleep(30);
    }

    foreach ($keywords[$keyword_id]['urls'] as $url_data) {
        $rank = null;
        $normalizedTarget = normalizeUrl($url_data['url']);
        foreach ($items as $item) {
            if (($item['type'] ?? '') !== 'organic') continue;
            if (!isset($item['url'])) continue;
            $normalizedItem = normalizeUrl($item['url']);
            if (strpos($normalizedItem, $normalizedTarget) === 0) {
                $rank = $item['rank_absolute'];
                break;
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO rank_logs (keyword_id, url, rank, url_type, measured_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$keyword_id, $url_data['url'], $rank, $url_data['url_type']]);

        $results[] = [
            'keyword' => $keywords[$keyword_id]['keyword'],
            'url' => $url_data['url'],
            'rank' => $rank ?? '圏外',
            'url_type' => $url_data['url_type']
        ];
    }
}

echo json_encode(['success' => true, 'results' => $results]);
?>