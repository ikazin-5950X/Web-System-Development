<?php

// セッションIDの取得(なければ新規で作成&設定)
date_default_timezone_set('Asia/Tokyo');
$session_cookie_name = 'session_id';
$session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
if (!isset($_COOKIE[$session_cookie_name])) {
    setcookie($session_cookie_name, $session_id);
}

// 接続 (redisコンテナの6379番ポートに接続)
$redis = new Redis();
$redis->connect('redis', 6379);

// redisにセッション変数を保存しておくキーを決めておきます。
$redis_session_key = "session-" . $session_id; 

// 既にセッション変数(の配列)が何かしら格納されていればそれを，なければ空の配列を $session_values変数に保存。
$session_values = $redis->exists($redis_session_key)
    ? json_decode($redis->get($redis_session_key), true) 
    : []; 

// セッション別のアクセスカウンタの処理
if (isset($session_values['access_count'])) {
    // カウントが既にある場合はインクリメント
    $session_values['access_count'] += 1;
} else {
    // 初回アクセスの場合は1をセット
    $session_values['access_count'] = 1;
}

//前回アクセス日時
$last_access_time = $session_values['last_access_time'] ?? '初回アクセスです。';

//現在の日時の取得
$current_access_time = date('Y-m-d H:i:s');
$session_values['last_access_time'] = $current_access_time;

// セッション変数に他のデータを保存
$session_values["username"] = "ikazin";

// Redisに保存
$redis->set($redis_session_key, json_encode($session_values));

// 値の取得
$username = $session_values["username"];
$access_count = $session_values['access_count'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>セッションアクセスカウンタ</title>
</head>
<body>

<h3>セッション別アクセスカウンタ_前回アクセス日時</h3>

<!-- セッションに保存されたアクセス回数とユーザ名を表示 -->
<!-- <p>こんにちは、<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>さん！</p> -->
<p>このセッションでの <?= htmlspecialchars($access_count, ENT_QUOTES, 'UTF-8') ?> 回目のアクセスです！</p>
<p>前回のアクセス日時: <?= htmlspecialchars($current_access_time, ENT_QUOTES, 'UTF-8') ?></p>

</body>
</html> 