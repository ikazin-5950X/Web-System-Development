<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$total_sth = $dbh->prepare("SELECT COUNT(*) FROM hogehoge");
$total_sth->execute();
$total = $total_sth -> fetchColumn();
$offset = ($page - 1) * $limit;
$total_pages = ceil($total / $limit);

$select_sth = $dbh->prepare("SELECT text, created_at FROM hogehoge ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$select_sth->bindParam(':limit', $limit, PDO::PARAM_INT);
$select_sth->bindParam(':offset', $offset, PDO::PARAM_INT);
$select_sth->execute();
$access_log = $select_sth->fetchAll(PDO::FETCH_ASSOC);

print('<div class="pager">');
if ($page > 1) {
    print('<a href="?page='. ($page - 1). '">&laquo; 前</a>');
}
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == $page) {
        echo '<span>'. $i. '</span>';
    } else {
        print('<a href="?page='. $i. '">'. $i. '</a>');
    }
}

if ($page < $total_pages) {
    print('<a href="?page='. ($page + 1). '">次 &raquo;</a>');
}
print('</div">');

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
