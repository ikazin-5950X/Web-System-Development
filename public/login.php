<?php
// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
if (!empty($_POST['email']) && !empty($_POST['password'])) {
  // POSTで email と password が送られてきた場合のみログイン処理をする
  // email から会員情報を引く
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email ORDER BY id DESC LIMIT 1");
  $select_sth->execute([
    ':email' => $_POST['email'],
  ]);
  $user = $select_sth->fetch();
  if (empty($user)) {
    // 入力されたメールアドレスに該当する会員が見つからなければ、処理を中断しエラー用クエリパラメータ付きのログイン画面URLにリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }

  $correct_password = password_verify($_POST['password'], $user['password']);

  if (!$correct_password) {
    // パスワードが間違っていれば、処理を中断しエラー用クエリパラメータ付きのログイン画面URLにリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }

  session_start();
  // セッションにログインできた会員情報の主キー(id)を設定
  $_SESSION["login_user_id"] = $user['id'];

  // ログインが成功したらログイン完了画面にリダイレクト
  header("HTTP/1.1 302 Found");
  header("Location: ./login_finish.php");
  return;
}
?>

<h1>ログイン</h1>
<!-- ログインフォーム -->
<form method="POST">
  <!-- input要素のtype属性は全部textでも動くが、適切なものに設定すると利用者は使いやすい -->
  <label>
    メールアドレス:
    <input type="email" name="email">
  </label>
  <br>
  <label>
    パスワード:
    <input type="password" name="password" minlength="6">
  </label>
  <br>
  <button type="submit">決定</button>
</form>
<?php if(!empty($_GET['error'])): // エラー用のクエリパラメータがある場合はエラーメッセージ表示 ?>
<div class="error">
  メールアドレスかパスワードが間違っています。
</div>
<?php endif; ?>
<hr>
<div class="signup" >初めての人は<a href="/signup.php">会員登録</a>しましょう。</div>


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
    }
    h1 {
      font-size: 1.5em;
      margin-bottom: 1em;
      text-align: center;
    }
    label {
      font-size: 1.1em;
      display: block;
      margin: 0.5em 0;
    }
    input {
      width: calc(100% - 20px);
      padding: 0.8em;
      font-size: 1em;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-bottom: 1em;
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
      display: block;
      text-align: center;
      margin: 1em 0;
    }
    a:hover {
      text-decoration: underline;
    }
    .error {
      color: red;
      text-align: center;
      margin-top: 1em;
    }

    .signup {
      text-align: center;
    }
  </style>
</head>