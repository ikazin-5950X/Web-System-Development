<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$select_sth = $dbh->prepare('SELECT * FROM bbs_entries WHERE id = :id');
$select_sth->execute([':id' => $id]);
$entry = $select_sth->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    echo "指定された投稿は存在しません。";
    exit;
}
?>
<dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt>ID</dt>
    <dd><?= $entry['id'] ?></dd>
    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt>内容</dt>
    <dd><?= nl2br(htmlspecialchars($entry['body'])) ?></dd>
</dl>

<a href="./bbs1.php">戻る</a>
