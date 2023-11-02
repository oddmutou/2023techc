<?php
// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (!empty($_POST['email']) && !empty($_POST['password'])) {
  // POSTで email と password が送られてきた場合のみログイン処理をする

  // email から会員情報を引く
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email ORDER BY id DESC LIMIT 1");
  $select_sth->execute([
    ':email' => $_POST['email'],
  ]);
  $user = $select_sth->fetch();

  if (empty($user)) {
    // 入力されたメールアドレスに該当する会員が見つからなければ、処理を中断しエラー用クエリパラメータ付きのログイン画面URLにリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }

  // ハッシュ部分とソルトを分離
  $password_hash = mb_substr($user['password'], 0, 64); // 0文字目から64文字分がハッシュ
  $salt = mb_substr($user['password'], 64, 64); // 64文字目から64文字分がソルト

  // ストレッチング
  $input_password_hash = $_POST['password'];
  foreach (range(1, 100) as $count) {
    // ソルトを追加してハッシュ化... を100回繰り返す
    $input_password_hash = hash('sha256', $input_password_hash . $salt);
  }

  $correct_password = $input_password_hash === $password_hash;

  if (!$correct_password) {
    // パスワードが間違っていれば、処理を中断しエラー用クエリパラメータ付きのログイン画面URLにリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }

  session_start();

  // セッションにログインできた会員情報の主キー(id)を設定
  $_SESSION["login_user_id"] = $user['id'];

  // ログインが成功したらログイン完了画面にリダイレクト
  header("HTTP/1.1 302 Found");
  header("Location: ./login_finish.php");
  return;
}
?>

<h1>ログイン</h1>

<!-- ログインフォーム -->
<form method="POST">
  <!-- input要素のtype属性は全部textでも動くが、適切なものに設定すると利用者は使いやすい -->
  <label>
    メールアドレス:
    <input type="email" name="email">
  </label>
  <br>
  <label>
    パスワード:
    <input type="password" name="password" min="6" autocomplete="new-password">
  </label>
  <br>
  <button type="submit">決定</button>
</form>

<?php if(!empty($_GET['error'])): // エラー用のクエリパラメータがある場合はエラーメッセージ表示 ?>
<div style="color: red;">
  メールアドレスかパスワードが間違っています。
</div>
<?php endif; ?>
