<?php
session_start();
require('dbconnect.php');

// $_SESSION['time'] に 1時間(3600秒)を足して、現在の時刻よりも大きい場合
// 1時間何もしないでいるとログアウトされてしまうことになる
// つまり、ログインをしている状態の記述を以下に書く
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
  //　最後のログインから1時間、ログインを有効にさせる
  $_SESSION['time'] = time();
  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();
} else {
  header('Location: login.php');
  exit();
}

if (!empty($_POST)) {
  if ($_POST['message'] !== '') {
    $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?, created=NOW()');
    $message->execute(array(
      $member['id'],
      $_POST['message'],
      $_POST['reply_post_id']
    ));

    // DBへメッセージの保存が終わっても$_POSTにメッセージが残ってしまっているので
    // 再度、同じページを読み込むことで$_POSTを空にする。
    header('Location:  index.php');
    exit();
  }
}

// ページネーション の為に定義
$page = $_REQUEST['page'];
// if($page == ''){
//   $page = 1;
// }
// 比較して大きい方を選択する maxメソッドで、$pageが 1より低い場合が指定されても 1ページ目を出すようにさせる
$page = max($page, 1);

// 大きい数字が指定された場合でも、最終ページを選択するようにする
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
// sql でエイリアスとして指定した cntをキーとして 5で割る
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;

// ユーザーの書き込み等ではない為、直接queryメソッドを呼び出す
// $posts = $db->query('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDEr BY p.created DESC LIMIT 0, 5 ');
// ページネーション 実装で柔軟に取得するため、上記のqueryメソッドから prepareメソッドに変更する
$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDEr BY p.created DESC LIMIT ?, 5 ');
// execute()だと文字列として渡ってしまう為、bindParam() PDO::PARAM_INTで数字として指定する
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();


// 返信用リンク Re がクリックされた時に URLパラメータに res= と付くので、そこに値が入っていた場合
if (isset($_REQUEST['res'])) {
  $response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
  $response->execute(array($_REQUEST['res']));

  $table = $response->fetch();
  $message = '@' . $table['name'] . ' ' . $table['message'];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ひとこと掲示板</title>

  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div id="wrap">
    <div id="head">
      <h1>ひとこと掲示板</h1>
    </div>
    <div id="content">
      <div style="text-align: right"><a href="logout.php">ログアウト</a></div>
      <form action="" method="post">
        <dl>
          <dt><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?>さん、メッセージをどうぞ</dt>
          <dd>
            <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?> </textarea>
            <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
          </dd>
        </dl>
        <div>
          <p>
            <input type="submit" value="投稿する" />
          </p>
        </div>
      </form>

      <?php foreach ($posts as $post) : ?>
        <div class="msg">
          <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
          <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>[<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]</p>
          <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
            <?php if (!$post['reply_message_id'] == 0) : ?>
              <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES)); ?>">
                返信元のメッセージ</a>
            <?php endif; ?>
            <!-- 自分のメッセージだけ消せるようにする -->
            <?php if ($_SESSION['id'] == $post['member_id']) : ?>
              [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>" style="color: #F33;">削除</a>]
            <?php endif ?>
          </p>
        </div>
      <?php endforeach; ?>

      <ul class="paging">
        <?php if ($page - 1 > 0) : ?>
          <li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
        <?php endif; ?>
        <?php if ($page < $maxPage) : ?>
          <li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</body>

</html>