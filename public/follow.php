<?php
session_start();

if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./login.php");
  return;
}

// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

// フォロー対象(フォローされる側)のデータを引く
$followee_user = null;
if (!empty($_GET['followee_user_id'])) {
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
  $select_sth->execute([
      ':id' => $_GET['followee_user_id'],
  ]);
  $followee_user = $select_sth->fetch();
}
if (empty($followee_user)) {
  header("HTTP/1.1 404 Not Found");
  print("そのようなユーザーIDの会員情報は存在しません");
  return;
}

// 現在のフォロー状態をDBから取得
$select_sth = $dbh->prepare(
  "SELECT * FROM user_relationships"
  . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
);
$select_sth->execute([
    ':followee_user_id' => $followee_user['id'], // フォローされる側(フォロー対象)
    ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側はログインしている会員
]);
$relationship = $select_sth->fetch();
if (!empty($relationship)) { // 既にフォロー関係がある場合は適当なエラー表示して終了
  print("既にフォローしています。");
  return;
}

$insert_result = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') { // フォームでPOSTした場合は実際のフォロー登録処理を行う
  $insert_sth = $dbh->prepare(
    "INSERT INTO user_relationships (follower_user_id, followee_user_id) VALUES (:follower_user_id, :followee_user_id)"
  );
  $insert_result = $insert_sth->execute([
      ':followee_user_id' => $followee_user['id'], // フォローされる側(フォロー対象)
      ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側はログインしている会員
  ]);
}
?>

<?php if($insert_result): ?>
<div>
  <?= htmlspecialchars($followee_user['name']) ?> さんをフォローしました。<br>
    <a href="/profile.php?user_id=<?= $followee_user['id'] ?>">
  <?= htmlspecialchars($followee_user['name']) ?> さんのプロフィールへ
  </a>
  /
  <a href="/users.php">
    会員一覧へ
  </a>
</div>
<?php else: ?>
<div>
  <?= htmlspecialchars($followee_user['name']) ?> さんをフォローしますか?
  <form method="POST">
    <button type="submit">
      フォローする
    </button>
  </form>
</div>
<?php endif; ?>

<style>
    body {
      font-family: Arial, sans-serif;
      margin: 15px;
      padding: 0;
      background-color: #f4f4f4;
    }
    .container {
      width: 100%;
      max-width: 400px;
      margin: 50px auto;
      padding: 2em;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    h1 {
      font-size: 1.5em;
      margin-bottom: 1em;
    }
    p {
      font-size: 1.2em;
      margin-bottom: 1.5em;
    }
    button {
      width: 100%;
      padding: 0.8em;
      font-size: 1.2em;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background-color: #45a049;
    }
    a {
      font-size: 1em;
      color: #007bff;
      text-decoration: none;
      margin-top: 1em;
      display: inline-block;
    }
    a:hover {
      text-decoration: underline;
    }
    .message {
      font-size: 1.2em;
      margin-bottom: 1.5em;
    }
</style>