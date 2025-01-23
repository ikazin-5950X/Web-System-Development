<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

$insert_sth = $dbh -> prepare("INSERT INTO hogehoge (text) VALUES (:text)");
$insert_sth -> execute ([
    ':text' => 'hello world!!!'
]);

print('insert success.');

$count_sth = $dbh -> prepare("SELECT COUNT(*) FROM hogehoge");
$count_sth -> execute();
$result = $count_sth -> fetchColumn();

print("<br> あなたは{$result}番目にアクセスした方です。");