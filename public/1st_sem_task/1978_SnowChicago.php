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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>掲示板</title>
    <style>
        body {
            width: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        form {
            margin-bottom: 1em;
            padding-bottom: 1em;
            border-bottom: 1px solid #000;
            padding: 0 1em;
        }
        textarea {
            width: 100%;
            min-height: 100px;
            padding: 0.5em;
            box-sizing: border-box;
            font-size: 1em;
        }
        .filer {
            margin: 1em 0;
        }
        button {
            padding: 0.5em;
            font-size: 1em;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
        }
        button:hover {
            background-color: #0056b3;
        }
        dl {
            margin-bottom: 1em;
            padding-bottom: 1em;
            border-bottom: 1px solid #ccc;
            padding: 0 1em;
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .anchor-preview {
            display: none;
            position: absolute;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 10px;
            max-width: 300px;
            max-height: 200px;
            overflow: auto;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 4px; 
        }
        .reply-anchor {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
        #image-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }
        #image-modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
        }
        #image-modal-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        #image-modal-close:hover,
        #image-modal-close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
        @media (max-width: 600px) {
            body {
                font-size: 14px;
            }
            form {
                padding: 0 0.5em;
            }
            dl {
                padding: 0 0.5em;
            }
            .anchor-preview {
                max-width: 100%;
                max-height: 150px;
                padding: 5px;
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="./1978_SnowChicago.php" enctype="multipart/form-data">
        <textarea name="body" id="body"></textarea>
        <div class="filer" style="margin: 1em 0;">
            <input type="file" accept="image/*" name="image" id="imageInput">
        </div>
        <button type="submit">送信</button>
    </form>

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

    <div id="image-modal">
        <span id="image-modal-close">&times;</span>
        <img id="image-modal-content">
    </div>

    <script>
        $(document).ready(function() {
            // 画像クリック時のイベント処理
            $('img').on('click', function() {
                $('#image-modal-content').attr('src', $(this).attr('src'));
                $('#image-modal').fadeIn();
            });

            // モーダルを閉じる
            $('#image-modal-close').on('click', function() {
                $('#image-modal').fadeOut();
            });

            // モーダルをクリックしたときに閉じる（画像以外の部分）
            $('#image-modal').on('click', function(e) {
                if (e.target.id === 'image-modal') {
                    $('#image-modal').fadeOut();
                }
            });
        });
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
                        const anchorRect = event.target.getBoundingClientRect();
                        const viewportHeight = window.innerHeight;
                        let left = anchorRect.left + window.pageXOffset + 10;
                        // let top = event.pageY + 10;
                        let top;

                        // プレビューを画面下部に表示するか上部に表示するかを判断
                        if (anchorRect.bottom + previewRect.height > viewportHeight) {
                            // プレビューが下に収まらない場合、上に表示
                            top = anchorRect.top + window.pageYOffset - previewRect.height - 10;
                        } else {
                            // 下に余裕がある場合、下に表示
                            top = anchorRect.bottom + window.pageYOffset + 10;
                        }

                        // プレビューが画面右端を超える場合、位置を調整
                        if (left + previewRect.width > window.innerWidth) {
                            left = window.innerWidth - previewRect.width - 10;
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
