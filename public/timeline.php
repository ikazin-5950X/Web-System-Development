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

  $image_filenames = []; // 複数の画像を保存するための配列
  if (!empty($_POST['image_base64'])) {
    // 画像が複数ある場合、カンマ区切りで送られているBase64を分割
    $base64Images = explode(',', $_POST['image_base64'][0]);

    foreach ($base64Images as $base64) {
      // Base64からバイナリにデコードする
      $image_binary = base64_decode($base64);

      // 画像の情報を取得
      $image_info = getimagesizefromstring($image_binary);
      $mime_type = $image_info['mime'];

      // 拡張子を決める
      switch ($mime_type) {
        case 'image/png':
          $extension = '.png';
          break;
        case 'image/jpeg':
          $extension = '.jpg';
          break;
        default:
          // サポートされていない画像形式の場合
          $extension = null;
          break;
      }

      // 拡張子が判定できた場合に画像を保存
      if ($extension) {
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . $extension;
        $filepath = '/var/www/upload/image/' . $image_filename;
        file_put_contents($filepath, $image_binary);

        // ファイル名を配列に追加
        $image_filenames[] = $image_filename;
      }
    }
  }

  // 画像のファイル名をカンマ区切りで保存
  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:user_id, :body, :image_filename)");
  $insert_sth->execute([
    ':user_id' => $_SESSION['login_user_id'],
    ':body' => $_POST['body'],
    ':image_filename' => implode(',', $image_filenames), // 複数画像ファイル名をカンマ区切りで保存
  ]);

  // リダイレクト
  header("HTTP/1.1 302 Found");
  header("Location: ./timeline.php");
  return;
}

?>

<div>
  現在 <?= htmlspecialchars($user['name']) ?> (ID: <?= $user['id'] ?>) さんでログイン中
</div>

<div style="margin-bottom: 1em;">
  <a href="login.php">ログアウト / 別のアカウントでログイン</a>
  <a href="/setting/setting.php">設定画面</a>
  <a href="/users.php">会員一覧画面</a>
  <a href="/profile.php?user_id=<?= htmlspecialchars($user['id']) ?>">自分のプロフィール</a>
</div>

<!-- フォームのPOST先はこのファイル自身にする -->
<form method="POST" action="./timeline.php" enctype="multipart/form-data">
<textarea name="body" required></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="images[]" id="imageInput" multiple>
  </div>
  <input id="imageBase64Input" type="hidden" name="image_base64[]"><!-- 画像用のhidden input -->
  <canvas id="imageCanvas" style="display: none;"></canvas>
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
  <dd data-role="entryBodyArea"></dd>
  <dt>画像</dt>
  <div data-role="entryImageArea"></div>
</dl>

<div id="entriesRenderArea"></div>

<script>
// JavaScriptで画像をBase64に変換してフォームにセット
document.getElementById("imageInput").addEventListener("change", function(event) {
  const files = event.target.files;
  if (files) {
    const base64Images = [];
    Array.from(files).forEach((file) => {
      const reader = new FileReader();
      reader.onloadend = function() {
        const base64Image = reader.result.split(',')[1];
        base64Images.push(base64Image);
        if (base64Images.length === files.length) {
          document.getElementById("imageBase64Input").value = base64Images.join(',');
        }
      };
      reader.readAsDataURL(file); // ファイルをBase64形式に変換
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const entryTemplate = document.getElementById('entryTemplate');
  const entriesRenderArea = document.getElementById('entriesRenderArea');
  let currentPage = 1;
  let loading = false;

  // 新しいエントリーを読み込む関数
  const loadEntries = () => {
    if (loading) return;
    loading = true;

    const request = new XMLHttpRequest();
    request.onload = (event) => {
      const response = event.target.response;
      if (response.entries.length === 0) {
        window.removeEventListener('scroll', handleScroll);
        return;
      }

      response.entries.forEach((entry) => {
        const entryCopied = entryTemplate.cloneNode(true);
        entryCopied.style.display = 'block';
        entryCopied.querySelector('[data-role="entryIdArea"]').innerText = entry.id.toString();
        entryCopied.querySelector('[data-role="entryUserAnchor"]').innerText = entry.user_name;
        entryCopied.querySelector('[data-role="entryUserAnchor"]').href = entry.user_profile_url;
        entryCopied.querySelector('[data-role="entryCreatedAtArea"]').innerText = entry.created_at;
        entryCopied.querySelector('[data-role="entryBodyArea"]').innerHTML = entry.body;

        // ユーザーアイコンを追加
        if (entry.user_icon_url) {
          const img = document.createElement('img');
          img.src = entry.user_icon_url;
          img.style.width = '40px';
          img.style.height = '40px';
          img.style.borderRadius = '50%';
          img.style.marginRight = '10px';
          entryCopied.querySelector('[data-role="entryUserAnchor"]').prepend(img);
        }

        // 投稿画像を追加
        if (entry.image_url) {
          const imageUrls = entry.image_url.split(','); // 画像URLを分割
          imageUrls.forEach((url) => {
            const img = document.createElement('img');
            img.src = url; // それぞれの画像URLを設定
            img.style.maxHeight = '10em';
            img.style.display = 'block';
            entryCopied.querySelector('[data-role="entryImageArea"]').appendChild(img);
          });
        }

        entriesRenderArea.appendChild(entryCopied);
      });

      currentPage++;
      loading = false;
    };

    request.open('GET', `/timeline_json.php?page=${currentPage}`, true);
    request.responseType = 'json';
    request.send();
  };

  // スクロール時に新しいエントリーを読み込む
  const handleScroll = () => {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
      loadEntries();
    }
  };

  window.addEventListener('scroll', handleScroll);
  loadEntries();
});
</script>


<style>
/* 基本的なスタイル */
body {
  font-family: Arial, sans-serif;
  margin: 15px;
  padding: 0;
  background-color: #f4f4f4;
}

.container {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  padding: 1em;
}

.content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.content h1 {
  font-size: 1.5em;
  margin-bottom: 0.5em;
}

textarea {
  width: 80%;
  height: 150px;
  padding: 1em;
  font-size: 1.1em;
  border: 1px solid #ccc;
  border-radius: 5px;
  resize: none;
  display: block;
  margin: 0 auto;
}

input[type="file"] {
  padding: 1em;
  font-size: 1em;
  margin: 1em 0;
  width: 100%;
  background-color: #f1f1f1;
  border: 1px solid #ccc;
  border-radius: 5px;
  cursor: pointer;
}

input[type="file"]:hover {
  background-color: #e7e7e7;
}

button {
  width: 60%;
  padding: 1.2em;
  font-size: 1.4em;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  display: block;
  margin: 1em auto;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #45a049;
}

a {
  color: #007bff;
  text-decoration: none;
  margin: 0.5em 0;
  display: block;
  text-align: center;
}

a:hover {
  text-decoration: underline;
}

a.btn-link {
  font-size: 1.3em;
  color: #007bff;
  padding: 0.8em;
  display: inline-block;
  margin-top: 1em;
  border: 1px solid #007bff;
  border-radius: 5px;
  text-align: center;
}

a.btn-link:hover {
  background-color: #007bff;
  color: white;
}

/* 投稿エントリーのスタイル */
#entryTemplate {
  display: none;
  margin-bottom: 1em;
  padding-bottom: 1em;
  border-bottom: 1px solid #ccc;
}

#entryTemplate img {
  max-width: 100%;
  height: auto;
}

[data-role="entryUserAnchor"] {
  display: inline; /* センタリングしないようにdisplayをinlineに */
  margin: 0; /* マージンをリセットしてセンタリングしないように */
  text-align: left; /* 左寄せにする */
}

/* レスポンシブ対応 */
@media screen and (max-width: 768px) {
  textarea {
    font-size: 1.2em;
  }

  button {
    width: 100%;
    font-size: 1.5em;
    padding: 1em;
  }

  a {
    font-size: 1.3em;
  }
  [data-role="entryUserAnchor"] {
    display: inline; /* センタリングしないようにdisplayをinlineに */
    margin: 0; /* マージンをリセットしてセンタリングしないように */
    text-align: left; /* 左寄せにする */
  }

  /* エントリー表示 */
  #entryTemplate {
    font-size: 1.2em;
  }
}

/* モバイル用のボタンやフォームのスタイル */
@media screen and (max-width: 480px) {
  button {
    font-size: 1.5em;
    padding: 1.2em;
  }

  textarea {
    font-size: 1.2em;
    padding: 1.2em;
  }

  a {
    font-size: 1.5em;
    padding: 1em;
  }

  /* 設定画面のリンクや会員一覧のリンク */
  a.btn-link {
    font-size: 1.4em;
    padding: 1.2em;
    width: 100%;
  }

  [data-role="entryUserAnchor"] {
    display: inline; /* センタリングしないようにdisplayをinlineに */
    margin: 0; /* マージンをリセットしてセンタリングしないように */
    text-align: left; /* 左寄せにする */
  }
}

</style>