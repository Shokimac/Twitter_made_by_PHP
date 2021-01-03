<?php
try {
    $db = new PDO('mysql:dbname=mini_bbs;host=localhost;port=3306;charset=utf8','root','root');
} catch(PDOException $e) {
    echo 'DB接続エラー：　' . $e->getMessage();
}
?>