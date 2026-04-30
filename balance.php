<?php
require_once 'auth_check.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.dataforseo.com/v3/appendix/user_data');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, DATAFORSEO_LOGIN . ':' . DATAFORSEO_PASSWORD);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$balance = $data['tasks'][0]['result'][0]['money']['balance'] ?? null;

echo json_encode(['balance' => $balance]);
?>