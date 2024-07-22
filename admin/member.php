<?php
session_start();

// 管理者用member.phpはログインしてる管理者だけが見れるように?
// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// セッションから値を取得
$loggedIn = isset($_SESSION['user_id']);
$userName = $loggedIn ? $_SESSION['user_name'] : '';

// ログアウト処理
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy(); // セッションを破棄
    header('Location: login.php'); // ログインページにリダイレクト
    exit();
}

$dsn = 'mysql:host=localhost;dbname=sampledb;charset=utf8mb4';
$username = 'root';
$password = 'K4aCuFEh';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {
        // データベースへの接続を確立
        $pdo = new PDO($dsn, $username, $password);
        // エラー発生時に例外をスローするように設定
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // DBのmemberテーブルから会員ID、氏名、性別、住所（都道府県＋それ以降の住所）、登録日時を取得
        $memberId = $_SESSION['user_id']; // 会員ID
        $name = $_SESSION['name_mei'].$_SESSION['name_sei']; // 氏名
        $gender = $_SESSION['gender']; // 性別
        $address = $_SESSION['pref_name'].$_SESSION['address']; // 住所（都道府県＋それ以降の住所）
        $createdAt = date( 'Y-m-d H:i:s'); // 登録日時

    } catch (PDOException $e) {
        echo '接続失敗: ' . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>会員一覧</title>
    <style>
        li {
            list-style-type: none;
            display: inline;
        }
    </style>
</head>
<body>
    <header>
        <ul>
            <li><strong>会員一覧</strong></li>
            <li><a href="admin_top.php">トップへ戻る</a></li>
        </ul>
    </header>

    <main>
    <!-- 会員検索 -->

    <!-- 会員一覧 1ページあたり10件 -->
    <table border="1" width="70%">
        <tr bgcolor="gray">
            <th>ID</th>
            <th>氏名</th>
            <th>性別</th>
            <th>住所</th>
            <th>登録日時</th>
        </tr>
        <tr>
            <!-- 会員ID、氏名、性別、住所、登録日時 -->
            <th><?php echo htmlspecialchars($memberId); ?></th>
            <th><?php echo htmlspecialchars($name); ?></th>
            <th><?php echo htmlspecialchars($gender); ?></th>
            <th><?php echo htmlspecialchars($address); ?></th>
            <th><?php echo htmlspecialchars($createdAt); ?></th>
        </tr>
    </table>


    </main>
</body>
</html>