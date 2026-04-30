<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /app');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';
    $pdo = getDBConnection();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: /app');
        exit;
    } else {
        $error = 'IDまたはパスワードが正しくありません';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ログイン - 逆SEO順位トラッカー</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: sans-serif; background: #0f1117; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; }
.card { background: #1a1d27; padding: 40px; border-radius: 12px; width: 360px; }
h1 { font-size: 18px; margin-bottom: 24px; text-align: center; }
label { font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; }
input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #333; background: #0f1117; color: #fff; font-size: 14px; margin-bottom: 16px; }
button { width: 100%; padding: 12px; background: #4f6ef7; color: #fff; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; }
.error { color: #ff6b6b; font-size: 13px; margin-bottom: 16px; text-align: center; }
</style>
</head>
<body>
<div class="card">
  <h1>🔐 逆SEO順位トラッカー</h1>
  <?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <label>ユーザーID</label>
    <input type="text" name="username" required autofocus>
    <label>パスワード</label>
    <input type="password" name="password" required>
    <button type="submit">ログイン</button>
  </form>
</div>
</body>
</html>