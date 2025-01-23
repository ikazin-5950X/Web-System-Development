<?php
session_start();

// セッションにログインIDが無ければ (=ログインされていない状態であれば) ログイン画面にリダイレクトさせる
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./login.php");
  return;
}
// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
// セッションにあるログインIDから、ログインしている対象の会員情報を引く
$insert_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$insert_sth->execute([
  ':id' => $_SESSION['login_user_id'],
]);
$user = $insert_sth->fetch();
?>
<h1>ログイン完了</h1>
<p>
  ログイン完了しました!<br>
  <a href="/timeline.php">タイムラインはこちら</a>
</p>
<hr>
<p>
  また、あなたが現在ログインしている会員情報は以下のとおりです。
</p>
<dl> <!-- 登録情報を出力する際はXSS防止のため htmlspecialchars() を必ず使いましょう -->
  <dt>ID</dt>
  <dd><?= htmlspecialchars($user['id']) ?></dd>
  <dt>メールアドレス</dt>
  <dd><?= htmlspecialchars($user['email']) ?></dd>
  <dt>名前</dt>
  <dd><?= htmlspecialchars($user['name']) ?></dd>
</dl>

<style>
  body {
    font-family: Arial, sans-serif;
    margin: 15px;
    padding: 0;
    background-color: #f4f4f4;
  }
  .container {
    width: 100%;
    max-width: 600px;
    margin: 50px auto;
    padding: 2em;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  h1 {
    font-size: 1.8em;
    margin-bottom: 1em;
    text-align: center;
  }
  p {
    font-size: 1.2em;
    line-height: 1.6;
  }
  a {
    font-size: 1.2em;
    color: #007bff;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
  }
  dl {
    margin-top: 1.5em;
  }
  dt {
    font-weight: bold;
    margin-top: 1em;
  }
  dd {
    margin: 0.5em 0 0 1em;
    word-wrap: break-word;
  }
  hr {
    margin: 2em 0;
    border: none;
    border-top: 1px solid #ccc;
  }
  </style>