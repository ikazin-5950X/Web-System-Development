<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

if (isset($_POST['body'])) {
    $image_filename = null;
    if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
        if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {
            header("HTTP/1.1 302 Found");
            header("Location: ./1978_SnowChicago.php");
        }
        $pathinfo = pathinfo($_FILES['image']['name']);
        $extension = $pathinfo['extension'];
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
        $filepath = '/var/www/upload/image/' . $image_filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
    }
    $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (body, image_filename) VALUES (:body, :image_filename)");
    $insert_sth->execute([
        ':body' => $_POST['body'],
        ':image_filename' => $image_filename,
    ]);
    header("HTTP/1.1 302 Found");
    header("Location: ./1978_SnowChicago.php");
    return;
}

$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth->execute();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
    <style>
        .anchor-preview {
            display: none; /* デフォルトで非表示 */
            position: absolute;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 10px;
            max-width: 300px; /* 最大幅を設定 */
            max-height: 200px; /* 最大高さを設定 */
            overflow: auto; /* 内容が溢れる場合にスクロールバーを表示 */
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* プレビューの影を追加 */
            border-radius: 4px; /* 角を丸くする */
        }
        .reply-anchor {
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form method="POST" action="./1978_SnowChicago.php" enctype="multipart/form-data">
        <textarea name="body" id="body"></textarea>
        <div style="margin: 1em 0;">
            <input type="file" accept="image/*" name="image" id="imageInput">
        </div>
        <button type="submit">送信</button>
    </form>

    <hr>

    <?php foreach ($select_sth as $entry): ?>
    <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
        <dt>ID</dt>
        <dd><?= $entry['id'] ?></dd>
        <dt>日時</dt>
        <dd><?= $entry['created_at'] ?></dd>
        <dt>内容</dt>
        <dd>
            <?= nl2br(preg_replace('/&gt;&gt;(\d+)/', '<a href="#entry-$1" class="reply-anchor">>>$1</a>', htmlspecialchars($entry['body']))) ?>
            <?php if (!empty($entry['image_filename'])): ?>
            <div>
                <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
            </div>
            <?php endif; ?>
            <div id="entry-<?= $entry['id'] ?>" style="height: 1px; margin-top: -1px;"></div>
        </dd>
    </dl>
    <?php endforeach ?>

    <div id="anchor-preview" class="anchor-preview"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('imageInput');
            const maxSize = 5 * 1024 * 1024; // 5MB

            imageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];

                if (file.size > maxSize) {
                    alert("5MB以下になるように自動的に圧縮されます。");
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.src = e.target.result;

                        img.onload = function() {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            const maxDimension = 1920; // 最大幅または高さの設定

                            let width = img.width;
                            let height = img.height;

                            if (width > height) {
                                if (width > maxDimension) {
                                    height *= maxDimension / width;
                                    width = maxDimension;
                                }
                            } else {
                                if (height > maxDimension) {
                                    width *= maxDimension / height;
                                    height = maxDimension;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(img, 0, 0, width, height);

                            canvas.toBlob(function(blob) {
                                const compressedFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });

                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(compressedFile);
                                imageInput.files = dataTransfer.files;
                            }, 'image/jpeg', 0.9); // 0.9は品質設定で、90%を意味します
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // レスアンカーのホバー処理
            document.querySelectorAll('.reply-anchor').forEach(anchor => {
                anchor.addEventListener('mouseover', function(event) {
                    const id = event.target.textContent.replace('>>', '');
                    const targetEntry = document.getElementById('entry-' + id);

                    if (targetEntry) {
                        const preview = document.getElementById('anchor-preview');
                        const parentDl = targetEntry.closest('dl');
                        
                        // ID、日時、内容を取得
                        const entryId = parentDl.querySelector('dt').nextElementSibling.textContent.trim();
                        const createdAt = parentDl.querySelector('dt:nth-of-type(2) + dd').textContent.trim();
                        const content = parentDl.querySelector('dt:nth-of-type(3) + dd').innerHTML;

                        // プレビューの内容を設定
                        preview.innerHTML = `
                            <strong>ID:</strong> ${entryId}<br>
                            <strong>日時:</strong> ${createdAt}<br><br>
                            ${content}
                        `;

                        // プレビュー表示位置の調整
                        preview.style.display = 'block'; // 確実に表示するために

                        // プレビューの位置を調整する
                        const previewRect = preview.getBoundingClientRect();
                        let left = event.pageX + 10;
                        let top = event.pageY + 10;

                        // プレビューが画面外に出ないように調整
                        if (left + previewRect.width > window.innerWidth) {
                            left = window.innerWidth - previewRect.width - 10;
                        }
                        if (top + previewRect.height > window.innerHeight) {
                            top = window.innerHeight - previewRect.height - 10;
                        }

                        preview.style.left = left + 'px';
                        preview.style.top = top + 'px';
                    }
                });

                anchor.addEventListener('mouseout', function() {
                    const preview = document.getElementById('anchor-preview');
                    preview.style.display = 'none';
                });

                anchor.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = event.target.textContent.replace('>>', '');
                    const targetEntry = document.getElementById('entry-' + id);
                    if (targetEntry) {
                        const targetDl = targetEntry.closest('dl');
                         const offsetTop = targetDl.getBoundingClientRect().top + window.pageYOffset;

                        window.scrollTo({
                            top: offsetTop - 10, // 投稿がウィンドウの上部に10px余裕を持たせて表示
                            behavior: 'smooth' // スクロールのアニメーションを追加
                        });
                    }
                });
            });
        });
        function insertAnchor(id) {
            const textarea = document.getElementById('body');
            const anchorText = `>>${id}\n`;
            textarea.value += anchorText;
            textarea.focus();
        }
    </script>
</body>
</html>
