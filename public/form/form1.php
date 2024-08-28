<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

$select_sth = $dbh -> prepare("SELECT text, created_at FROM hogehoge ORDER BY created_at DESC");
$select_sth -> execute();
$access_log = $select_sth -> fetchAll(PDO::FETCH_ASSOC);

print("<h2>Form Log</h2>");
print("<table border='1'>");
print("<th>text</th><th>created at</th>");
foreach ($access_log as $log) {
    print("<tr>");
    print("<td>". nl2br(htmlspecialchars($log['text'], ENT_QUOTES, 'utf-8')). "</td>");
    print("<td>". nl2br(htmlspecialchars($log['created_at'], ENT_QUOTES, 'utf-8')). "</td>");
    print("</tr>");
}
print("</table>");