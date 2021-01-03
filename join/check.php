<?php
session_start();
require('../dbconnect.php');
// $_SESSION['join]が、空の場合は強制的に index.phpへ戻す
if(!isset($_SESSION['join'])){
	header('Location: index.php');
	exit();
}

if(!empty($_POST)){
	$statement = $db->prepare('INSERT INTO members SET name=?, email=?, password=?, picture=?, created=NOW()');
	$statement->execute(array(
		$_SESSION['join']['name'],
		$_SESSION['join']['email'],
		sha1($_SESSION['join']['password']),
		$_SESSION['join']['image']
	));
	// DBに登録したので、unset で一時保管情報を削除する
	unset($_SESSION['join']);

	header('Location: thanks.php');
	exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>
<body>
<div id="wrap">
<div id="head">
<h1>会員登録</h1>
</div>

<div id="content">
<p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
<form action="" method="post">
	<!-- hidden の value に、submit を指定することで、確認画面の登録ボタンが押された際に、$_POST へsubmitが渡りボタンが押されたことを検知できる -->
	<input type="hidden" name="action" value="submit" />
	<dl>
		<dt>ニックネーム</dt>
		<dd>
			<?php print(htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES)); ?>
        </dd>
		<dt>メールアドレス</dt>
		<dd>
			<?php print(htmlspecialchars($_SESSION['join']['email'], ENT_QUOTES)); ?>
        </dd>
		<dt>パスワード</dt>
		<dd>
		【表示されません】
		</dd>
		<dt>写真など</dt>
		<dd>
		<?php if($_SESSION['join']['image'] !== ''): ?>
			<img src="../member_picture/<?php print(htmlspecialchars($_SESSION['join']['image'], ENT_QUOTES)); ?>" alt="画像表示場所">
		<?php endif; ?>
		</dd>
	</dl>
	<!-- action=rewrite とURLパラメータを付けることで、index.php側で書き直すボタンから飛んできたことが判別出来るようになる -->
	<div><a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する" /></div>
</form>
</div>

</div>
</body>
</html>
