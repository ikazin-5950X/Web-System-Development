<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

session_start();
if (empty($_SESSION['login_user_id'])) { // 非ログインの場合利用不可
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// 現在のログイン情報を取得する
$user_select_sth = $dbh->prepare("SELECT * from users WHERE id = :id");
$user_select_sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $user_select_sth->fetch();

// ページ番号とバッチサイズを設定
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // デフォルトで1ページ目
$batch_size = 10; // 1ページあたりの投稿数
$offset = ($page - 1) * $batch_size;

// 投稿データを取得
$sql = 'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
  . ' FROM bbs_entries'
  . ' INNER JOIN users ON bbs_entries.user_id = users.id'
  . ' WHERE'
  . '   bbs_entries.user_id IN'
  . '     (SELECT followee_user_id FROM user_relationships WHERE follower_user_id = :login_user_id)'
  . '   OR bbs_entries.user_id = :login_user_id'
  . ' ORDER BY bbs_entries.created_at DESC'
  . ' LIMIT :limit OFFSET :offset';

$select_sth = $dbh->prepare($sql);
$select_sth->bindValue(':login_user_id', $_SESSION['login_user_id'], PDO::PARAM_INT);
$select_sth->bindValue(':limit', $batch_size, PDO::PARAM_INT);
$select_sth->bindValue(':offset', $offset, PDO::PARAM_INT);
$select_sth->execute();

// bodyのHTMLを出力するための関数を用意する
function bodyFilter(string $body): string
{
  $body = htmlspecialchars($body); // エスケープ処理
  $body = nl2br($body); // 改行文字を<br>要素に変換
  return $body;
}

// JSONに吐き出す用のentries
$result_entries = [];
foreach ($select_sth as $entry) {
  // 画像が複数の場合、カンマ区切りで分割して配列にする
  $image_urls = [];
  if (!empty($entry['image_filename'])) {
    $image_filenames = explode(',', $entry['image_filename']);
    foreach ($image_filenames as $filename) {
      $image_urls[] = '/image/' . $filename;
    }
  }
  
  $result_entry = [
    'id' => $entry['id'],
    'user_name' => $entry['user_name'],
    'user_profile_url' => '/profile.php?user_id=' . $entry['user_id'],
    'user_icon_url' => empty($entry['user_icon_filename']) ? null : '/image/' . $entry['user_icon_filename'],
    'body' => bodyFilter($entry['body']),
    'created_at' => $entry['created_at'],
    'image_url' => empty($entry['image_filename']) ? null : implode(',', $image_urls),
  ];
  $result_entries[] = $result_entry;
}

header("HTTP/1.1 200 OK");
header("Content-Type: application/json");
print(json_encode(['entries' => $result_entries]));