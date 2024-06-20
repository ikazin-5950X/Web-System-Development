<?php
$dbh = new PDO('mysql:host=mysql; dbname=techc', 'root', '');

$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$logger = $dbh -> prepare("INSERT INTO logs (ip_address, user_agent) VALUES (:ip_address, :user_agent)");
$logger ->execute([
    ':ip_address' => $ip_address,
    ':user_agent' => $user_agent,
]);

$select_sth = $dbh -> prepare("SELECT * FROM logs ORDER BY access_time DESC");
$select_sth -> execute();
$access_log = $select_sth -> fetchAll(PDO::FETCH_ASSOC);

print("<h2>Access Log</h2>");
print("<table border='1'>");
print("<th>IP Address</th><th>User Agent</th><th>Access Time</th></tr>");
foreach ($access_log as $log) {
    print("<tr>");
    print("<td>{$log['ip_address']}</td>");
    print("<td>{$log['user_agent']}</td>");
    print("<td>{$log['access_time']}</td>");
    print("</tr>");
}
print("</table>");