<?php
$user = null;
if (!empty($_GET['user_id'])) {
  $user_id = $_GET['user_id'];
  $dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
  $select_sth->execute([':id' => $user_id]);
  $user = $select_sth->fetch();
}

if (empty($user)) {
  header("HTTP/1.1 404 Not Found");
  print("そのようなユーザーIDの会員情報は存在しません");
  return;
}

// 年齢を計算
$age = null;
if (!empty($user['birthday'])) {
    $birthDate = new DateTime($user['birthday']);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate)->y; // 年齢を計算
}

// この人の投稿データを取得
$select_sth = $dbh->prepare(
  'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
  . ' FROM bbs_entries INNER JOIN users ON bbs_entries.user_id = users.id'
  . ' WHERE user_id = :user_id'
  . ' ORDER BY bbs_entries.created_at DESC'
);
$select_sth->execute([
  ':user_id' => $user_id,
]);
?>
<h1><?= htmlspecialchars($user['name']) ?> さん のプロフィール</h1>
<a href="/bbs.php">掲示板に戻る</a>
<div>
  <?php if (!empty($user['cover_filename'])): ?>
    <img src="/image/<?= htmlspecialchars($user['cover_filename']) ?>" style="width: 100%; max-height: 20em; object-fit: cover;">
  <?php else: ?>
    <p>カバー画像は設定されていません。</p>
  <?php endif; ?>
</div>
<div>
    <?php if(empty($user['icon_filename'])): ?>
      アイコン未設定
    <?php else: ?>
    <img src="/image/<?= $user['icon_filename'] ?>" style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>
<div>
  <?php if ($age !== null): ?>
    <p>年齢: <?= $age ?>歳</p>
  <?php else: ?>
    <p>年齢情報は未設定です。</p>
  <?php endif; ?>
</div>
<div>
  <h2>自己紹介</h2>
  <p><?= nl2br(htmlspecialchars($user['self_intro'] ?? '自己紹介はまだ設定されていません')) ?></p>
</div>

<hr>
<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt>内容</dt>
    <dd>
      <?= htmlspecialchars($entry['body']) ?>
      <?php if(!empty($entry['image_filename'])): ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>
