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
  $icon_filename = $user['icon_filename'];
  $cover_filename = $user['cover_filename'];

  // アイコン画像の処理
  if (!empty($_POST['icon_image_base64'])) {
    $base64 = preg_replace('/^data:.+base64,/', '', $_POST['icon_image_base64']);
    $image_binary = base64_decode($base64);
    $icon_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
    $filepath = '/var/www/upload/image/' . $icon_filename;
    file_put_contents($filepath, $image_binary);
  }

  // カバー画像の処理
  if (!empty($_POST['cover_image_base64'])) {
    $base64 = preg_replace('/^data:.+base64,/', '', $_POST['cover_image_base64']);
    $image_binary = base64_decode($base64);
    $cover_filename = strval(time()) . bin2hex(random_bytes(25)) . '_cover.png';
    $filepath = '/var/www/upload/image/' . $cover_filename;
    file_put_contents($filepath, $image_binary);
  }

  // 自己紹介文を保存
  $self_intro = mb_substr($_POST['self_intro'], 0, 1000); // 1000文字以内に制限
  $birthday = !empty($_POST['birthday']) ? $_POST['birthday'] : null;
  $update_sth = $dbh->prepare("UPDATE users SET icon_filename = :icon_filename, cover_filename = :cover_filename, self_intro = :self_intro, cover_filename = :cover_filename, birthday = :birthday WHERE id = :id");
  $update_sth->execute([
      ':id' => $user['id'],
      ':icon_filename' => $icon_filename,
      ':cover_filename' => $cover_filename,
      ':self_intro' => $self_intro,
      ':cover_filename' => $cover_filename,
      ':birthday' => $birthday,
  ]);

  header("HTTP/1.1 302 Found");
  header("Location: ./setting.php");
  return;
}
?>

<a href="/bbs.php">掲示板に戻る</a>
<h1>アイコン画像およびカバー画像設定/変更</h1>
<dl>
  <dt>ID</dt>
  <dd><?= htmlspecialchars($user['id']) ?></dd>
  <dt>名前</dt>
  <dd><?= htmlspecialchars($user['name']) ?></dd>
  <dt>生年月日</dt>
  <dd>
    <?= htmlspecialchars($user['birthday'] ?? '未設定') ?>
  </dd>
  <dt>アイコン</dt>
  <dd>
    <?php if(empty($user['icon_filename'])): ?>
      現在未設定
    <?php else: ?>
      <img src="/image/<?= $user['icon_filename'] ?>" style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
    <?php endif; ?>
  </dd>
  <dt>カバー画像</dt>
  <dd>
    <?php if(empty($user['cover_filename'])): ?>
      現在未設定
    <?php else: ?>
      <img src="/image/<?= $user['cover_filename'] ?>" style="height: 10em; width: 100%; object-fit: cover;">
    <?php endif; ?>
  </dd>
</dl>

<form method="POST">
  <div>
    <label>アイコン画像:</label>
    <input type="file" accept="image/*" id="iconImageInput">
    <input id="iconImageBase64Input" type="hidden" name="icon_image_base64">
  </div>
  <div>
    <label>カバー画像:</label>
    <input type="file" accept="image/*" id="coverImageInput">
    <input id="coverImageBase64Input" type="hidden" name="cover_image_base64">
  </div>
  <div>
    <label>自己紹介 (最大1000文字):</label>
    <textarea name="self_intro" rows="5" maxlength="1000"><?= htmlspecialchars($user['self_intro'] ?? '') ?></textarea>
  </div>
  <div>
    <label>生年月日:</label>
    <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
  </div>
  <button type="submit">アップロード</button>
</form>

<script>
// 以下はアイコン画像とカバー画像の処理をするスクリプト
document.addEventListener("DOMContentLoaded", () => {
  function setupImageInput(inputId, base64InputId, canvasId) {
    const imageInput = document.getElementById(inputId);
    const base64Input = document.getElementById(base64InputId);
    const canvas = document.createElement('canvas'); // canvasを動的に生成

    imageInput.addEventListener("change", () => {
      if (imageInput.files.length < 1) return;
      const file = imageInput.files[0];
      if (!file.type.startsWith('image/')) return;

      const reader = new FileReader();
      const image = new Image();

      reader.onload = () => {
        image.onload = () => {
          const maxLength = 1000;
          if (image.naturalWidth > image.naturalHeight) {
            canvas.width = maxLength;
            canvas.height = (image.naturalHeight / image.naturalWidth) * maxLength;
          } else {
            canvas.height = maxLength;
            canvas.width = (image.naturalWidth / image.naturalHeight) * maxLength;
          }

          const ctx = canvas.getContext("2d");
          ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
          base64Input.value = canvas.toDataURL();
        };
        image.src = reader.result;
      };

      reader.readAsDataURL(file);
    });
  }

  setupImageInput("iconImageInput", "iconImageBase64Input");
  setupImageInput("coverImageInput", "coverImageBase64Input");
});
</script>
