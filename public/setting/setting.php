<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}
// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
// セッションにあるログインIDから、ログインしている会員情報を取得
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $image_filename = $user['icon_filename'];
  if (!empty($_POST['image_base64'])) {
    $base64 = preg_replace('/^data:.+base64,/', '', $_POST['image_base64']);
    $image_binary = base64_decode($base64);
    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
    $filepath = '/var/www/upload/image/' . $image_filename;
    file_put_contents($filepath, $image_binary);
  }
  
  // 自己紹介文を保存
  $self_intro = mb_substr($_POST['self_intro'], 0, 1000); // 1000文字以内に制限
  $update_sth = $dbh->prepare("UPDATE users SET icon_filename = :icon_filename, self_intro = :self_intro WHERE id = :id");
  $update_sth->execute([
      ':id' => $user['id'],
      ':icon_filename' => $image_filename,
      ':self_intro' => $self_intro,
  ]);
  
  header("HTTP/1.1 302 Found");
  header("Location: ./setting.php");
  return;
}
?>
<h1>アイコン画像設定/変更</h1>
<div>
  <?php if(empty($user['icon_filename'])): ?>
    現在未設定
  <?php else: ?>
    <img src="/image/<?= $user['icon_filename'] ?>" style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>
<form method="POST">
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <div style="margin: 1em 0;">
    <label>自己紹介 (最大1000文字):</label>
    <textarea name="self_intro" rows="5" maxlength="1000"><?= htmlspecialchars($user['self_intro'] ?? '') ?></textarea>
  </div>
  <input id="imageBase64Input" type="hidden" name="image_base64">
  <canvas id="imageCanvas" style="display: none;"></canvas>
  <button type="submit">アップロード</button>
</form>
<script>
// 画像アップロード用のJavaScriptは前述のコードと同様
</script>
