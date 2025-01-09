<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

session_start();
if (empty($_SESSION['login_user_id'])) { // 非ログインの場合利用不可
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// 現在のログイン情報を取得する
$user_select_sth = $dbh->prepare("SELECT * from users WHERE id = :id");
$user_select_sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $user_select_sth->fetch();

// 投稿処理
if (isset($_POST['body']) && !empty($_SESSION['login_user_id'])) {

  $image_filename = null;
  if (!empty($_POST['image_base64'])) {
    // 先頭の data:~base64, のところは削る
    $base64 = preg_replace('/^data:.+base64,/', '', $_POST['image_base64']);

    // base64からバイナリにデコードする
    $image_binary = base64_decode($base64);

    // 新しいファイル名を決めてバイナリを出力する
    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
    $filepath =  '/var/www/upload/image/' . $image_filename;
    file_put_contents($filepath, $image_binary);
  }

  // insertする
  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:user_id, :body, :image_filename)");
  $insert_sth->execute([
    ':user_id' => $_SESSION['login_user_id'], // ログインしている会員情報の主キー
    ':body' => $_POST['body'], // フォームから送られてきた投稿本文
    ':image_filename' => $image_filename, // 保存した画像の名前 (nullの場合もある)
  ]);

  // 処理が終わったらリダイレクトする
  // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
  header("HTTP/1.1 302 Found");
  header("Location: ./timeline.php");
  return;
}
?>

<div>
  現在 <?= htmlspecialchars($user['name']) ?> (ID: <?= $user['id'] ?>) さんでログイン中
</div>
<div style="margin-bottom: 1em;">
  <a href="/setting/setting.php">設定画面</a>
  /
  <a href="/users.php">会員一覧画面</a>
</div>

<!-- フォームのPOST先はこのファイル自身にする -->
<form method="POST" action="./timeline.php"><!-- enctypeは外しておきましょう -->
  <textarea name="body" required></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <input id="imageBase64Input" type="hidden" name="image_base64"><!-- base64を送る用のinput (非表示) -->
  <canvas id="imageCanvas" style="display: none;"></canvas><!-- 画像縮小に使うcanvas (非表示) -->
  <button type="submit">送信</button>
</form>
<hr>
<dl id="entryTemplate" style="display: none; margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
  <dt>番号</dt>
  <dd data-role="entryIdArea"></dd>
  <dt>投稿者</dt>
  <dd>
    <a href="" data-role="entryUserAnchor"></a>
  </dd>
  <dt>日時</dt>
  <dd data-role="entryCreatedAtArea"></dd>
  <dt>内容</dt>
  <dd data-role="entryBodyArea">
  </dd>
</dl>
<div id="entriesRenderArea"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const entryTemplate = document.getElementById('entryTemplate');
  const entriesRenderArea = document.getElementById('entriesRenderArea');
  const request = new XMLHttpRequest();
  
  request.onload = (event) => {
    const response = event.target.response;
    response.entries.forEach((entry) => {
      // テンプレートとするものから要素をコピー
      const entryCopied = entryTemplate.cloneNode(true);
      // display: none を display: block に書き換える
      entryCopied.style.display = 'block';
      // 番号(ID)を表示
      entryCopied.querySelector('[data-role="entryIdArea"]').innerText = entry.id.toString();
      // 名前を表示
      entryCopied.querySelector('[data-role="entryUserAnchor"]').innerText = entry.user_name;
      // 名前のところのリンク先(プロフィール)のURLを設定
      entryCopied.querySelector('[data-role="entryUserAnchor"]').href = entry.user_profile_url;
      // 投稿日時を表示
      entryCopied.querySelector('[data-role="entryCreatedAtArea"]').innerText = entry.created_at;
      // 本文を表示 (ここはHTMLなのでinnerHTMLで)
      entryCopied.querySelector('[data-role="entryBodyArea"]').innerHTML = entry.body;

      // ユーザーアイコン表示
      if (entry.user_icon_url) {
        const img = document.createElement('img');
        img.src = entry.user_icon_url;
        img.style.width = '40px';  // アイコンのサイズを調整
        img.style.height = '40px'; // アイコンのサイズを調整
        img.style.borderRadius = '50%'; // 丸型にする
        img.style.marginRight = '10px'; // 名前との間隔調整
        entryCopied.querySelector('[data-role="entryUserAnchor"]').prepend(img); // ユーザー名の前にアイコンを追加
      }
      
      // 画像表示
      if (entry.image_url) {
        const img = document.createElement('img');
        img.src = entry.image_url;
        img.style.maxHeight = '10em';
        entryCopied.querySelector('[data-role="entryBodyArea"]').appendChild(img);
      }

      // 最後に実際の描画を行う
      entriesRenderArea.appendChild(entryCopied);
    });
  }
  
  request.open('GET', '/timeline_json.php', true); // timeline_json.php を叩く
  request.responseType = 'json';
  request.send();
});
</script>