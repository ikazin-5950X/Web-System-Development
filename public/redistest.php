<?php

$redis = new Redis();
$redis -> connect('redis', 6379);

//演習1
/*
$counterKey = 'access_counter';

$accessCount = $redis->incr($counterKey);

echo "This page has been accessed $accessCount times.";
*/


/*演習2
$boardkey = 'simple_board_post';

if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['content'])) {
    $content = $_POST['content'];
    $redis -> set($boardkey, $content);
}

$latestPost = $redis -> get($boardkey);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>簡単な掲示板</title>
</head>
<body>

<h1>掲示板</h1>

<form action="" method="POST">
    <label for="content">投稿内容:</label><br>
    <textarea id="content" name="content" rows="4" cols="50" required></textarea><br><br>
    <button type="submit">投稿する</button>
</form>

<hr>

<h2>最新の投稿</h2>
<?php if ($latestPost): ?>
    <p><?= htmlspecialchars($latestPost, ENT_QUOTES, 'UTF-8') ?></p>
<?php else: ?>
    <p>まだ投稿はありません。</p>
<?php endif; ?>

</body>
</html>
*/

date_default_timezone_set('Asia/Tokyo');
$boardKey = 'simple_board_posts';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['content'])) {
    $content = $_POST['content'];
    
    $jsonData = $redis->get($boardKey);  
    $posts = is_string($jsonData) ? json_decode($jsonData, true) : [];  // JSONデータがあるか確認してデコード

    $posts[] = [
        'content' => $content,
        'timestamp' => date('Y-m-d H:i:s')  
    ];

    $redis->set($boardKey, json_encode($posts));
}

$jsonData = $redis->get($boardKey);
$posts = is_string($jsonData) ? json_decode($jsonData, true) : [];  // JSONデータをデコード
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板（複数投稿対応）</title>
</head>
<body>

<h1>掲示板</h1>

<form action="" method="POST">
    <label for="content">投稿内容:</label><br>
    <textarea id="content" name="content" rows="4" cols="50" required></textarea><br><br>
    <button type="submit">投稿する</button>
</form>

<hr>

<h2>投稿一覧</h2>
<?php if (!empty($posts)): ?>
    <ul>
        <?php foreach ($posts as $post): ?>
            <li>
                <strong>投稿日:</strong> <?= htmlspecialchars($post['timestamp'], ENT_QUOTES, 'UTF-8') ?><br>
                <strong>内容:</strong> <?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>まだ投稿はありません。</p>
<?php endif; ?>

</body>
</html>