<?php
if (isset($_POST['body'])) {
    print('以下の内容を受け取りました!<br>');
    
    print(nl2br(htmlspecialchars($_POST['body'])));
}

?>

<form method="POST" action="./formtest.php">
    <textarea name="body"></textarea>
    <button type="submit">送信</button>
</form>