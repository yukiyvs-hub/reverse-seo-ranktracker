<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

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

$pdo = getDBConnection();

// task_queueから全タスクを取得
$tasks = $pdo->query("SELECT * FROM task_queue ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);

if (empty($tasks)) {
    echo json_encode(['success' => false, 'message' => 'タスクキューが空です']);
    exit;
}

$results = [];

foreach ($tasks as $task) {
    $task_id = $task['task_id'];
    $keyword_id = $task['keyword_id'];

    // DataForSEOから結果取得
    $url = 'https://api.dataforseo.com/v3/serp/google/organic/task_get/advanced/' . $task_id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, DATAFORSEO_LOGIN . ':' . DATAFORSEO_PASSWORD);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    $status = $result['tasks'][0]['status_code'] ?? 0;
    if ($status !== 20000) {
        $results[] = ['keyword_id' => $keyword_id, 'status' => 'not_ready', 'status_code' => $status];
        continue;
    }

    $items = $result['tasks'][0]['result'][0]['items'] ?? [];

    // 監視URLを取得
    $stmt = $pdo->prepare("SELECT url, url_type FROM watch_urls WHERE keyword_id = ?");
    $stmt->execute([$keyword_id]);
    $watch_urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($watch_urls as $watch) {
        $rank = null;
        $normalizedTarget = normalizeUrl($watch['url']);

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
        $stmt->execute([$keyword_id, $watch['url'], $rank, $watch['url_type']]);

        $results[] = [
            'keyword_id' => $keyword_id,
            'url' => $watch['url'],
            'rank' => $rank ?? '圏外',
            'url_type' => $watch['url_type']
        ];
    }

    // 完了したタスクをキューから削除
    $stmt = $pdo->prepare("DELETE FROM task_queue WHERE task_id = ?");
    $stmt->execute([$task_id]);
}

echo json_encode(['success' => true, 'results' => $results]);
?>