<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

if (isset($_POST['body'])) {
    $insert_sth = $dbh -> prepare("INSERT INTO hogehoge (text) VALUES (:body)");
    $insert_sth -> execute ([
        ':body' => $_POST['body'],
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ./formtodbtest.php");
    return;
}
?>

<form method="POST" action="./formtodbtest.php">
    <textarea name="body"></textarea>
    <button type="submit">送信</button>
</form>