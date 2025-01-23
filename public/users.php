<?php
session_start();

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

// 会員データを取得
$sql = 'SELECT * FROM users';
$where_sql_array = [];
$prepare_params = [];

if (!empty($_GET['name'])) {
  $where_sql_array[] = ' name LIKE :name';
  $prepare_params[':name'] = '%' . $_GET['name'] . '%';
}
if (!empty($_GET['year_from'])) {
    $where_sql_array[] = ' birthday >= :year_from';
    $prepare_params[':year_from'] = $_GET['year_from'] . '-01-01'; // 入力年の1月1日
}
if (!empty($_GET['year_until'])) {
    $where_sql_array[] = ' birthday <= :year_until';
    $prepare_params[':year_until'] = $_GET['year_until'] . '-12-31'; // 入力年の12月31日
  }
  if (!empty($where_sql_array)) {
    $sql .= ' WHERE ' . implode(' AND', $where_sql_array);
  }
  
$sql .= ' ORDER BY id DESC';

$select_sth = $dbh->prepare($sql);
$select_sth->execute($prepare_params);

// ログインしている場合、フォローしている会員IDリストを取得
$followee_user_ids = [];
if (!empty($_SESSION['login_user_id'])) {
  $followee_users_select_sth = $dbh->prepare(
    'SELECT * FROM user_relationships WHERE follower_user_id = :follower_user_id'
  );
  $followee_users_select_sth->execute([
    ':follower_user_id' => $_SESSION['login_user_id'],
  ]);
  $followee_user_ids = array_map(
    function ($relationship) {
        return $relationship['followee_user_id'];
    },
    $followee_users_select_sth->fetchAll()
  ); // array_map で followee_user_id カラムだけ抜き出す
}
?>

<body>
  <h1>会員一覧</h1>

  <div class="links">
    <a href="/setting/setting.php">設定画面</a>
    /
    <a href="/timeline.php">タイムライン</a>
  </div>

  <div class="filter-form">
    絞り込み<br>
    <form method="GET">
      名前: <input type="text" name="name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"><br>
      生まれ年:
      <input type="number" name="year_from" value="<?= htmlspecialchars($_GET['year_from'] ?? '') ?>">年
      ~
      <br>
      <input type="number" name="year_until" value="<?= htmlspecialchars($_GET['year_until'] ?? '') ?>">年
      <br>
      <button type="submit">決定</button>
    </form>
  </div>

  <?php foreach($select_sth as $user): ?>
    <div class="user">
      <?php if(empty($user['icon_filename'])): ?>
        <!-- アイコン無い場合は同じ大きさの空白を表示して揃えておく -->
        <div style="height: 3em; width: 3em; background-color: #ddd; border-radius: 50%;"></div>
      <?php else: ?>
        <img src="/image/<?= $user['icon_filename'] ?>">
      <?php endif; ?>
      <a href="/profile.php?user_id=<?= $user['id'] ?>">
        <?= htmlspecialchars($user['name']) ?>
      </a>

      <div class="actions">
        <?php if($user['id'] === $_SESSION['login_user_id']): ?>
          これはあなたです!
        <?php elseif(in_array($user['id'], $followee_user_ids)): ?>
          フォロー済
        <?php else: ?>
          <a href="./follow.php?followee_user_id=<?= $user['id'] ?>">フォローする</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 15px;
      padding: 0;
      background-color: #f4f4f4;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      padding: 2em;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    h1 {
      text-align: center;
      font-size: 1.8em;
      margin-bottom: 1em;
    }
    .links {
      margin-bottom: 1.5em;
      text-align: center;
    }
    .links a {
      margin: 0 1em;
      text-decoration: none;
      color: #007bff;
    }
    .links a:hover {
      text-decoration: underline;
    }
    .filter-form {
      margin-bottom: 2em;
      padding: 1em;
      border: 1px solid #ddd;
      border-radius: 10px;
      background-color: #f9f9f9;
    }
    .filter-form input {
      width: calc(100% - 22px);
      padding: 0.8em;
      margin-top: 0.5em;
      font-size: 1em;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .filter-form button {
      margin-top: 1em;
      padding: 0.8em 1.2em;
      font-size: 1em;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .filter-form button:hover {
      background-color: #45a049;
    }
    .user {
      display: flex;
      align-items: center;
      padding: 1em 0;
      border-bottom: 1px solid #ddd;
    }
    .user img {
      height: 3em;
      width: 3em;
      border-radius: 50%;
      object-fit: cover;
    }
    .user .info {
      margin-left: 1em;
    }
    .user .actions {
      margin-left: auto;
    }
    .user .actions a {
      color: #007bff;
      text-decoration: none;
    }
    .user .actions a:hover {
      text-decoration: underline;
    }
  </style>
  
</body>