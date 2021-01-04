<?php
// ログインが成功した時に、セッションへ一時保存する為に session_start()を記述する
session_start();
require('./dbconnect.php');

if (!empty($_POST)) {
  if ($_POST['email'] !== '' && $_POST['password'] !== '') {
    $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
    $login->execute(array(
      $_POST['email'],
      // sha1 で暗号化した情報は不可逆暗号で強力な暗号 。ただし、同じ文字列は暗号化しても同じ文字列になるため、再度ログインでも sha1で暗号化してあげる必要がある。
      sha1($_POST['password'])
    ));
    // fetch() でDB上にマッチするデータがあるかどうかを取得する
    $member = $login->fetch();

    if ($member) {
      $_SESSION['id'] =  $member['id'];
      $_SESSION['time'] = time();
      header('Location: index.php');
      exit();
    } else {
      $error['login'] = 'failed';
    }
  } else {
    if ($_POST['email'] == '') {
      $error['email'] = 'blank';
    }
    if ($_POST['password'] == '') {
      $error['password'] = 'blank';
    }
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="style.css" />
  <title>ログインする</title>
</head>

<body>
  <div id="wrap">
    <div id="head">
      <h1>ログインする</h1>
    </div>
    <div id="content">
      <div id="lead">
        <p>メールアドレスとパスワードを記入してログインしてください。</p>
        <p>入会手続きがまだの方はこちらからどうぞ。</p>
        <p>&raquo;<a href="join/">入会手続きをする</a></p>
        <?php if ($error['login'] == 'failed') : ?>
              <p class="error">* 会員情報が見つかりません</p>
            <?php endif; ?>
      </div>
      <form action="" method="post">
        <dl>
          <dt>メールアドレス</dt>
          <dd>
            <input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['email'], ENT_QUOTES)); ?>" />
            <?php if ($error['email'] == 'blank') : ?>
              <p class="error">* メールアドレスを入力してください</p>
            <?php endif; ?>
          </dd>
          <dt>パスワード</dt>
          <dd>
            <input type="password" name="password" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['password'], ENT_QUOTES)); ?>" />
            <?php if ($error['password'] == 'blank') : ?>
              <p class="error">* パスワードを入力してください</p>
            <?php endif; ?>
          </dd>
          <dt>ログイン情報の記録</dt>
          <dd>
            <input id="save" type="checkbox" name="save" value="on">
            <label for="save">次回からは自動的にログインする</label>
          </dd>
        </dl>
        <div>
          <input type="submit" value="ログインする" />
        </div>
      </form>
    </div>
    <div id="foot">
      <p><img src="images/txt_copyright.png" width="136" height="15" alt="(C) H2O Space. MYCOM" /></p>
    </div>
  </div>
</body>

</html>