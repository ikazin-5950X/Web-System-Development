<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

if (isset($_POST['body'])) {
    $insert_sth = $dbh -> prepare("INSERT INTO bbs_entries (body) VALUES (:body)");
    $insert_sth->execute([
        ':body' => $_POST['body'],
    ]);
    header("HTTP/1.1 302 Found");
    header("Location: ./bbs.php");
    return;
}

$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $select_sth = $dbh->prepare('SELECT * FROM bbs_entries WHERE body LIKE :search ORDER BY created_at DESC');
    $select_sth -> execute([':search' => '%' . $search_query . '%']);
} else {
    $select_sth = $dbh -> prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
    $select_sth->execute();
}

?>

<form method="POST" action="./bbs.php">
  <textarea name="body"></textarea>
  <button type="submit">送信</button>
</form>

<form method="GET" action="./bbs.php" style="margin-top: 1em;">
    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>">
    <button type="submit">検索</button>
    <?php if ($search_query): ?>
        <a href="./bbs.php">検索を解除</a>
    <?php endif; ?>
</form>

<hr>

<?php foreach ($select_sth as $entry): ?>
    <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
        <dt>ID</dt>
        <dd><a href="bbs_view.php?id=<?= $entry['id'] ?>"><?= $entry['id'] ?></a></dd>
        <dt>日時</dt>
        <dd><?= $entry['created_at'] ?></dd>
        <dt>内容</dt>
        <dd><?= nl2br(htmlspecialchars($entry['body'])) ?></dd>
    </dl>
<?php endforeach ?>