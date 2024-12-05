<?php
session_start();

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
    $age = $currentDate->diff($birthDate)->y;
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

// フォロー状態を取得
$relationship = null;
$reverse_relationship = null;
if (!empty($_SESSION['login_user_id'])) {
  // 自分が相手をフォローしているかどうか
  $select_sth = $dbh->prepare(
    "SELECT * FROM user_relationships"
    . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
  );
  $select_sth->execute([
      ':followee_user_id' => $user['id'],
      ':follower_user_id' => $_SESSION['login_user_id'],
  ]);
  $relationship = $select_sth->fetch();

  // 相手が自分をフォローしているかどうか
  $select_sth = $dbh->prepare(
    "SELECT * FROM user_relationships"
    . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
  );
  $select_sth->execute([
      ':followee_user_id' => $_SESSION['login_user_id'],
      ':follower_user_id' => $user['id'],
  ]);
  $reverse_relationship = $select_sth->fetch();
}
?>

<h1><?= htmlspecialchars($user['name']) ?> さん のプロフィール</h1>
<a href="/bbs.php">掲示板に戻る</a>

<!-- カバー画像表示 -->
<div>
  <?php if (!empty($user['cover_filename'])): ?>
    <img src="/image/<?= htmlspecialchars($user['cover_filename']) ?>" style="width: 100%; max-height: 20em; object-fit: cover;">
  <?php else: ?>
    <p>カバー画像は設定されていません。</p>
  <?php endif; ?>
</div>

<!-- アイコン画像表示 -->
<div>
  <?php if (empty($user['icon_filename'])): ?>
    アイコン未設定
  <?php else: ?>
    <img src="/image/<?= htmlspecialchars($user['icon_filename']) ?>" style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>

<!-- フォローリンクまたはフォロー状態表示 -->
<?php if ($_SESSION['login_user_id'] !== $user['id']): ?>
  <?php if (empty($relationship)): ?>
    <div>
      <a href="./follow.php?followee_user_id=<?= htmlspecialchars($user['id']) ?>">フォローする</a>
    </div>
  <?php else: ?>
    <div>
      <?= htmlspecialchars($relationship['created_at']) ?> にフォローしました。
    </div>
  <?php endif; ?>
<?php endif; ?>

<!-- フォローされているかどうかの表示 -->
<?php if (!empty($reverse_relationship)): ?>
  <div>
    このユーザーはあなたをフォローしています。
  </div>
<?php endif; ?>

<!-- 年齢表示 -->
<div>
  <?php if ($age !== null): ?>
    <p>年齢: <?= htmlspecialchars($age) ?>歳</p>
  <?php else: ?>
    <p>年齢情報は未設定です。</p>
  <?php endif; ?>
</div>

<!-- 自己紹介 -->
<div>
  <h2>自己紹介</h2>
  <p><?= nl2br(htmlspecialchars($user['self_intro'] ?? '自己紹介はまだ設定されていません')) ?></p>
</div>

<hr>

<!-- 投稿データ表示 -->
<?php foreach ($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt>日時</dt>
    <dd><?= htmlspecialchars($entry['created_at']) ?></dd>
    <dt>内容</dt>
    <dd>
      <?= nl2br(htmlspecialchars($entry['body'])) ?>
      <?php if (!empty($entry['image_filename'])): ?>
        <div>
          <img src="/image/<?= htmlspecialchars($entry['image_filename']) ?>" style="max-height: 10em;">
        </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach; ?>
