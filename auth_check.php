<?php
session_start();

// ─── セッションタイムアウト設定（秒） ───
$timeout = 60 * 60 * 2; // ← 2時間。変えたければここの数字を変える

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// 最終アクセス時刻を記録・チェック
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        // タイムアウト → セッション破棄してログインへ
        session_unset();
        session_destroy();
        header('Location: /login?timeout=1');
        exit;
    }
}
$_SESSION['last_activity'] = time(); // アクセスのたびに更新