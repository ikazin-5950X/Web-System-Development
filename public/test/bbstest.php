<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

if (isset($_POST['body'])) {
    $insert_sth = $dbh -> prepare("INSERT INTO bbs_entries (body) VALUES (:body)");
    $insert_sth->execute([
        ':body' => $_POST['body'],
    ]);
    header("HTTP/1.1 302 Found");
    header("Location: ./bbstest.php");
    return;
}

$select_sth  = $dbh -> prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth -> execute();

?>

<form method="POST" action="./bbstest.php">
  <textarea name="body"></textarea>
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
        <dd><?= nl2br(htmlspecialchars($entry['body'])) ?></dd>
    </dl>
<?php endforeach ?>