<?php
session_start();

// ログインしていなければログイン画面にリダイレクト
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

// 自分がフォローされている一覧をDBから取得
// テーブル結合を使って、フォロワーの会員情報を一緒に取得
$select_sth = $dbh->prepare(
  'SELECT user_relationships.*, users.name AS follower_user_name, users.icon_filename AS follower_user_icon_filename'
  . ' FROM user_relationships INNER JOIN users ON user_relationships.follower_user_id = users.id'
  . ' WHERE user_relationships.followee_user_id = :followee_user_id'
  . ' ORDER BY user_relationships.id DESC'
);
$select_sth->execute([
    ':followee_user_id' => $_SESSION['login_user_id'],
]);
?>

<h1>フォロワー一覧</h1>
<a href="/timeline.php">タイムラインに戻る</a>

<ul>
  <?php foreach($select_sth as $relationship): ?>
  <li>
    <a href="/profile.php?user_id=<?= $relationship['follower_user_id'] ?>">
      <?php if(!empty($relationship['follower_user_icon_filename'])): // アイコン画像がある場合は表示 ?>
      <img src="/image/<?= $relationship['follower_user_icon_filename'] ?>"
        style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
      <?php endif; ?>

      <?= htmlspecialchars($relationship['follower_user_name']) ?>
      (ID: <?= htmlspecialchars($relationship['follower_user_id']) ?>)
    </a>
    (<?= $relationship['created_at'] ?>にフォローされました)
  </li>
  <?php endforeach; ?>
</ul>
