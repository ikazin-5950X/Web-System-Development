<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc','root','');

if (isset($_POST['body'])) {
    $image_filename = null;
    if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
        if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {
            header("HTTP/1.1 302 Found");
            header("Location: ./bbsimagetest.php");
        }
        $pathinfo = pathinfo($_FILES['image']['name']);
        $extension = $pathinfo['extension'];
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
        $filepath = '/var/www/upload/image/' . $image_filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
    }
    $insert_sth = $dbh -> prepare("INSERT INTO bbs_entries (body, image_filename) VALUES (:body, :image_filename)");
    $insert_sth -> execute([
        'body' => $_POST['body'],
        ':image_filename' => $image_filename,
    ]);
    header("HTTP/1.1 302 Found");
    header("Location: ./bbsimagetest.php");
    return;
}

$select_sth = $dbh -> prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth -> execute();
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('imageinput');

            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (file.size > maxSize) {
                    alert('ファイルサイズは5MB以下にしてください。');
                    fileInput.value = ''; // Clear the file input
                }
            });
        });
    </script>
</head>
<body>
    <form method="POST" action="./bbsimagetest.php" enctype="multipart/form-data">
        <textarea name="body"></textarea>
        <div style="margin: 1em 0;">
            <input type="file" accept="image/*" name="image" id="imageinput">
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
            <?= nl2br(htmlspecialchars($entry['body'])) ?>
            <?php if (!empty($entry['image_filename'])): ?>
            <div>
                <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
            </div>
            <?php endif; ?>
        </dd>
    </dl>

    <?php endforeach ?>
</body>
</html>