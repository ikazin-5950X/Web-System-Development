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
?>
<h1><?= htmlspecialchars($user['name']) ?> さん のプロフィール</h1>
<div>
  <?php if(empty($user['icon_filename'])): ?>
    現在未設定
  <?php else: ?>
    <img src="/image/<?= $user['icon_filename'] ?>" style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>
<div>
  <h2>自己紹介</h2>
  <p><?= nl2br(htmlspecialchars($user['self_intro'] ?? '自己紹介はまだ設定されていません')) ?></p>
</div>
