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

$entries = $select_sth->fetchAll(PDO::FETCH_ASSOC);

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



<div class="container">
  <h1><?= htmlspecialchars($user['name']) ?> さん のプロフィール</h1>
  <a href="/timeline.php">タイムラインに戻る</a>

  <!-- カバー画像表示 -->
  <div>
    <?php if (!empty($user['cover_filename'])): ?>
      <img src="/image/<?= htmlspecialchars($user['cover_filename']) ?>" style="width: 100%; max-height: 20em; object-fit: cover;">
    <?php else: ?>
      <p>カバー画像は設定されていません。</p>
    <?php endif; ?>
  </div>

  <!-- アイコン画像表示 -->
  <div class="profile-section">
    <?php if (empty($user['icon_filename'])): ?>
      アイコン未設定
    <?php else: ?>
      <img src="/image/<?= htmlspecialchars($user['icon_filename']) ?>" style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
    <?php endif; ?>
  </div>

  <!-- フォローリンクまたはフォロー状態表示 -->
  <div class="follow-links">
    <?php if (isset($_SESSION['login_user_id']) && $_SESSION['login_user_id'] !== $user['id']): ?>
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
  </div>

  <!-- 年齢表示 -->
  <div class="user-info">
    <?php if ($age !== null): ?>
      <p>年齢: <?= htmlspecialchars($age) ?>歳</p>
    <?php else: ?>
      <p>年齢情報は未設定です。</p>
    <?php endif; ?>
  </div>

  <div class="follow-info">
    <p>
      <a href="follow_list.php?user_id=<?= htmlspecialchars($user['id']) ?>">フォローリストを見る</a> |
      <a href="follower_list.php?user_id=<?= htmlspecialchars($user['id']) ?>">フォロワーリストを見る</a>
    </p>
  </div>

  <!-- 自己紹介 -->
  <div class="self-intro">
    <h2>自己紹介</h2>
    <p><?= nl2br(htmlspecialchars($user['self_intro'] ?? '自己紹介はまだ設定されていません')) ?></p>
  </div>

  <hr>

  <!-- 投稿データ表示 -->
  <div class="user-posts">
    <?php
      if (count($entries) > 0) {
        foreach ($entries as $entry): ?>
            <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
              <dt>日時</dt>
              <dd><?= htmlspecialchars($entry['created_at']) ?></dd>
              <dt>内容</dt>
              <dd>
                <?= nl2br(htmlspecialchars($entry['body'])) ?>
                <?php if (!empty($entry['image_filename'])): ?>
                  <div>
                    <?php
                    $image_filenames = explode(',', $entry['image_filename']);
                    foreach ($image_filenames as $filename):
                        $image_url = "/image/" . htmlspecialchars($filename);
                    ?>
                        <img src="<?= $image_url ?>" alt="投稿画像" style="max-width: 100%; height: 10em; margin-bottom: 1em;">
                        <br>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </dd>
            </dl>
        <?php endforeach; 
      } else { ?>
          <p>まだ投稿はありません。</p>
      <?php } ?>
  </div>
</div>

<style>
    body {
      font-family: Arial, sans-serif;
      margin: 1px;
      padding: 0;
      background-color: #f4f4f4;
    }
    .container {
      /* max-width: 800px; */
      margin: 0 auto;
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    h1 {
      font-size: 1.5em;
      text-align: center;
    }
    .cover-image {
      width: 100%;
      max-height: 20em;
      object-fit: cover;
      border-radius: 10px 10px 0 0;
    }
    .profile-section {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-top: 20px;
    }
    .profile-section img {
      height: 5em;
      width: 5em;
      border-radius: 50%;
      object-fit: cover;
    }
    .follow-links {
      margin: 20px 0;
      text-align: center;
    }
    a {
      color: #007bff;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    .user-info p, .self-intro, .user-posts dl {
      margin: 15px 0;
    }
    .user-posts dl {
      padding-bottom: 1em;
      border-bottom: 1px solid #ccc;
    }
    button {
      padding: 0.8em 1.2em;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background-color: #45a049;
    }
  </style>