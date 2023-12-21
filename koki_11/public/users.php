<?php
session_start();

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

// 会員データを取得
$select_sth = $dbh->prepare('SELECT * FROM users ORDER BY id DESC');
$select_sth->execute();

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

  <div style="margin-bottom: 1em;">
    <a href="/setting/index.php">設定画面</a>
    /
    <a href="/timeline.php">タイムライン</a>
  </div>

  <?php foreach($select_sth as $user): ?>
    <div style="display: flex; justify-content: start; align-items: center; padding: 1em 2em;">
      <?php if(empty($user['icon_filename'])): ?>
        <!-- アイコン無い場合は同じ大きさの空白を表示して揃えておく -->
        <div style="height: 2em; width: 2em;"></div>
      <?php else: ?>
        <img src="/image/<?= $user['icon_filename'] ?>"
          style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
      <?php endif; ?>
      <a href="/profile.php?user_id=<?= $user['id'] ?>" style="margin-left: 1em;">
        <?= htmlspecialchars($user['name']) ?>
      </a>
      <div style="margin-left: 2em;">
        <?php if($user['id'] === $_SESSION['login_user_id']): ?>
          これはあなたです!
        <?php elseif(in_array($user['id'], $followee_user_ids)): ?>
          フォロー済
        <?php else: ?>
          <a href="./follow.php?followee_user_id=<?= $user['id'] ?>">フォローする</a>
        <?php endif; ?>
      </div>
    </div>
    <hr style="border: none; border-bottom: 1px solid gray;">
  <?php endforeach; ?>
</body>
