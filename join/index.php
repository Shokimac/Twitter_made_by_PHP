<?php
// check.phpへ値を渡すために一時保存であるセッションを使う
session_start();
require('../dbconnect.php');

if(!empty($_POST)){
	if($_POST['name'] === ''){
		$error['name'] = 'blank';
	}
	if($_POST['email'] === ''){
		$error['email'] = 'blank';
	}
	// strlen()は、文字数を数値で返すメソッド
	if(strlen($_POST['password']) < 4 ){
		$error['password'] = 'length';
	}
	if($_POST['password'] === ''){
		$error['password'] = 'blank';
	}
	$fileName = $_FILES['image']['name'];
	if(!empty($fileName)) {
		// 第二引数の-3 で後ろ3文字を取得することで、拡張子を得る
		$ext = substr($fileName, -3);
		if($ext != 'jpg' && $ext != 'gif' && $ext != 'png'){
			$error['image'] = 'type';
		}
	}

	// アカウントの重複調査
	if(empty($error)) {
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
		$member->execute(array($_POST['email']));
		$record = $member->fetch();
		if ($record['cnt'] > 0) {
			$error['email'] = 'duplicate';
		}
	}

	if(empty($error)){
		$image = date('YmdHis') . $_FILES['image']['name'];
		// move_uploaded_file()で グローバル変数$_FILESの一時保管場所['tmp_name']にある画像ファイルを第二引数で示した場所に保存する
		move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);
		// 何もエラーが起きていない場合に、$_POSTを一時保存させる
		$_SESSION['join'] = $_POST;
		$_SESSION['join']['image'] = $image;
		header('Location: check.php');
		exit();
	}
}

// check.php の書き直すリンクから飛んできた場合に、セッションに保存した情報を$_POST に入れる
if($_REQUEST['action'] == 'rewrite' && isset($_SESSION['join'])){
	$_POST = $_SESSION['join'];
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
			<p>次のフォームに必要事項をご記入ください。</p>
			<!-- ファイルをアップロードする場合には、enctype="multipart/form-data"を指定する -->
			<form action="" method="post" enctype="multipart/form-data">
				<dl>
					<dt>ニックネーム<span class="required">必須</span></dt>
					<dd>
						<input type="text" name="name" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['name'], ENT_QUOTES)); ?>" />
						<?php if($error['name'] === 'blank'): ?>
						<p class="error">* ニックネームを入力してください</p>
						<?php endif; ?>
					</dd>
					<dt>メールアドレス<span class="required">必須</span></dt>
					<dd>
						<input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['email'], ENT_QUOTES)); ?>" />
						<?php if($error['email'] === 'blank'): ?>
						<p class="error">* メールアドレスを入力してください</p>
						<?php endif; ?>
						<?php if($error['email'] === 'duplicate'): ?>
						<p class="error">* 指定されたメールアドレスは既に登録されています</p>
						<?php endif; ?>
					<dt>パスワード<span class="required">必須</span></dt>
					<dd>
						<input type="password" name="password" size="10" maxlength="20" value="<?php print(htmlspecialchars($_POST['password'], ENT_QUOTES)); ?>" />
						<?php if($error['password'] === 'blank'): ?>
						<p class="error">* パスワードを入力してください</p>
						<?php endif; ?>
						<?php if($error['password'] === 'length'): ?>
						<p class="error">* パスワードは4文字以上で入力してください</p>
						<?php endif; ?>
					</dd>
					<dt>写真など</dt>
					<dd>
						<!-- typeを file にすると、ファイル選択ボタンが追加される -->
						<input type="file" name="image" size="35" value="test" />
						<?php if($error['image'] === 'type'): ?>
						<p class="error">* 画像ファイルを指定してください</p>
						<?php endif; ?>
						<?php if(!empty($error)): ?>
						<p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
						<?php endif; ?>
					</dd>
				</dl>
				<div><input type="submit" value="入力内容を確認する" /></div>
			</form>
		</div>
</body>

</html>